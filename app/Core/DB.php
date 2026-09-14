<?php
declare(strict_types=1);
namespace App\Core;
use PDO;
final class DB
{
    private static ?PDO $pdo = null;
    public static function connection(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }
        if (env('DB_CONNECTION', 'mysql') !== 'mysql') throw new \RuntimeException('This edition requires MySQL. Configure .env before installation.');
        $name = env('DB_DATABASE', 'koji');
        $dsn = 'mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';dbname='.$name.';charset=utf8mb4';
        self::$pdo = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }
    public static function run(string $sql, array $args = []): \PDOStatement
    {
        $s = self::connection()->prepare($sql);
        $s->execute($args);
        return $s;
    }
    public static function all(string $sql, array $args = []): array
    {
        return self::run($sql, $args)->fetchAll();
    }
    public static function one(string $sql, array $args = []): ?array
    {
        return self::run($sql, $args)->fetch() ?: null;
    }
    public static function transaction(callable $fn): mixed
    {
        $p = self::connection();
        $p->beginTransaction();
        try {
            $r = $fn();
            $p->commit();
            return $r;
        } catch (\Throwable $e) {
            $p->rollBack();
            throw $e;
        }
    }
    public static function insert(string $table, array $values): int
    {
        $keys = array_keys($values);
        self::run(
            'INSERT INTO ' .
                $table .
                ' (' .
                implode(',', $keys) .
                ') VALUES (' .
                implode(',', array_fill(0, count($keys), '?')) .
                ')',
            array_values($values),
        );
        return (int) self::connection()->lastInsertId();
    }
    public static function audit(string $action): void
    {
        self::insert('activities', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
