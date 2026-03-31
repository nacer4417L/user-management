<?php
/**
 * seed.php — run once to create the two demo accounts.
 * Usage: php seed.php
 */
require_once __DIR__ . '/autoload.php';

use App\Services\UserRepository;

$repo = new UserRepository();

$accounts = [
    ['Alice', 'alice@example.com', 'admin123', 'Admin'],
    ['Bob',   'bob@example.com',   'user123',  'Regular User'],
];

foreach ($accounts as [$name, $email, $pass, $role]) {
    if ($repo->emailExists($email)) {
        echo "  [SKIP] $email already exists.\n";
    } else {
        $id = $repo->create($name, $email, $pass, $role);
        echo "  [OK]   Created $role '$name' (id=$id)\n";
    }
}

echo "\nDone. Database stored at: " . realpath(__DIR__ . '/database/users.db') . "\n";
