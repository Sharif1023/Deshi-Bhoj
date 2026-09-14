<?php
declare(strict_types=1);
namespace App\Core;
final class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        $u = DB::one('SELECT id,name,email,role,session_version FROM users WHERE id=?', [
            $_SESSION['user_id'],
        ]);
        if (
            !$u ||
            $u['session_version'] !== ($_SESSION['version'] ?? null) ||
            ($_SESSION['expires'] ?? 0) < time()
        ) {
            unset($_SESSION['user_id']);
            return null;
        }
        return $u;
    }
    public static function requireUser(bool $owner = false): array
    {
        $u = self::user();
        if (!$u) {
            redirect('/login');
        }
        if ($owner && $u['role'] !== 'owner') {
            http_response_code(403);
            throw new ValidationException('Owner access required.');
        }
        return $u;
    }
    public static function throttle(string $key, int $max = 8, int $seconds = 900): void
    {
        $bucket = hash('sha256', $key);
        $now = time();
        DB::run('DELETE FROM rate_limits WHERE expires_at < ?', [$now]);
        $sql = 'INSERT INTO rate_limits (bucket,hits,expires_at) VALUES (?,1,?) ON DUPLICATE KEY UPDATE hits=hits+1';
        DB::run($sql, [$bucket, $now + $seconds]);
        $r = DB::one('SELECT hits FROM rate_limits WHERE bucket=?', [$bucket]);
        if ((int) $r['hits'] > $max) {
            http_response_code(429);
            throw new ValidationException('Too many attempts. Please try again later.');
        }
    }
    public static function password(string $p): string
    {
        if (strlen($p) < 12 || strlen($p) > 200) {
            throw new ValidationException('Use a password between 12 and 200 characters.');
        }
        return password_hash($p, PASSWORD_DEFAULT);
    }
}
