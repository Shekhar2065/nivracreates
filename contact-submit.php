<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require_once __DIR__ . '/config/database.php';

function redirect_to_form(): never
{
    header('Location: index.php#contact', true, 303);
    exit;
}

function clean_input(mixed $value, int $maxLength): string
{
    if (!is_string($value)) {
        return '';
    }
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return function_exists('mb_substr')
        ? mb_substr($value, 0, $maxLength, 'UTF-8')
        : substr($value, 0, $maxLength);
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$old = [
    'name' => clean_input($_POST['name'] ?? '', 100),
    'email' => clean_input($_POST['email'] ?? '', 190),
    'phone' => clean_input($_POST['phone'] ?? '', 30),
    'company' => clean_input($_POST['company'] ?? '', 150),
    'service' => clean_input($_POST['service'] ?? '', 80),
    'message' => clean_input($_POST['message'] ?? '', 3000),
];
$_SESSION['form_old'] = $old;

// Bots receive a neutral success response, but their message is not stored.
if (clean_input($_POST['website'] ?? '', 250) !== '') {
    $_SESSION['form_flash'] = ['type' => 'success', 'message' => 'Thank you. Your inquiry has been received.'];
    unset($_SESSION['form_old']);
    redirect_to_form();
}

$token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['form_flash'] = ['type' => 'error', 'message' => 'Your session expired. Please refresh the page and try again.'];
    redirect_to_form();
}

$now = time();
if (!empty($_SESSION['last_contact_submit']) && $now - (int) $_SESSION['last_contact_submit'] < 30) {
    $_SESSION['form_flash'] = ['type' => 'error', 'message' => 'Please wait a moment before sending another inquiry.'];
    redirect_to_form();
}

$errors = [];
if (text_length($old['name']) < 2) $errors[] = 'Please enter your name.';
if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if ($old['phone'] !== '' && !preg_match('/^[0-9+()\-\s.]{7,30}$/', $old['phone'])) $errors[] = 'Please enter a valid phone number.';
$allowedServices = ['Strategy', 'Creative', 'Growth', 'Integrated campaign', 'Not sure yet'];
if (!in_array($old['service'], $allowedServices, true)) $errors[] = 'Please choose a valid service.';
if (text_length($old['message']) < 20) $errors[] = 'Please describe your project in at least 20 characters.';

if ($errors) {
    $_SESSION['form_flash'] = ['type' => 'error', 'message' => 'Nothing was saved yet.', 'errors' => $errors];
    redirect_to_form();
}

$db = nivra_db();
if (!$db) {
    $_SESSION['form_flash'] = [
        'type' => 'error',
        'message' => 'The inquiry form is temporarily unavailable. Please use the email address shown on this page.',
    ];
    redirect_to_form();
}

try {
    $statement = $db->prepare(
        'INSERT INTO contact_messages (name, email, phone, company, service, message, status) VALUES (:name, :email, :phone, :company, :service, :message, :status)'
    );
    $statement->execute([
        ':name' => $old['name'],
        ':email' => $old['email'],
        ':phone' => $old['phone'] ?: null,
        ':company' => $old['company'] ?: null,
        ':service' => $old['service'],
        ':message' => $old['message'],
        ':status' => 'new',
    ]);
    $_SESSION['last_contact_submit'] = $now;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['form_flash'] = ['type' => 'success', 'message' => 'Thanks for the context. Your inquiry was saved securely, and Nivra can now follow up using the email you provided.'];
    unset($_SESSION['form_old']);
} catch (PDOException $exception) {
    error_log('Nivra contact insert failed: ' . $exception->getMessage());
    $_SESSION['form_flash'] = ['type' => 'error', 'message' => 'We could not save your inquiry right now. Please use the email address shown on this page.'];
}

redirect_to_form();
