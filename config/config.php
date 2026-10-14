<?php

declare(strict_types=1);

use EWnioski\Support\Env;

/**
 * Konfiguracja aplikacji budowana wyłącznie ze zmiennych środowiskowych.
 * Żadnych danych dostępowych ani ścieżek zaszytych w kodzie.
 */
return [
    'app' => [
        'env' => Env::get('APP_ENV', 'prod'),
        'debug' => Env::bool('APP_DEBUG', false),
        'url' => rtrim(Env::get('APP_URL', ''), '/'),
        'timezone' => Env::get('APP_TIMEZONE', 'Europe/Warsaw'),
    ],

    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => (int) Env::get('DB_PORT', '3306'),
            'database' => Env::get('DB_NAME', 'ewnioski'),
            'username' => Env::get('DB_USER', ''),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
        ],
        'rcp' => [
            'enabled' => Env::bool('RCP_ENABLED', false),
            'driver' => 'sqlsrv',
            'host' => Env::get('RCP_HOST', ''),
            'database' => Env::get('RCP_DATABASE', ''),
            'username' => Env::get('RCP_USER', ''),
            'password' => Env::get('RCP_PASSWORD', ''),
        ],
    ],

    'mail' => [
        'from' => Env::get('MAIL_FROM', 'ewnioski.admin@example.local'),
        'it_alerts' => Env::get('MAIL_IT_ALERTS', ''),
    ],

    'storage' => [
        'logs' => Env::get('STORAGE_LOGS', 'var/logs'),
        'uploads' => Env::get('STORAGE_UPLOADS', 'var/uploads'),
        'uploads_managers' => Env::get('STORAGE_UPLOADS_MANAGERS', 'var/uploads-managers'),
    ],
];
