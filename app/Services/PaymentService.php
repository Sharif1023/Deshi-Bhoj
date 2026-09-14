<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\{DB, ValidationException};
/** Hosted bKash/Nagad through SSLCOMMERZ; no browser callback is trusted as proof. */
final class PaymentService
{
    public static function enabled(): bool
    {
        return env('PAYMENTS_ENABLED') === 'true' &&
            env('SSLCOMMERZ_STORE_ID') !== '' &&
            env('SSLCOMMERZ_STORE_PASSWORD') !== '';
    }
    private static function base(): string
    {
        return env('SSLCOMMERZ_SANDBOX', 'true') === 'true'
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }
    private static function request(string $path, array $data, bool $post = true): array
    {
        $data += [
            'store_id' => env('SSLCOMMERZ_STORE_ID'),
            'store_passwd' => env('SSLCOMMERZ_STORE_PASSWORD'),
            'format' => 'json',
        ];
        $ch = curl_init(self::base() . $path . ($post ? '' : '?' . http_build_query($data)));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        if ($post) {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
            ]);
        }
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status !== 200) {
            throw new ValidationException(
                'Payment provider is unavailable. Your order is saved; retry payment shortly.',
            );
        }
        $result = json_decode($body, true);
        if (!is_array($result)) {
            throw new ValidationException('Invalid payment provider response.');
        }
        return $result;
    }
    public static function start(array $order): string
    {
        if (!self::enabled()) {
            throw new ValidationException('Online payments are not configured.');
        }
        if (
            $order['payment_method'] === 'cod' ||
            $order['payment_status'] === 'paid' ||
            $order['status'] === 'Cancelled'
        ) {
            throw new ValidationException('This order cannot start an online payment.');
        }
        // Reuse the same pending provider session. An uncertain initiation is reconciled by staff;
        // it must never silently create a second charge attempt.
        $p = DB::one('SELECT * FROM payments WHERE order_id=? ORDER BY id DESC LIMIT 1', [
            $order['id'],
        ]);
        if ($p) {
            if ($p['status'] === 'pending' && $p['gateway_url']) {
                return $p['gateway_url'];
            }
            throw new ValidationException(
                'A payment attempt already exists. Staff must reconcile it before another order is placed.',
            );
        }
        $tran = 'KJ' . $order['id'] . '-' . bin2hex(random_bytes(10));
        $now = date('Y-m-d H:i:s');
        $id = DB::insert('payments', [
            'order_id' => $order['id'],
            'transaction_id' => $tran,
            'amount' => $order['total'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $url = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
        $channel = env(
            $order['payment_method'] === 'bkash'
                ? 'SSLCOMMERZ_BKASH_CHANNEL'
                : 'SSLCOMMERZ_NAGAD_CHANNEL',
            $order['payment_method'],
        );
        $r = self::request('/gwprocess/v4/api.php', [
            'total_amount' => number_format($order['total'] / 100, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $tran,
            'success_url' => $url . '/payment/success',
            'fail_url' => $url . '/payment/fail',
            'cancel_url' => $url . '/payment/cancel',
            'ipn_url' => $url . '/payment/ipn',
            'cus_name' => $order['customer_name'],
            'cus_email' => $order['email'],
            'cus_add1' => $order['address'] ?: 'Pickup at restaurant',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $order['phone'],
            'shipping_method' => 'NO',
            'product_name' => 'KOJI restaurant order ' . $order['number'],
            'product_category' => 'Food',
            'product_profile' => 'general',
            'multi_card_name' => $channel,
        ]);
        if (($r['status'] ?? '') !== 'SUCCESS' || !self::validUrl($r['GatewayPageURL'] ?? '')) {
            throw new ValidationException(
                'Gateway initiation did not succeed. Contact the restaurant with your order number.',
            );
        }
        DB::run('UPDATE payments SET gateway_url=?,session_key=?,updated_at=? WHERE id=?', [
            $r['GatewayPageURL'],
            $r['sessionkey'] ?? '',
            date('Y-m-d H:i:s'),
            $id,
        ]);
        return $r['GatewayPageURL'];
    }
    public static function validUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        return parse_url($url, PHP_URL_SCHEME) === 'https' &&
            is_string($host) &&
            ($host === 'sslcommerz.com' || str_ends_with($host, '.sslcommerz.com'));
    }
    public static function validate(string $validationId): void
    {
        if (!self::enabled() || strlen($validationId) > 190 || $validationId === '') {
            throw new ValidationException('Missing payment validation reference.');
        }
        $r = self::request(
            '/validator/api/validationserverAPI.php',
            ['val_id' => $validationId],
            false,
        );
        self::applyVerified($r);
    }
    /** Only called with an authenticated server-to-server validation response. */
    public static function applyVerified(array $r): void
    {
        if (!in_array($r['status'] ?? '', ['VALID', 'VALIDATED'], true)) {
            throw new ValidationException('Payment has not been verified.');
        }
        $p = DB::one('SELECT * FROM payments WHERE transaction_id=?', [
            (string) ($r['tran_id'] ?? ''),
        ]);
        if (
            !$p ||
            ($r['currency'] ?? '') !== 'BDT' ||
            self::paisa((string) ($r['amount'] ?? '')) !== (int) $p['amount'] ||
            empty($r['bank_tran_id'])
        ) {
            throw new ValidationException('Payment details do not match this order.');
        }
        if (!isset($r['risk_level']) || !in_array((string) $r['risk_level'], ['0', '1'], true)) {
            throw new ValidationException('Payment risk status is missing.');
        }
        DB::transaction(function () use ($p, $r) {
            $fresh = DB::one('SELECT * FROM payments WHERE id=?', [$p['id']]);
            $o = DB::one('SELECT * FROM orders WHERE id=?', [$p['order_id']]);
            if ($fresh['status'] === 'paid') {
                return;
            }
            $status =
                (string) $r['risk_level'] === '1' || $o['status'] === 'Cancelled'
                    ? 'review'
                    : 'paid';
            DB::run(
                'UPDATE payments SET status=?,provider_reference=?,updated_at=? WHERE id=? AND status<>\'paid\'',
                [$status, (string) $r['bank_tran_id'], date('Y-m-d H:i:s'), $p['id']],
            );
            DB::run('UPDATE orders SET payment_status=? WHERE id=? AND payment_status<>?', [
                $status,
                $p['order_id'],
                'paid',
            ]);
            DB::audit('Payment ' . $p['transaction_id'] . ' verified: ' . $status);
        });
    }
    private static function paisa(string $value): int
    {
        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $m)) {
            return -1;
        }
        return (int) $m[1] * 100 + (int) str_pad($m[2] ?? '', 2, '0');
    }
}
