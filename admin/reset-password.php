<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$db = admin_db();
$requestId = (int) ($_SESSION['password_reset_id'] ?? 0);
$resetToken = (string) ($_SESSION['password_reset_token'] ?? '');
$stmt = $db->prepare('SELECT * FROM password_reset_requests WHERE id = ? AND verified_at IS NOT NULL AND used_at IS NULL AND expires_at > NOW()');
$stmt->execute([$requestId]);
$request = $stmt->fetch();
if (!$request || $resetToken === '' || !hash_equals((string) $request['reset_token_hash'], hash('sha256', $resetToken))) {
    admin_flash('error', 'That recovery session expired. Please start again.');
    admin_redirect('forgot-password.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 12) {
        $error = 'Use a password with at least 12 characters.';
    } elseif (!hash_equals($password, (string) ($_POST['password_confirmation'] ?? ''))) {
        $error = 'The passwords do not match.';
    } else {
        $db->beginTransaction();
        $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), (int) $request['user_id']]);
        $db->prepare('UPDATE password_reset_requests SET used_at = NOW() WHERE id = ?')->execute([$requestId]);
        $db->prepare('UPDATE password_reset_requests SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')->execute([(int) $request['user_id']]);
        $db->commit();
        unset($_SESSION['password_reset_id'], $_SESSION['password_reset_key'], $_SESSION['password_reset_token']);
        admin_flash('success', 'Password updated. You can sign in now.');
        admin_redirect('login.php');
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Choose new password · Nivra Admin</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth-page"><main class="auth-card"><a class="auth-logo" href="login.php"><span class="brand-mark">N</span>Nivra</a><h1>Choose a new password</h1><p>Your phone has been verified. Create a fresh admin password.</p><?php if ($error): ?><div class="notice error"><?= admin_e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><div class="field"><label for="password">New password</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password"></div><div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></div><button class="button acid" type="submit">Save new password</button></form></main></body></html>
