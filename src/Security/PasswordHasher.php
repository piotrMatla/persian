<?php

declare(strict_types=1);

namespace EWnioski\Security;

/**
 * Haszowanie haseł (bcrypt/argon przez password_hash).
 *
 * Stara wersja trzymała hasła jawnym tekstem (`haslo_urlopy`),
 * porównywała je `==`, wysyłała mailem i logowała. Tutaj:
 *  - nowe hasła są haszowane,
 *  - istnieje ścieżka migracji starych haseł jawnych („rehash on login"),
 *  - porównanie jest odporne na timing (password_verify / hash_equals).
 */
final class PasswordHasher
{
    private const ALGO = PASSWORD_DEFAULT;

    public function hash(string $plain): string
    {
        return password_hash($plain, self::ALGO);
    }

    public function verify(string $plain, string $hash): bool
    {
        // Rozpoznanie starego hasła jawnego (brak prefiksu algorytmu bcrypt/argon).
        if (!$this->looksHashed($hash)) {
            return hash_equals($hash, $plain);
        }

        return password_verify($plain, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        if (!$this->looksHashed($hash)) {
            return true; // stare hasło jawne – trzeba przehaszować przy pierwszym logowaniu
        }

        return password_needs_rehash($hash, self::ALGO);
    }

    private function looksHashed(string $hash): bool
    {
        return str_starts_with($hash, '$2y$')
            || str_starts_with($hash, '$argon2i$')
            || str_starts_with($hash, '$argon2id$');
    }
}
