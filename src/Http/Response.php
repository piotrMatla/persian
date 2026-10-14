<?php

declare(strict_types=1);

namespace EWnioski\Http;

/**
 * Prosta odpowiedź HTTP. Przekierowania robimy nagłówkiem Location,
 * a nie `<script>window.location</script>` jak w starej wersji (26 miejsc).
 */
class Response
{
    /**
     * @param array<string,string> $headers
     */
    public function __construct(
        private string $body = '',
        private int $status = 200,
        private array $headers = ['Content-Type' => 'text/html; charset=utf-8'],
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status);
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['Location' => $location]);
    }

    public function withHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }
        echo $this->body;
    }
}
