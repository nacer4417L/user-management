<?php
session_start();
require_once __DIR__ . '/autoload.php';

use App\Services\AuthService;
use App\Services\UserRepository;

$auth  = new AuthService();
$repo  = new UserRepository();
$error = '';
$success = '';

// ── Route: POST actions ───────────────────────────────────────────────────────

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'Regular User';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid e-mail address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($repo->emailExists($email)) {
        $error = 'That e-mail is already registered.';
    } else {
        $repo->create($name, $email, $password, $role);
        $success = 'Account created! You can now log in.';
    }
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $error    = $auth->attempt($email, $password);
    if (!$error) {
        header('Location: ?action=dashboard');
        exit;
    }
}

if ($action === 'logout') {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// ── Helpers ──────────────────────────────────────────────────────────────────

$loggedIn = $auth->isLoggedIn();
$page     = $loggedIn ? ($action ?: 'dashboard') : ($action ?: 'login');
if (!$loggedIn && !in_array($page, ['login', 'register'], true)) {
    $page = 'login';
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// ── HTML ─────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management System</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f4f8; color: #1a202c; min-height: 100vh; }

  /* NAV */
  nav { background: #2d3748; color: #fff; display: flex; align-items: center; justify-content: space-between;
        padding: 0 2rem; height: 56px; }
  nav .brand { font-weight: 700; font-size: 1.1rem; letter-spacing: .5px; }
  nav a { color: #cbd5e0; text-decoration: none; margin-left: 1.5rem; font-size: .9rem; transition: color .2s; }
  nav a:hover { color: #fff; }
  nav .badge { background: #e53e3e; color: #fff; font-size: .7rem; padding: 2px 7px;
               border-radius: 999px; margin-left: .4rem; vertical-align: middle; }
  nav .badge.green { background: #38a169; }

  /* MAIN */
  main { max-width: 900px; margin: 2.5rem auto; padding: 0 1rem; }

  /* CARDS */
  .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.08);
          padding: 2rem; margin-bottom: 1.5rem; }
  .card h2 { font-size: 1.3rem; margin-bottom: 1.2rem; color: #2d3748; }

  /* FORMS */
  .form-group { margin-bottom: 1rem; }
  label { display: block; font-size: .85rem; font-weight: 600; color: #4a5568; margin-bottom: .3rem; }
  input[type=text], input[type=email], input[type=password], select {
    width: 100%; padding: .55rem .8rem; border: 1px solid #cbd5e0; border-radius: 6px;
    font-size: .95rem; transition: border-color .2s; }
  input:focus, select:focus { outline: none; border-color: #4299e1; }
  .btn { display: inline-block; padding: .55rem 1.3rem; border: none; border-radius: 6px;
         font-size: .95rem; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; }
  .btn-primary  { background: #4299e1; color: #fff; }
  .btn-primary:hover  { background: #3182ce; }
  .btn-danger   { background: #e53e3e; color: #fff; }
  .btn-danger:hover   { background: #c53030; }
  .btn-secondary{ background: #718096; color: #fff; }
  .btn-secondary:hover{ background: #4a5568; }

  /* ALERTS */
  .alert { padding: .7rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: .9rem; }
  .alert-error   { background: #fff5f5; border: 1px solid #fc8181; color: #c53030; }
  .alert-success { background: #f0fff4; border: 1px solid #68d391; color: #276749; }

  /* PROFILE GRID */
  .profile-grid { display: grid; grid-template-columns: auto 1fr; gap: .4rem 1.2rem; font-size: .95rem; }
  .profile-grid .key { font-weight: 600; color: #718096; }
  .role-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: .78rem;
                font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
  .role-admin { background: #ebf8ff; color: #2b6cb0; }
  .role-user  { background: #f0fff4; color: #276749; }

  /* TABLE */
  table { width: 100%; border-collapse: collapse; font-size: .9rem; }
  th { background: #edf2f7; text-align: left; padding: .5rem .8rem; font-weight: 700; color: #4a5568; }
  td { padding: .5rem .8rem; border-bottom: 1px solid #e2e8f0; }
  tr:last-child td { border: none; }
  tr:hover td { background: #f7fafc; }

  /* TABS */
  .tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
  .tabs a { padding: .45rem 1rem; border-radius: 6px; font-size: .9rem; font-weight: 600;
             text-decoration: none; color: #4a5568; background: #edf2f7; }
  .tabs a.active { background: #4299e1; color: #fff; }

  .split { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  @media (max-width: 600px) { .split { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<nav>
  <span class="brand">🔐 User Management</span>
  <div>
    <?php if ($loggedIn): ?>
      <span style="color:#a0aec0;font-size:.85rem">
        <?= h($_SESSION['user_name']) ?>
        <span class="badge <?= $_SESSION['user_role'] === 'Admin' ? '' : 'green' ?>">
          <?= h($_SESSION['user_role']) ?>
        </span>
      </span>
      <a href="?action=dashboard">Dashboard</a>
      <?php if ($_SESSION['user_role'] === 'Admin'): ?>
        <a href="?action=users">All Users</a>
      <?php endif; ?>
      <a href="?action=logout">Logout</a>
    <?php else: ?>
      <a href="?action=login">Login</a>
      <a href="?action=register">Register</a>
    <?php endif; ?>
  </div>
</nav>

<main>

<?php // ── LOGIN PAGE ──────────────────────────────────────────────────────────
if ($page === 'login'): ?>

  <div class="card" style="max-width:420px;margin:0 auto">
    <h2>Sign In</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
    <form method="POST" action="?action=login">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="••••••••">
      </div>
      <button class="btn btn-primary" style="width:100%">Sign In</button>
    </form>
    <p style="margin-top:1rem;font-size:.88rem;color:#718096;text-align:center">
      No account? <a href="?action=register" style="color:#4299e1">Register here</a>
    </p>
    <hr style="margin:1.2rem 0;border-color:#e2e8f0">
    <p style="font-size:.82rem;color:#a0aec0">
      <strong>Demo accounts (pre-seeded):</strong><br>
      Admin — alice@example.com / admin123<br>
      User &nbsp;— bob@example.com / user123
    </p>
  </div>

<?php // ── REGISTER PAGE ───────────────────────────────────────────────────────
elseif ($page === 'register'): ?>

  <div class="card" style="max-width:440px;margin:0 auto">
    <h2>Create Account</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
    <form method="POST" action="?action=register">
      <input type="hidden" name="action" value="register">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" required placeholder="Jane Doe">
      </div>
      <div class="form-group">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Password <span style="font-weight:400;color:#a0aec0">(min 6 chars)</span></label>
        <input type="password" name="password" required placeholder="••••••••">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="Regular User">Regular User</option>
          <option value="Admin">Admin</option>
        </select>
      </div>
      <button class="btn btn-primary" style="width:100%">Register</button>
    </form>
    <p style="margin-top:1rem;font-size:.88rem;color:#718096;text-align:center">
      Already have an account? <a href="?action=login" style="color:#4299e1">Sign in</a>
    </p>
  </div>

<?php // ── DASHBOARD ───────────────────────────────────────────────────────────
elseif ($page === 'dashboard'): ?>

  <h1 style="font-size:1.5rem;margin-bottom:1.3rem;color:#2d3748">
    Welcome back, <?= h($_SESSION['user_name']) ?> 👋
  </h1>

  <div class="split">
    <div class="card">
      <h2>Your Profile</h2>
      <div class="profile-grid">
        <span class="key">Name</span>  <span><?= h($_SESSION['user_name']) ?></span>
        <span class="key">E-mail</span><span><?= h($_SESSION['user_email']) ?></span>
        <span class="key">Role</span>
        <span>
          <span class="role-badge <?= $_SESSION['user_role'] === 'Admin' ? 'role-admin' : 'role-user' ?>">
            <?= h($_SESSION['user_role']) ?>
          </span>
        </span>
      </div>
      <a href="?action=logout" class="btn btn-danger" style="margin-top:1.4rem">Logout</a>
    </div>

    <div class="card">
      <h2>Permissions</h2>
      <?php if ($_SESSION['user_role'] === 'Admin'): ?>
        <ul style="padding-left:1.2rem;line-height:2">
          <li>✅ View all registered users</li>
          <li>✅ Full admin dashboard</li>
          <li>✅ Activity is logged</li>
        </ul>
        <a href="?action=users" class="btn btn-primary" style="margin-top:1.2rem">Manage Users →</a>
      <?php else: ?>
        <ul style="padding-left:1.2rem;line-height:2">
          <li>✅ View your own profile</li>
          <li>🚫 No admin access</li>
          <li>✅ Activity is logged</li>
        </ul>
      <?php endif; ?>
    </div>
  </div>

<?php // ── ADMIN: ALL USERS ────────────────────────────────────────────────────
elseif ($page === 'users'): ?>

  <?php if ($_SESSION['user_role'] !== 'Admin'): ?>
    <div class="alert alert-error">⛔ Access denied. Admins only.</div>
  <?php else: ?>
    <h1 style="font-size:1.4rem;margin-bottom:1.2rem;color:#2d3748">All Registered Users</h1>
    <div class="card">
      <?php $users = $repo->findAll(); ?>
      <?php if (empty($users)): ?>
        <p style="color:#718096">No users found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>ID</th><th>Name</th><th>E-mail</th><th>Role</th><th>Registered</th></tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= h($u['name']) ?></td>
              <td><?= h($u['email']) ?></td>
              <td>
                <span class="role-badge <?= $u['role'] === 'Admin' ? 'role-admin' : 'role-user' ?>">
                  <?= h($u['role']) ?>
                </span>
              </td>
              <td style="color:#718096;font-size:.85rem"><?= h($u['created']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

</main>
</body>
</html>
