<?php

declare(strict_types=1);

/**
 * Wspólny bootstrap dla front controllera i skryptów CLI.
 * Zwraca gotowy kontener zależności (Container).
 */

use EWnioski\Support\Autoloader;
use EWnioski\Support\Env;

// Autoloader: preferuj Composer, w razie jego braku użyj własnego PSR-4.
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    require __DIR__ . '/src/Support/Autoloader.php';
    Autoloader::register(__DIR__ . '/src');
}

Env::load(__DIR__ . '/.env');

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Europe/Warsaw'));

$config = require __DIR__ . '/config/config.php';

return new EWnioski\Kernel\Container($config);
