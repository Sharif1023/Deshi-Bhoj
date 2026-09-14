<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{DB, Auth, ValidationException};
use App\Services\{Content, OrderService, PaymentService, Translations, Locale};
final class StoreController
{
    public function page(string $page): void
    {
        $data = [
            'page' => $page,
            'title' => match ($page) {
                'home' => 'বাংলার স্বাদ, আপন আয়োজনে',
                'menu' => 'খাবারের মেনু',
                'about' => 'আমাদের গল্প',
                'gallery' => 'গ্যালারি',
                'contact' => 'যোগাযোগ ও বুকিং',
                'cart' => 'আপনার অর্ডার',
                'checkout' => 'অর্ডার সম্পন্ন করুন',
                default => 'দেশি ভোজ',
            },
        ];
        if (in_array($page, ['home', 'menu'])) {
            $data['products'] = Content::products(
                (string) ($_GET['q'] ?? ''),
                (string) ($_GET['category'] ?? ''),
                (string) ($_GET['sort'] ?? ''),
            );
            $data['categories'] = Translations::rows('categories', DB::all('SELECT * FROM categories ORDER BY sort_order,id'));
        }
        if ($page === 'home') {
            $data['features'] = Translations::rows('features', DB::all('SELECT * FROM features ORDER BY sort_order,id'));
            $data['reviews'] = Translations::rows('reviews', DB::all('SELECT * FROM reviews WHERE published=1 ORDER BY sort_order,id'));
        }
        if ($page === 'gallery') {
            $data['images'] = Translations::rows('gallery', DB::all('SELECT * FROM gallery ORDER BY sort_order,id'));
        }
        if (in_array($page, ['cart', 'checkout'])) {
            $data['cart'] = [];
            foreach ($_SESSION['cart'] ?? [] as $id => $quantity) {
                $p = DB::one('SELECT * FROM products WHERE id=?', [$id]);
                if ($p) {
                    $data['cart'][] = Translations::row('products', $p) + ['quantity' => $quantity];
                } else {
                    unset($_SESSION['cart'][$id]);
                }
            }
            $_SESSION['checkout_key'] ??= bin2hex(random_bytes(32));
        }
        if ($page === 'home') {
            DB::run("UPDATE settings SET value=value+1 WHERE setting_key='analytics.views'");
        }
        if ($page==='faq') $data['faqs']=Translations::rows('faqs', DB::all('SELECT * FROM faqs ORDER BY sort_order,id'));
        if (in_array($page,['delivery','privacy','terms'])) {$data['infoKey']=$page;$data['title']=ucfirst($page);view('store/information',$data);return;}
        view('store/' . $page, $data);
    }
    public function product(string $slug): void
    {
        $p = DB::one('SELECT * FROM products WHERE slug=?', [$slug]);
        if (!$p) {
            http_response_code(404);
            view('error', [
                'title' => 'Dish not found',
                'message' => 'This dish is no longer on our menu.',
            ]);
            return;
        }
        $p = Translations::row('products', $p);
        view('store/product', ['title' => plain($p['name']), 'p' => $p]);
    }
    public function cart(): void
    {
        $id = (int) input('product_id');
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0 || $quantity > 20) {
            throw new ValidationException('Choose a quantity between 0 and 20.');
        }
        if (input('action') === 'add') {
            $p = DB::one('SELECT * FROM products WHERE id=?', [$id]);
            if (!$p || !$p['available']) {
                throw new ValidationException('Dish unavailable.');
            }
            $quantity += $_SESSION['cart'][$id] ?? 0;
            if ($quantity > 20) {
                throw new ValidationException('Maximum 20 of each dish.');
            }
        }
        if ($quantity === 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $quantity;
        }
        unset($_SESSION['checkout_key']);
        flash('Your cart has been updated.');
        redirect('/cart');
    }
    public function checkout(): void
    {
        Auth::throttle('checkout:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 20, 3600);
        $key = input('checkout_key', 64);
        if (!hash_equals($_SESSION['checkout_key'] ?? '', $key)) {
            throw new ValidationException('Checkout expired. Reload your cart.');
        }
        $customer = [
            'customer_name' => input('name', 100),
            'email' => emailInput(),
            'phone' => phoneInput(),
            'address' => input('address', 1000, false),
            'order_type' => choice('order_type', ['pickup', 'delivery']),
            'notes' => input('notes', 2000, false),
            'payment_method' => choice('payment_method', ['cod', 'bkash', 'nagad']),
        ];
        if ($customer['order_type'] === 'delivery' && strlen($customer['address']) < 8) {
            throw new ValidationException('Enter your full delivery address.');
        }
        if ($customer['payment_method'] !== 'cod' && !PaymentService::enabled()) {
            throw new ValidationException('Online payment is not configured. Select cash payment.');
        }
        $o = OrderService::create($_SESSION['cart'] ?? [], $customer, $key);
        $_SESSION['cart'] = [];
        $this->rememberOrder($o);
        redirect('/order/' . $o['access_token']);
    }
    public function order(string $token): void
    {
        $o = DB::one('SELECT * FROM orders WHERE access_token=?', [$token]);
        if (!$o) {
            http_response_code(404);
            view('error', [
                'title' => 'Order not found',
                'message' => 'Check your private order link.',
            ]);
            return;
        }
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        $this->rememberOrder($o);
        view('store/order', [
            'title' => 'Order ' . $o['number'],
            'o' => $o,
            'items' => DB::all('SELECT * FROM order_items WHERE order_id=?', [$o['id']]),
            'events' => OrderService::events((int) $o['id']),
            'page' => 'order',
        ]);
    }
    private function rememberOrder(array $order): void
    {
        $recent = $_SESSION['recent_orders'] ?? [];
        unset($recent[$order['access_token']]);
        $_SESSION['recent_orders'] = array_slice([$order['access_token'] => $order['number']] + $recent, 0, 5, true);
    }
    public function trackPage(string $error = ''): void
    {
        header('Cache-Control: no-store');
        view('store/track', ['page' => 'track', 'title' => 'Track your order', 'error' => $error]);
    }
    public function track(): void
    {
        Auth::throttle('track:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 10, 900);
        $number = strtoupper(input('number', 40));
        $phone = OrderService::normalizePhone(phoneInput());
        $order = DB::one('SELECT * FROM orders WHERE number=?', [$number]);
        if (!$order || !hash_equals(OrderService::normalizePhone($order['phone']), $phone)) {
            http_response_code(422);
            $this->trackPage('No matching order. Check the order number and phone number.');
            return;
        }
        $this->rememberOrder($order);
        redirect('/order/' . $order['access_token']);
    }
    public function orderStatus(string $token): void
    {
        $order = DB::one('SELECT * FROM orders WHERE access_token=?', [$token]);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        if (!$order) { http_response_code(404); echo json_encode(['error' => 'Order not found.']); return; }
        $steps = OrderService::steps($order['order_type']);
        echo json_encode([
            'status' => $order['status'], 'statusLabel' => OrderService::statusLabel($order['status'], $order['order_type']),
            'paymentStatus' => $order['payment_status'], 'paymentLabel' => Locale::text($order['payment_status']),
            'stepIndex' => array_search($order['status'], $steps, true),
            'events' => OrderService::events((int) $order['id']),
            'checkedLabel' => Locale::text('Updated just now'),
        ], JSON_UNESCAPED_UNICODE);
    }
    public function pay(): void
    {
        $o = DB::one('SELECT * FROM orders WHERE access_token=?', [input('access_token', 64)]);
        if (!$o) {
            throw new ValidationException('Order not found.');
        }
        redirect(PaymentService::start($o));
    }
    public function paymentCallback(string $result): void
    {
        if ($result === 'ipn' || $result === 'success') {
            PaymentService::validate(input('val_id', 190));
            if ($result === 'ipn') {
                echo 'OK';
                return;
            }
        }
        view('store/payment', ['title' => 'Payment update', 'result' => $result]);
    }
    public function contact(string $type): void
    {
        Auth::throttle('forms:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 10, 3600);
        if (input('website', 100, false) !== '') {
            throw new ValidationException('Unable to accept this submission.');
        }
        $data = [
            'name' => input('name', 100),
            'email' => emailInput(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($type === 'reservation') {
            $data += [
                'phone' => phoneInput(),
                'reserved_at' => input('reserved_at', 30),
                'notes' => input('notes', 2000, false),
            ];
            $dt = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $data['reserved_at']);
            if (
                !$dt ||
                $dt->format('Y-m-d\TH:i') !== $data['reserved_at'] ||
                $dt->getTimestamp() <= time() ||
                $dt->getTimestamp() > time() + 90 * 86400
            ) {
                throw new ValidationException('Choose a valid future time within 90 days.');
            }
            $guests = filter_var($_POST['guests'] ?? null, FILTER_VALIDATE_INT);
            if (!$guests || $guests < 1 || $guests > (int) setting('reservation.max_guests', 12)) {
                throw new ValidationException('Invalid party size.');
            }
            $data['guests'] = $guests;
            DB::insert('reservations', $data);
            flash('Reservation requested. Our team will contact you to confirm availability.');
        } else {
            $data['message'] = input('message', 5000);
            DB::insert('messages', $data);
            flash('Thank you. Your message has been received.');
        }
        redirect('/contact');
    }
}
