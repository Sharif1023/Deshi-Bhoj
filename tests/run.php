<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/Core/bootstrap.php';
require __DIR__.'/database.php';
$testDb=qaDatabase('create');putenv('DB_CONNECTION=mysql');putenv('DB_DATABASE='.$testDb);
use App\Core\{DB, Auth, ValidationException};
use App\Services\{OrderService, PaymentService};
ob_start();
$_SESSION = [];
$passed = 0;
function check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    echo 'PASS: ' . $label . "\n";
    $passed++;
}
function rejects(callable $fn, string $label): void
{
    try {
        $fn();
    } catch (ValidationException) {
        check(true, $label);
        return;
    }
    throw new RuntimeException('FAIL: ' . $label);
}
try {
    require ROOT . '/database/migrate.php';
    require ROOT . '/database/seed.php';
    $p = DB::one('SELECT * FROM products ORDER BY id LIMIT 1');
    $id = $p['id'];
    $before = DB::one('SELECT COUNT(*) n FROM products')['n'];
    require ROOT . '/database/seed.php';
    check(
        DB::one('SELECT COUNT(*) n FROM products')['n'] === $before,
        'Seed does not duplicate content',
    );
    check(DB::one('SELECT COUNT(*) n FROM users')['n'] === 0, 'No insecure default admin account');
    $q = OrderService::quote([$id => 2], 'pickup');
    check($q['total'] === (int) $p['price'] * 2, 'Server-side authoritative prices');
    check(
        OrderService::quote([$id => 2], 'delivery')['delivery_fee'] === 6000,
        'Delivery fee from settings',
    );
    rejects(fn() => OrderService::quote([$id => 0], 'pickup'), 'Reject zero quantity');
    rejects(fn() => OrderService::quote([$id => 21], 'pickup'), 'Reject excessive quantity');
    rejects(fn() => OrderService::quote([$id => 1.5], 'pickup'), 'Reject fractional quantity');
    rejects(fn() => OrderService::quote([99999 => 1], 'pickup'), 'Reject missing product');
    DB::run('UPDATE products SET available=0 WHERE id=?', [$id]);
    rejects(fn() => OrderService::quote([$id => 1], 'pickup'), 'Reject unavailable dish');
    DB::run('UPDATE products SET available=1 WHERE id=?', [$id]);
    $c = [
        'customer_name' => 'Test Guest',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'address' => 'Dhaka address',
        'order_type' => 'pickup',
        'notes' => '',
        'payment_method' => 'cod',
    ];
    $key = bin2hex(random_bytes(32));
    $o = OrderService::create([$id => 2], $c, $key);
    $duplicate = OrderService::create([$id => 2], $c, $key);
    check(
        $o['id'] === $duplicate['id'] && DB::one('SELECT COUNT(*) n FROM orders')['n'] === 1,
        'Repeated checkout creates one order',
    );
    check(
        count(DB::all('SELECT * FROM order_items WHERE order_id=?', [$o['id']])) === 1,
        'Order snapshot is not duplicated',
    );
    DB::run('UPDATE products SET price=99900 WHERE id=?', [$id]);
    check(
        (int) DB::one('SELECT price FROM order_items WHERE order_id=?', [$o['id']])['price'] ===
            (int) $p['price'],
        'Historical prices remain unchanged',
    );
    rejects(
        fn() => OrderService::transition($o['id'], 'Delivered'),
        'Reject skipping order states',
    );
    OrderService::transition($o['id'], 'Confirmed');
    OrderService::transition($o['id'], 'Preparing');
    OrderService::transition($o['id'], 'Delivered');
    rejects(
        fn() => OrderService::transition($o['id'], 'Confirmed'),
        'Delivered orders cannot reopen',
    );
    $online = OrderService::create(
        [$id => 1],
        array_replace($c, ['payment_method' => 'bkash']),
        bin2hex(random_bytes(32)),
    );
    rejects(
        fn() => OrderService::transition($online['id'], 'Confirmed'),
        'Unpaid online orders cannot be fulfilled',
    );
    $now = date('Y-m-d H:i:s');
    DB::insert('payments', [
        'order_id' => $online['id'],
        'transaction_id' => 'TEST-PAYMENT',
        'amount' => $online['total'],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $verified = [
        'status' => 'VALID',
        'tran_id' => 'TEST-PAYMENT',
        'currency' => 'BDT',
        'amount' => number_format($online['total'] / 100, 2, '.', ''),
        'bank_tran_id' => 'TEST-BANK-REFERENCE',
        'risk_level' => '0',
    ];
    rejects(
        fn() => PaymentService::applyVerified(array_replace($verified, ['amount' => '0.01'])),
        'Reject payment amount mismatch',
    );
    rejects(
        fn() => PaymentService::applyVerified(array_replace($verified, ['currency' => 'USD'])),
        'Reject payment currency mismatch',
    );
    rejects(
        fn() => PaymentService::applyVerified(array_replace($verified, ['tran_id' => 'FAKE'])),
        'Reject unknown transaction',
    );
    rejects(
        fn() => PaymentService::applyVerified(array_replace($verified, ['status' => 'FAILED'])),
        'Reject failed provider status',
    );
    PaymentService::applyVerified(array_replace($verified, ['risk_level' => '1']));
    check(
        DB::one('SELECT payment_status FROM orders WHERE id=?', [$online['id']])[
            'payment_status'
        ] === 'review',
        'Risky payments held for review',
    );
    PaymentService::applyVerified($verified);
    PaymentService::applyVerified($verified);
    check(
        DB::one('SELECT payment_status FROM orders WHERE id=?', [$online['id']])[
            'payment_status'
        ] === 'paid',
        'Verified payment and duplicate notification are idempotent',
    );
    OrderService::transition($online['id'], 'Confirmed');
    check(
        !PaymentService::validUrl('https://sslcommerz.com.evil.test/pay') &&
            !PaymentService::validUrl('http://sandbox.sslcommerz.com/pay') &&
            PaymentService::validUrl('https://sandbox.sslcommerz.com/pay'),
        'Gateway redirect allowlist',
    );
    check(
        e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;',
        'HTML output escaping',
    );
    rejects(fn() => Auth::password('short'), 'Enforce strong minimum password length');
    $hash = Auth::password('LongTestPassword!42');
    check(password_verify('LongTestPassword!42', $hash), 'Password hashing round-trip');
    Auth::throttle('test', 2);
    Auth::throttle('test', 2);
    rejects(fn() => Auth::throttle('test', 2), 'Database-backed rate limits');
    $uid = DB::insert('users', [
        'name' => 'Test',
        'email' => 'owner@example.com',
        'password' => $hash,
        'role' => 'owner',
        'created_at' => $now,
    ]);
    $_SESSION = ['user_id' => $uid, 'version' => 1, 'expires' => time() + 60];
    check(Auth::user()['id'] === $uid, 'Valid staff session');
    DB::run('UPDATE users SET session_version=2 WHERE id=?', [$uid]);
    check(Auth::user() === null, 'Revoked session rejected');
    echo "\n$passed checks passed. Isolated MySQL test database only.\n";
} finally {
    ob_end_flush();
    qaDatabase('drop',$testDb);
}
