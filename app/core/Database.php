<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
            ]);
            // Keep CURDATE()/NOW() in Saudi time, same as PHP.
            self::$pdo->exec("SET time_zone = '+03:00'");
        }
        return self::$pdo;
    }

    /** Run a prepared statement and return it. */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $v = self::query($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function transaction(callable $fn): mixed
    {
        $pdo = self::connect();
        $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            // The callback may already have rolled back (e.g. a business rule failed); only commit an open transaction.
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
