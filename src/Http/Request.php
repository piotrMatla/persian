<?php

declare(strict_types=1);

namespace EWnioski\Http;

/**
 * Opakowanie na dane żądania. Nic nie czyta bezpośrednio z superglobali
 * w kontrolerach – wszystko przechodzi przez jawne, otypowane akcesory.
 */
final class Request
{
    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $post
     * @param array<string,mixed> $server
     */
    public function __construct(
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);

        return is_string($path) ? $path : '/';
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $this->query[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post) || array_key_exists($key, $this->query);
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function trimmed(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->input($key);

        return $value !== null && $value !== '' ? (int) $value : $default;
    }

    public function clientIp(): string
    {
        // Uwaga: X-Forwarded-For jest sfałszowalny. Ufamy mu tylko, jeśli
        // aplikacja stoi za zaufanym reverse-proxy (konfiguracja wdrożenia).
        $remote = (string) ($this->server['REMOTE_ADDR'] ?? 'unknown');

        return $remote;
    }
}
