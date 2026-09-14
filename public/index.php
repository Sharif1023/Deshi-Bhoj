<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/Core/bootstrap.php';
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT . '/storage/logs/app.log');
$secure = env('SESSION_SECURE', 'false') === 'true';
session_save_path(ROOT . '/storage/sessions');
ini_set('session.use_strict_mode', '1');
session_name('koji_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
App\Services\Locale::boot();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header(
    "Content-Security-Policy: default-src 'self'; img-src 'self' https: data:; style-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'",
);
header('Cache-Control: no-store');
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method === 'POST' && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0 && empty($_POST) && empty($_FILES) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data')) {
        http_response_code(413);
        throw new App\Core\ValidationException('Upload exceeds PHP post_max_size (' . ini_get('post_max_size') . '). Set post_max_size=6M and upload_max_filesize=5M in php.ini, then restart PHP.');
    }
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 6 * 1024 * 1024) {
        http_response_code(413);
        throw new App\Core\ValidationException('Request is too large.');
    }
    if (
        $method === 'POST' &&
        !in_array(
            $path,
            ['/payment/ipn', '/payment/success', '/payment/fail', '/payment/cancel'],
            true,
        )
    ) {
        if (
            !is_string($_POST['_token'] ?? null) ||
            !hash_equals($_SESSION['csrf'] ?? bin2hex(random_bytes(32)), $_POST['_token'])
        ) {
            http_response_code(419);
            throw new App\Core\ValidationException(
                'Your session expired. Reload the page and try again.',
            );
        }
    }
    require ROOT . '/routes/web.php';
} catch (App\Core\ValidationException $e) {
    if (http_response_code() < 400) {
        http_response_code(422);
    }
    if (str_starts_with($path, '/control-center/media/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    } else view('error', ['title' => 'Please check your request', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    view('error', [
        'title' => 'Something went wrong',
        'message' =>
            'Please try again. If this is a new installation, run php bin/console install.',
    ]);
}
