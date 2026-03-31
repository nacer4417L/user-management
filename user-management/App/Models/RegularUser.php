<?php
namespace App\Models;

use App\Core\AbstractUser;
use App\Core\AuthInterface;
use App\Core\LoggerTrait;

class RegularUser extends AbstractUser implements AuthInterface {
    use LoggerTrait;

    public function userRole(): string {
        return 'Regular User';
    }

    public function login(string $email, string $password): bool {
        if ($email === $this->email && password_verify($password, $this->password)) {
            $this->logActivity("User '{$this->name}' logged in.");
            return true;
        }
        return false;
    }

    public function logout(): void {
        $this->logActivity("User '{$this->name}' logged out.");
    }
}
