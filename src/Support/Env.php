<?php

declare(strict_types=1);

namespace EWnioski\Support;

/**
 * Minimalny loader pliku .env (bez zależności zewnętrznych).
 *
 * Zastępuje zaszyte na stałe dane dostępowe ze starej wersji
 * (Common::MysqliConnect, confe.ini, hardkodowane ścieżki /home/lora/...).
 */
final class Env
{
    /** @var array<string,string> */
    private static array $values = [];

    private static bool $loaded = false;

    public static function load(string $path): void
    {
        self::$loaded = true;

        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Zdejmij opcjonalne cudzysłowy.
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")) {
                $value = substr($value, 1, -1);
            }
            self::$values[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (!self::$loaded) {
            // Fallback na realne środowisko, jeśli nie wczytano pliku.
            $fromEnv = getenv($key);
            return $fromEnv === false ? $default : $fromEnv;
        }

        return self::$values[$key] ?? (getenv($key) !== false ? (string) getenv($key) : $default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function require(string $key): string
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            throw new \RuntimeException("Brak wymaganej zmiennej środowiskowej: {$key}");
        }

        return $value;
    }
}
