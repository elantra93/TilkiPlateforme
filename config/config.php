<?php
require_once __DIR__ . '/env.php';
load_env(dirname(__DIR__) . '/.env');

return [
    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => env('DB_NAME', ''),
        'user'    => env('DB_USER', ''),
        'pass'    => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'               => 'TILKI Portail Client',
        'url'                => 'https://tilki.digital',
        'env'                => env('APP_ENV', 'production'),
        'max_login_attempts' => 5,
        'lockout_minutes'    => 15,
    ],
    'storage' => [
        'documents' => dirname(__DIR__) . '/storage/documents',
        'logs'      => dirname(__DIR__) . '/storage/logs',
    ],
    'mail' => [
        'from'      => env('MAIL_FROM', 'noreply@tilki.digital'),
        'from_name' => 'TILKI Portail Client',
        'smtp' => [
            'host'   => env('MAIL_SMTP_HOST', ''),
            'port'   => (int) env('MAIL_SMTP_PORT', 587),
            'user'   => env('MAIL_SMTP_USER', ''),
            'pass'   => env('MAIL_SMTP_PASS', ''),
            'secure' => env('MAIL_SMTP_SECURE', 'tls'),
        ],
    ],
    'tally' => [
        'secret'         => env('TALLY_SECRET', ''),
        'claim_form_url' => env('TALLY_CLAIM_FORM_URL', ''),
    ],
];
