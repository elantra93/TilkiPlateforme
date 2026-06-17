<?php
return [
    'db' => [
        'host'    => '127.0.0.1',   // Sur Hostinger : utiliser 'localhost'
        'port'    => 3306,
        'name'    => 'CHANGE_ME',   // Format Hostinger : u123456789_nombase
        'user'    => 'CHANGE_ME',   // Format Hostinger : u123456789_nomuser
        'pass'    => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'               => 'TILKI Portail Client',
        'url'                => 'https://www.tilkiconseils.com',
        'env'                => 'production', // development | production
        'max_login_attempts' => 5,
        'lockout_minutes'    => 15,
    ],
    'storage' => [
        // dirname(__DIR__) pointe vers tilki_app/ sur Hostinger
        'documents' => dirname(__DIR__) . '/storage/documents',
        'logs'      => dirname(__DIR__) . '/storage/logs',
    ],
    'mail' => [
        'from'      => 'noreply@tilkiconseils.com', // Doit correspondre à un email créé sur Hostinger
        'from_name' => 'TILKI Portail Client',
        'smtp' => [
            // Laisser 'host' vide ('') pour utiliser PHP mail() à la place de SMTP
            'host'   => 'smtp.hostinger.com',
            'port'   => 587,
            'user'   => 'noreply@tilkiconseils.com', // Email Hostinger
            'pass'   => 'CHANGE_ME',
            'secure' => 'tls', // 'tls' (port 587) ou 'ssl' (port 465)
        ],
    ],
    'tally' => [
        'secret'         => 'CHANGE_ME',       // Secret partagé Tally (webhook signing key)
        'claim_form_url' => 'https://tally.so/r/XXXXX', // URL du formulaire de déclaration de sinistre
    ],
];
