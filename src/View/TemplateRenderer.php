<?php

declare(strict_types=1);

namespace EWnioski\View;

/**
 * Minimalny silnik szablonów PHP z automatycznym escapowaniem przez helper e().
 *
 * Stara wersja mieszała HTML/SQL/logikę i wypisywała dane bez escapowania
 * (echo wprost do HTML => XSS). Tutaj widoki są oddzielone, a helper e()
 * wymusza htmlspecialchars.
 */
final class TemplateRenderer
{
    /**
     * @param array<string,mixed> $appConfig
     */
    public function __construct(
        private readonly string $templateDir,
        private readonly array $appConfig = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $file = $this->templateDir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Brak szablonu: {$template}");
        }

        $e = static fn (mixed $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
        $app = $this->appConfig;

        ob_start();
        (function () use ($file, $data, $e, $app): void {
            extract($data, EXTR_SKIP);
            require $file;
        })();

        return (string) ob_get_clean();
    }
}
