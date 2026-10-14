<?php

declare(strict_types=1);

namespace EWnioski\Security;

/**
 * Ochrona CSRF – token na sesję, weryfikowany przy każdym POST.
 * Stara wersja nie miała żadnej ochrony CSRF.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public function isValid(?string $token): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);

        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }

    /**
     * Gotowe pole hidden do wstawienia w formularzu.
     */
    public function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($this->token(), ENT_QUOTES) . '">';
    }
}
