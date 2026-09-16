<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once dirname(__DIR__) . '/config/database.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");

function admin_e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_db(): PDO
{
    $db = nivra_db();
    if (!$db) {
        http_response_code(503);
        exit('Database unavailable. Copy config/config.example.php to config/config.php, update it, and import database/nivra_portfolio.sql.');
    }
    return $db;
}

function admin_url(string $path = ''): string
{
    return '/Nivra/admin/' . ltrim($path, '/');
}

function admin_redirect(string $path): never
{
    header('Location: ' . admin_url($path));
    exit;
}

function admin_csrf(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['admin_csrf'];
}

function admin_verify_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if ($token === '' || !hash_equals(admin_csrf(), $token)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type:string,message:string}|null */
function admin_take_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return is_array($flash) ? $flash : null;
}

/** @return array<string, mixed>|null */
function admin_user(): ?array
{
    $userId = (int) ($_SESSION['admin_user_id'] ?? 0);
    $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? 0);
    if (!$userId || ($lastActivity && time() - $lastActivity > 1800)) {
        unset($_SESSION['admin_user_id'], $_SESSION['admin_last_activity']);
        return null;
    }
    $stmt = admin_db()->prepare('SELECT id, username, email, recovery_phone FROM admin_users WHERE id = ? AND is_active = 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        unset($_SESSION['admin_user_id'], $_SESSION['admin_last_activity']);
        return null;
    }
    $_SESSION['admin_last_activity'] = time();
    return $user;
}

function admin_require_auth(): array
{
    $user = admin_user();
    if (!$user) {
        admin_flash('error', 'Please sign in to continue.');
        admin_redirect('login.php');
    }
    return $user;
}

function admin_setting(string $key, string $default = ''): string
{
    static $settings;
    if (!is_array($settings)) {
        try {
            $settings = admin_db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException) {
            $settings = [];
        }
    }
    return (string) ($settings[$key] ?? $default);
}

function admin_mask_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    return strlen($digits) > 4 ? '+' . substr($digits, 0, 3) . ' ••••• ' . substr($digits, -3) : 'your recovery phone';
}

/** @return array{ok:bool,message:string,status?:string} */
function admin_twilio_verify(string $phone, ?string $code = null): array
{
    $sms = nivra_config()['sms'] ?? [];
    $sid = trim((string) ($sms['twilio_account_sid'] ?? ''));
    $token = trim((string) ($sms['twilio_auth_token'] ?? ''));
    $service = trim((string) ($sms['twilio_verify_service_sid'] ?? ''));
    if ($sid === '' || $token === '' || $service === '') {
        return ['ok' => false, 'message' => 'SMS recovery is not configured yet. Add your Twilio Verify credentials in config/config.php.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'message' => 'The PHP cURL extension is required for SMS recovery.'];
    }

    $action = $code === null ? 'Verifications' : 'VerificationCheck';
    $fields = $code === null ? ['To' => $phone, 'Channel' => 'sms'] : ['To' => $phone, 'Code' => $code];
    $curl = curl_init('https://verify.twilio.com/v2/Services/' . rawurlencode($service) . '/' . $action);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERPWD => $sid . ':' . $token,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
    ]);
    $body = curl_exec($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    $payload = is_string($body) ? json_decode($body, true) : null;
    if ($statusCode < 200 || $statusCode >= 300 || !is_array($payload)) {
        error_log('Twilio Verify request failed: HTTP ' . $statusCode . ($error ? ' / ' . $error : ''));
        return ['ok' => false, 'message' => 'The verification service could not complete the request. Please try again later.'];
    }
    $status = (string) ($payload['status'] ?? '');
    return ['ok' => $code === null ? in_array($status, ['pending', 'approved'], true) : $status === 'approved', 'message' => '', 'status' => $status];
}

function admin_upload_image(string $field, ?string $existing = null): ?string
{
    if (!isset($_FILES[$field]) || (int) $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }
    $file = $_FILES[$field];
    if ((int) $file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image upload failed or exceeds 5 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string) $file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Use a JPG, PNG, or WebP image.');
    }
    $directory = dirname(__DIR__) . '/assets/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the upload folder.');
    }
    $filename = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    return 'assets/uploads/' . $filename;
}

/** @return list<string> */
function admin_upload_images(string $field): array
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'] ?? null)) {
        return [];
    }

    $files = $_FILES[$field];
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $validated = [];
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($files['name'] as $index => $name) {
        $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $size = (int) ($files['size'][$index] ?? 0);
        $temporaryPath = (string) ($files['tmp_name'][$index] ?? '');
        if ($error !== UPLOAD_ERR_OK || $size <= 0 || $size > 5 * 1024 * 1024) {
            throw new RuntimeException('One of the gallery images failed to upload or exceeds 5 MB.');
        }
        $mime = $finfo->file($temporaryPath);
        if (!is_string($mime) || !isset($extensions[$mime])) {
            throw new RuntimeException('Gallery images must be JPG, PNG, or WebP files.');
        }
        $validated[] = ['temporary_path' => $temporaryPath, 'extension' => $extensions[$mime]];
    }

    if ($validated === []) {
        return [];
    }

    $directory = dirname(__DIR__) . '/assets/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the upload folder.');
    }

    $paths = [];
    foreach ($validated as $file) {
        $filename = bin2hex(random_bytes(12)) . '.' . $file['extension'];
        if (!move_uploaded_file($file['temporary_path'], $directory . '/' . $filename)) {
            throw new RuntimeException('Could not save one of the gallery images.');
        }
        $paths[] = 'assets/uploads/' . $filename;
    }

    return $paths;
}
