<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$db = admin_db();
$requestId = (int) ($_SESSION['password_reset_id'] ?? 0);
$requestKey = (string) ($_SESSION['password_reset_key'] ?? '');
$stmt = $db->prepare('SELECT r.*, u.recovery_phone FROM password_reset_requests r JOIN admin_users u ON u.id = r.user_id WHERE r.id = ? AND r.used_at IS NULL AND r.expires_at > NOW()');
$stmt->execute([$requestId]);
$request = $stmt->fetch();
if (!$request || $requestKey === '' || !hash_equals((string) $request['request_key_hash'], hash('sha256', $requestKey))) {
    admin_flash('error', 'That recovery request expired. Please request a new OTP.');
    admin_redirect('forgot-password.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? '')) ?? '';
    if ((int) $request['attempts'] >= 5) {
        $error = 'Too many incorrect codes. Start a new recovery request.';
    } elseif (!preg_match('/^\d{4,10}$/', $code)) {
        $error = 'Enter the numeric code from the SMS.';
    } else {
        $db->prepare('UPDATE password_reset_requests SET attempts = attempts + 1 WHERE id = ?')->execute([$requestId]);
        $result = admin_twilio_verify((string) $request['recovery_phone'], $code);
        if (!$result['ok']) {
            $error = $result['message'] ?: 'That code is incorrect or expired.';
        } else {
            $resetToken = bin2hex(random_bytes(32));
            $db->prepare('UPDATE password_reset_requests SET verified_at = NOW(), reset_token_hash = ? WHERE id = ?')->execute([hash('sha256', $resetToken), $requestId]);
            $_SESSION['password_reset_token'] = $resetToken;
            admin_redirect('reset-password.php');
        }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify OTP · Nivra Admin</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth-page"><main class="auth-card"><a class="auth-logo" href="login.php"><span class="brand-mark">N</span>Nivra</a><h1>Enter your OTP</h1><p>A verification code was sent to <?= admin_e(admin_mask_phone((string) $request['recovery_phone'])) ?>. It expires in 10 minutes.</p><?php if ($error): ?><div class="notice error"><?= admin_e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><div class="field"><label for="code">SMS code</label><input id="code" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="10"></div><button class="button acid" type="submit">Verify code</button></form><div class="auth-links"><a href="forgot-password.php">Request a new code</a></div></main></body></html>
