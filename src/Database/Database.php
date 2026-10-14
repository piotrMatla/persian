<?php

declare(strict_types=1);

namespace EWnioski\Database;

use PDO;
use RuntimeException;

/**
 * Fabryka połączeń PDO. Jedno połączenie na nazwaną bazę (lazy).
 *
 * Zmiany względem starej wersji:
 *  - PDO zamiast mysqli, wyjątki zamiast `OR DIE(mysqli_error())`,
 *  - ERRMODE_EXCEPTION – błędy nie wyciekają na ekran użytkownika,
 *  - utf8mb4, dane dostępowe z konfiguracji (środowiska), nie z kodu.
 */
final class Database
{
    /** @var array<string,PDO> */
    private array $connections = [];

    /**
     * @param array<string,array<string,mixed>> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function connection(string $name = 'default'): PDO
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $cfg = $this->config[$name] ?? throw new RuntimeException("Nieznane połączenie bazy: {$name}");

        return $this->connections[$name] = match ($cfg['driver']) {
            'mysql' => $this->makeMysql($cfg),
            'sqlsrv' => $this->makeSqlsrv($cfg, $name),
            default => throw new RuntimeException("Nieobsługiwany sterownik: {$cfg['driver']}"),
        };
    }

    public function rcpAvailable(): bool
    {
        return (bool) ($this->config['rcp']['enabled'] ?? false) && extension_loaded('pdo_sqlsrv');
    }

    /**
     * @param array<string,mixed> $cfg
     */
    private function makeMysql(array $cfg): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset'],
        );

        return new PDO($dsn, (string) $cfg['username'], (string) $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /**
     * @param array<string,mixed> $cfg
     */
    private function makeSqlsrv(array $cfg, string $name): PDO
    {
        if (!($cfg['enabled'] ?? false)) {
            throw new RuntimeException("Połączenie RCP ({$name}) jest wyłączone w konfiguracji (RCP_ENABLED=false).");
        }
        if (!extension_loaded('pdo_sqlsrv')) {
            throw new RuntimeException('Brak rozszerzenia pdo_sqlsrv – moduł RCP (MSSQL) jest niedostępny na tym środowisku.');
        }

        $dsn = sprintf('sqlsrv:Server=%s;Database=%s', $cfg['host'], $cfg['database']);

        return new PDO($dsn, (string) $cfg['username'], (string) $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
