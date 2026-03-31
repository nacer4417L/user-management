<?php
namespace App\Core;

interface AuthInterface {
    public function login(string $email, string $password): bool;
    public function logout(): void;
}
