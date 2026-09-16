<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$db = admin_db();
try {
    $hasAdmin = (int) $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
} catch (PDOException) {
    exit('Admin tables are missing. Import database/nivra_portfolio.sql first.');
}
if ($hasAdmin) {
    admin_redirect('login.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/', $username)) {
        $error = 'Use 3–40 letters, numbers, dots, dashes, or underscores for the username.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 12) {
        $error = 'Use a password with at least 12 characters.';
    } elseif (!hash_equals($password, (string) ($_POST['password_confirmation'] ?? ''))) {
        $error = 'The passwords do not match.';
    } else {
        $stmt = $db->prepare('INSERT INTO admin_users (username, email, password_hash, recovery_phone) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), '+9779845895222']);
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = (int) $db->lastInsertId();
        $_SESSION['admin_last_activity'] = time();
        admin_flash('success', 'Owner account created. Welcome to Nivra Admin.');
        admin_redirect('index.php');
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Nivra admin</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth-page"><main class="auth-card"><a class="auth-logo" href="../index.php"><span class="brand-mark">N</span>Nivra</a><h1>Create owner account</h1><p>This one-time setup locks automatically after the first administrator is created.</p>
<?php if ($error): ?><div class="notice error"><?= admin_e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><div class="field"><label for="username">Username</label><input id="username" name="username" required autocomplete="username" value="<?= admin_e((string) ($_POST['username'] ?? '')) ?>"></div><div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="email" value="<?= admin_e((string) ($_POST['email'] ?? '')) ?>"></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password"><small>Minimum 12 characters.</small></div><div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></div><div class="field"><label>Recovery phone</label><input value="+977 9845895222" disabled><small>OTP password recovery is assigned to this number.</small></div><button class="button acid" type="submit">Create secure account</button></form></main></body></html>
