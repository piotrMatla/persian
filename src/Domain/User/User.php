<?php

declare(strict_types=1);

namespace EWnioski\Domain\User;

use EWnioski\Domain\Enum\Role;

/**
 * Zalogowany użytkownik (niemutowalny obiekt domenowy).
 * Mapuje kolumny z `ewidencja_szkolen.osoby` + rolę z `oddelegowania.users`.
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $employeeNumber,   // numer_ewidencyjny
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email,
        public readonly Role $role,
        public readonly ?string $managerEmployeeNumber,
        public readonly ?string $managerEmail,
        public readonly ?string $managerName,
        public readonly bool $canSubmitForOthers,  // wnioski_dla_innych
        public readonly ?string $passwordHash,
    ) {
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function is(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
