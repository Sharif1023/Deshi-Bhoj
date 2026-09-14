<?php
declare(strict_types=1);
define('ROOT', dirname(__DIR__, 2));
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
if (is_file(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        if (getenv(trim($k)) === false) {
            putenv(trim($k) . '=' . trim(trim($v), '"\''));
        }
    };
}
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Dhaka'));
function env(string $key, string $default = ''): string
{
    $v = getenv($key);
    return $v === false ? $default : $v;
}
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function money(int $paisa): string
{
    return 'Tk ' . number_format($paisa / 100, 2);
}
function redirect(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit();
}
function csrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="_token" value="' . e($_SESSION['csrf']) . '">';
}
function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}
function user(): ?array
{
    return App\Core\Auth::user();
}
function setting(string $key, mixed $default = ''): mixed
{
    return App\Services\Content::setting($key, $default);
}
function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = ROOT . '/resources/views/' . $name . '.php';
    ob_start();
    try {
        if ($name === 'error') {echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title??'Request error').'</title><link rel="stylesheet" href="/assets/app.css"><script src="/assets/app.js" defer></script><body><main>';require $viewFile;echo '</main></body></html>';}
        else require ROOT . '/resources/views/layout.php';
        $html=ob_get_clean();
        $isPublic = str_starts_with($name, 'store/') || ($name === 'error' && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/control-center'));
        echo $isPublic ? App\Services\Locale::html($html) : $html;
    } catch (Throwable $error) {ob_end_clean();throw $error;}
}
function input(string $key, int $max = 255, bool $required = true): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v) || strlen($v) > $max || ($required && trim($v) === '')) {
        throw new App\Core\ValidationException('Please check ' . $key . '.');
    }
    return trim($v);
}
function emailInput(): string
{
    $v = strtolower(input('email'));
    if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
        throw new App\Core\ValidationException('Enter a valid email address.');
    }
    return $v;
}
function phoneInput(): string
{
    $v = input('phone', 20);
    if (!preg_match('/^(?:\+?88)?01[3-9][0-9]{8}$/', $v)) {
        throw new App\Core\ValidationException('Enter a valid Bangladesh mobile number.');
    }
    return $v;
}
function choice(string $name, array $values): string
{
    $v = input($name);
    if (!in_array($v, $values, true)) {
        throw new App\Core\ValidationException('Invalid ' . $name . '.');
    }
    return $v;
}
function safeImage(string $value): string
{
    return preg_match('~^/(?:media/[a-f0-9]{40}\.(?:webp|jpg|png)|assets/[a-z0-9._-]+\.(?:svg|webp|png|jpg|jpeg))$~i', $value) ||
        preg_match('~^https://[^\s]+$~i', $value)
        ? $value
        : '/assets/kacchi.png';
}

function rich(mixed $value): string {return App\Services\RichText::clean((string)$value);}
function rich_inline(mixed $value): string {return App\Services\RichText::clean((string)$value, true);}
function plain(mixed $value): string {return App\Services\RichText::plain((string)$value);}
