<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\{DB, ValidationException};
final class OrderService
{
    public const STATUSES = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for delivery', 'Delivered', 'Cancelled'];
    public static function quote(array $cart, string $type): array
    {
        if (!$cart || count($cart) > 50) {
            throw new ValidationException('Your cart is empty or too large.');
        }
        if (!in_array($type, ['pickup', 'delivery'], true)) {
            throw new ValidationException('Invalid order type.');
        }
        if ($type === 'delivery' && !setting('ordering.delivery_enabled', true)) {
            throw new ValidationException('Delivery is currently unavailable.');
        }
        $items = [];
        $subtotal = 0;
        foreach ($cart as $id => $quantity) {
            if (
                !is_numeric($id) ||
                filter_var($quantity, FILTER_VALIDATE_INT) === false ||
                $quantity < 1 ||
                $quantity > 20
            ) {
                throw new ValidationException('Quantity must be 1–20.');
            }
            $p = DB::one('SELECT * FROM products WHERE id=?', [(int) $id]);
            if (!$p || !$p['available']) {
                throw new ValidationException(
                    'A dish in your cart is unavailable. Please update your cart.',
                );
            }
            $items[] = [
                'product_id' => $p['id'],
                'name' => plain($p['name']),
                'name_translations' => json_encode(array_combine(['en', 'bn'], array_map(static fn($language) => plain(Translations::value('products', (int) $p['id'], 'name', $language) ?? Locale::translateFragment($p['name'], $language)), ['en', 'bn'])), JSON_UNESCAPED_UNICODE),
                'price' => (int) $p['price'],
                'quantity' => (int) $quantity,
            ];
            $subtotal += (int) $p['price'] * $quantity;
        }
        if ($subtotal > 100000000) {
            throw new ValidationException('Order total exceeds the limit.');
        }
        $fee = $type === 'delivery' ? (int) setting('ordering.delivery_fee', 6000) : 0;
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => $fee,
            'total' => $subtotal + $fee,
        ];
    }
    public static function create(array $cart, array $customer, string $key): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $key)) {
            throw new ValidationException('Checkout expired. Reload the page.');
        }
        try {
            return DB::transaction(function () use ($cart, $customer, $key) {
                $existing = DB::one('SELECT * FROM orders WHERE idempotency_key=?', [$key]);
                if ($existing) {
                    return $existing;
                }
                $q = self::quote($cart, $customer['order_type']);
                $id = DB::insert(
                    'orders',
                    array_merge($customer, [
                        'number' => 'KJ-' . strtoupper(bin2hex(random_bytes(5))),
                        'access_token' => bin2hex(random_bytes(32)),
                        'idempotency_key' => $key,
                        'subtotal' => $q['subtotal'],
                        'delivery_fee' => $q['delivery_fee'],
                        'total' => $q['total'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ]),
                );
                foreach ($q['items'] as $i) {
                    DB::insert('order_items', ['order_id' => $id] + $i);
                }
                DB::insert('order_events', ['order_id' => $id, 'status' => 'Pending', 'created_at' => date('Y-m-d H:i:s')]);
                DB::audit('Order #' . $id . ' placed');
                return DB::one('SELECT * FROM orders WHERE id=?', [$id]);
            });
        } catch (\PDOException $e) {
            $existing = DB::one('SELECT * FROM orders WHERE idempotency_key=?', [$key]);
            if ($existing) {
                return $existing;
            }
            throw $e;
        }
    }
    public static function transition(int $id, string $next): void
    {
        DB::transaction(function () use ($id, $next) {
            $o = DB::one('SELECT * FROM orders WHERE id=?', [$id]);
            if (!$o || !in_array($next, self::nextStatuses($o), true)) {
                throw new ValidationException('This order status change is not allowed.');
            }
            if (
                $next !== 'Cancelled' &&
                $o['payment_method'] !== 'cod' &&
                $o['payment_status'] !== 'paid'
            ) {
                throw new ValidationException(
                    'Online payment must be verified before fulfillment.',
                );
            }
            $s = DB::run('UPDATE orders SET status=? WHERE id=? AND status=?', [
                $next,
                $id,
                $o['status'],
            ]);
            if ($s->rowCount() !== 1) {
                throw new ValidationException('Order changed. Reload and try again.');
            }
            DB::insert('order_events', ['order_id' => $id, 'status' => $next, 'created_at' => date('Y-m-d H:i:s')]);
            DB::audit('Order #' . $id . ' → ' . $next);
        });
    }
    public static function nextStatuses(array $order): array
    {
        return match ($order['status']) {
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['Preparing', 'Cancelled'],
            'Preparing' => ['Ready', 'Delivered', 'Cancelled'],
            'Ready' => $order['order_type'] === 'delivery' ? ['Out for delivery', 'Delivered', 'Cancelled'] : ['Delivered', 'Cancelled'],
            'Out for delivery' => ['Delivered', 'Cancelled'],
            default => [],
        };
    }
    public static function steps(string $type): array
    {
        $steps = ['Pending', 'Confirmed', 'Preparing', 'Ready'];
        if ($type === 'delivery') $steps[] = 'Out for delivery';
        $steps[] = 'Delivered';
        return $steps;
    }
    public static function statusLabel(string $status, string $type): string
    {
        $label = match ($status) {
            'Ready' => $type === 'pickup' ? 'Ready for pickup' : 'Ready for dispatch',
            'Delivered' => $type === 'pickup' ? 'Collected' : 'Delivered',
            default => $status,
        };
        return Locale::text($label);
    }
    public static function events(int $id): array
    {
        return DB::all('SELECT status,created_at FROM order_events WHERE order_id=? ORDER BY id', [$id]);
    }
    public static function itemName(array $item): string
    {
        $names = json_decode($item['name_translations'] ?? 'null', true);
        $value = is_array($names) ? ($names[Locale::current()] ?? null) : null;
        if (is_string($value)) { $value = plain($value); Locale::protectContent($value); return $value; }
        // Covers historical receipts whose name field contains editor markup.
        return plain($item['name']);
    }
    public static function normalizePhone(string $phone): string
    {
        return substr(preg_replace('/[^0-9]/', '', $phone), -11);
    }
}
