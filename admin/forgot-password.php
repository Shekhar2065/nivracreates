<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$db = admin_db();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $stmt = $db->prepare('SELECT id, recovery_phone FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
    $stmt->execute([$identifier, strtolower($identifier)]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = 'We could not start password recovery. Check the account details and try again.';
    } else {
        $rate = $db->prepare('SELECT created_at FROM password_reset_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $rate->execute([(int) $user['id']]);
        $last = $rate->fetchColumn();
        if ($last && strtotime((string) $last) > time() - 60) {
            $error = 'Please wait one minute before requesting another code.';
        } else {
            $result = admin_twilio_verify((string) $user['recovery_phone']);
            if (!$result['ok']) {
                $error = $result['message'];
            } else {
                $requestKey = bin2hex(random_bytes(32));
                $insert = $db->prepare('INSERT INTO password_reset_requests (user_id, request_key_hash, ip_hash, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
                $insert->execute([(int) $user['id'], hash('sha256', $requestKey), hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))]);
                $_SESSION['password_reset_id'] = (int) $db->lastInsertId();
                $_SESSION['password_reset_key'] = $requestKey;
                admin_redirect('verify-otp.php');
            }
        }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recover Nivra Admin</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth-page"><main class="auth-card"><a class="auth-logo" href="login.php"><span class="brand-mark">N</span>Nivra</a><h1>Reset password</h1><p>Enter your admin username or email. We’ll send an OTP to the owner recovery number.</p><?php if ($error): ?><div class="notice error"><?= admin_e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>"><div class="field"><label for="identifier">Username or email</label><input id="identifier" name="identifier" required autocomplete="username"></div><button class="button acid" type="submit">Send OTP</button></form><div class="auth-links"><a href="login.php">← Back to sign in</a></div></main></body></html>
