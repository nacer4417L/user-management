<?php
namespace App\Services;

use App\Models\Admin;
use App\Models\RegularUser;
use App\Core\AbstractUser;

/**
 * Handles all database operations for users.
 */
class UserRepository {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseService::getConnection();
    }

    /** Save a new user; throws on duplicate e-mail. */
    public function create(string $name, string $email, string $password, string $role = 'Regular User'): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
        );
        $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hash, ':role' => $role]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Find a user row by e-mail address. */
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Find a user row by ID. */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Return all users (admin view). */
    public function findAll(): array {
        return $this->pdo->query('SELECT id, name, email, role, created FROM users ORDER BY id')->fetchAll();
    }

    /** Hydrate a DB row into the correct model object. */
    public function hydrate(array $row): AbstractUser {
        if ($row['role'] === 'Admin') {
            return new Admin($row['name'], $row['email'], $row['password'], (int)$row['id']);
        }
        return new RegularUser($row['name'], $row['email'], $row['password'], (int)$row['id']);
    }

    /** Check whether e-mail is already registered. */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== null;
    }
}
