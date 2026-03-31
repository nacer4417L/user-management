<?php
namespace App\Services;

use App\Core\AuthInterface;

/**
 * Wraps PHP sessions around the AuthInterface login / logout contract.
 */
class AuthService {
    private UserRepository $repo;

    public function __construct() {
        $this->repo = new UserRepository();
    }

    /**
     * Attempt login: verify credentials, start session on success.
     * Returns an error string on failure, or empty string on success.
     */
    public function attempt(string $email, string $password): string {
        $row = $this->repo->findByEmail($email);
        if (!$row) {
            return 'No account found with that e-mail address.';
        }

        $user = $this->repo->hydrate($row);   // Admin or RegularUser object

        /** @var AuthInterface $user */
        if (!$user->login($email, $password)) {
            return 'Incorrect password.';
        }

        // Persist identity in session
        $_SESSION['user_id']   = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->userRole();
        $_SESSION['user_email']= $user->getEmail();

        return '';
    }

    /** Destroy session and call model logout (for logging). */
    public function logout(): void {
        if (!empty($_SESSION['user_id'])) {
            $row = $this->repo->findById((int)$_SESSION['user_id']);
            if ($row) {
                $user = $this->repo->hydrate($row);
                /** @var AuthInterface $user */
                $user->logout();
            }
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Return true when a user is currently logged in. */
    public function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }
}
