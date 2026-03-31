<?php
namespace App\Services;

/**
 * Manages the MySQL database connection (singleton).
 * Compatible with XAMPP default settings.
 */
class DatabaseService {
    private static ?\PDO $pdo = null;

    // ── Change these if your XAMPP settings are different ──
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'user_management';
    private const DB_USER = 'root';
    private const DB_PASS = '';   // XAMPP default: no password

    public static function getConnection(): \PDO {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . self::DB_HOST
                 . ';dbname=' . self::DB_NAME
                 . ';charset=utf8mb4';

            self::$pdo = new \PDO($dsn, self::DB_USER, self::DB_PASS);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }
}
