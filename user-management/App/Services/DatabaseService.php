<?php
namespace App\Services;

/**
 * Manages the SQLite database connection (singleton).
 * Uses PDO + SQLite so the project runs without any external server.
 */
class DatabaseService {
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../../database/users.db';
            $dbDir  = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            self::$pdo = new \PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            self::createTables(self::$pdo);
        }
        return self::$pdo;
    }

    private static function createTables(\PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                name     TEXT    NOT NULL,
                email    TEXT    NOT NULL UNIQUE,
                password TEXT    NOT NULL,
                role     TEXT    NOT NULL DEFAULT 'Regular User',
                created  TEXT    NOT NULL DEFAULT (datetime('now'))
            )
        ");
    }
}
