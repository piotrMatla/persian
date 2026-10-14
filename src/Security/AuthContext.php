<?php

declare(strict_types=1);

namespace EWnioski\Security;

use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\User\User;

/**
 * Bieżący kontekst uwierzytelnienia, odtwarzany z sesji przy każdym żądaniu.
 * Źródło prawdy o tym „kim jest użytkownik" – kontrolery i router nie czytają
 * $_SESSION bezpośrednio.
 */
final class AuthContext
{
    public function __construct(private readonly ?User $user)
    {
    }

    public function isLoggedIn(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function userOrFail(): User
    {
        return $this->user ?? throw new \LogicException('Brak zalogowanego użytkownika.');
    }

    /**
     * @param list<Role> $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->user !== null && in_array($this->user->role, $roles, true);
    }
}
