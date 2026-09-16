<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$db = admin_db();
try { $hasAdmin = (int) $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0; } catch (PDOException) { $hasAdmin = false; }
if (!$hasAdmin) admin_redirect('setup.php');
if (admin_user()) admin_redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $blockedUntil = (int) ($_SESSION['login_blocked_until'] ?? 0);
    if ($blockedUntil > time()) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $stmt = $db->prepare('SELECT id, password_hash FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
        $stmt->execute([$identifier, strtolower($identifier)]);
        $row = $stmt->fetch();
        if ($row && password_verify((string) ($_POST['password'] ?? ''), (string) $row['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts'], $_SESSION['login_blocked_until']);
            $_SESSION['admin_user_id'] = (int) $row['id'];
            $_SESSION['admin_last_activity'] = time();
            $db->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([(int) $row['id']]);
            admin_redirect('index.php');
        }
        $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 5) $_SESSION['login_blocked_until'] = time() + 300;
        $error = 'The username/email or password is incorrect.';
    }
}
$flash = admin_take_flash();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nivra Admin sign in</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth-page"><main class="auth-card"><a class="auth-logo" href="../index.php"><span class="brand-mark">N</span>Nivra</a><h1>Welcome back</h1><p>Sign in to manage the website and inquiries.</p><?php if ($flash): ?><div class="notice <?= admin_e($flash['type']) ?>"><?= admin_e($flash['message']) ?></div><?php endif; ?><?php if ($error): ?><div class="notice error"><?= admin_e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><div class="field"><label for="identifier">Username or email</label><input id="identifier" name="identifier" required autocomplete="username"></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button class="button acid" type="submit">Sign in</button></form><div class="auth-links"><a href="forgot-password.php">Forgot password?</a><a href="../index.php">Back to site</a></div></main></body></html>
