<?php

declare(strict_types=1);

namespace EWnioski\Support;

/**
 * Prosty autoloader PSR-4 na wypadek braku `composer install`
 * (np. wdrożenie offline). W normalnym wdrożeniu używany jest
 * autoloader Composera – patrz bootstrap.php.
 */
final class Autoloader
{
    public static function register(string $baseDir): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir): void {
            $prefix = 'EWnioski\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });
    }
}
