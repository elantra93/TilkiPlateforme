<?php
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'tilki_portal',
        'user'    => 'tilki_user',
        'pass'    => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'              => 'TILKI Portail Client',
        'url'               => 'https://yourdomain.com',
        'env'               => 'production', // development | production
        'max_login_attempts'=> 5,
        'lockout_minutes'   => 15,
    ],
    'storage' => [
        'documents' => dirname(__DIR__) . '/storage/documents',
        'logs'      => dirname(__DIR__) . '/storage/logs',
    ],
    'tally' => [
        'secret'         => 'CHANGE_ME',        // Secret partagé Tally (webhook signing key)
        'claim_form_url' => 'https://tally.so/r/XXXXX',  // URL du formulaire de déclaration de sinistre
    ],
];
