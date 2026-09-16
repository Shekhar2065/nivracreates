<?php
declare(strict_types=1);

return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'nivra_portfolio',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'app_url' => 'http://localhost/Nivra',
    'sms' => [
        'provider' => 'twilio_verify',
        'twilio_account_sid' => '',
        'twilio_auth_token' => '',
        'twilio_verify_service_sid' => '',
    ],
];
