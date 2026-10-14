<?php

declare(strict_types=1);

namespace EWnioski\Security;

use EWnioski\Domain\User\User;
use EWnioski\Repository\UserRepository;

/**
 * Logika logowania/wylogowania. Zastępuje log.php:
 *  - zapytanie przygotowane (brak SQL injection na loginie),
 *  - weryfikacja hasła przez password_verify,
 *  - migracja starego hasła jawnego na hash przy pierwszym poprawnym logowaniu,
 *  - regeneracja identyfikatora sesji,
 *  - brak logowania/odsyłania hasła.
 */
final class Authenticator
{
    private const SESSION_USER = 'auth_user_number';

    private readonly PasswordHasher $hasher;

    public function __construct(
        private readonly UserRepository $users,
        private readonly Session $session,
    ) {
        $this->hasher = new PasswordHasher();
    }

    public function attempt(string $employeeNumber, string $password): ?User
    {
        $user = $this->users->findByEmployeeNumber($employeeNumber);
        if ($user === null || $user->passwordHash === null || $user->passwordHash === '') {
            return null;
        }

        if (!$this->hasher->verify($password, $user->passwordHash)) {
            return null;
        }

        // Przezroczysta migracja: stare hasło jawne -> hash, bcrypt -> nowszy koszt.
        if ($this->hasher->needsRehash($user->passwordHash)) {
            $this->users->updatePasswordHash($user->id, $this->hasher->hash($password));
        }

        $this->session->regenerate();
        $this->session->set(self::SESSION_USER, $user->employeeNumber);

        return $user;
    }

    /**
     * Odtwarza kontekst z sesji na początku żądania.
     */
    public function currentContext(): AuthContext
    {
        $number = $this->session->get(self::SESSION_USER);
        if (!is_string($number) || $number === '') {
            return new AuthContext(null);
        }

        return new AuthContext($this->users->findByEmployeeNumber($number));
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_USER);
        $this->session->regenerate();
        $this->session->destroy();
    }
}
