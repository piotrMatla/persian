<?php

declare(strict_types=1);

namespace EWnioski\Domain\Enum;

/**
 * Role w systemie. Zastępuje porozrzucane po kodzie porównania napisów
 * (`permision == 'hr'`, `ewnioski_mpk == 'Ind.667099'`, listy nazwisk)
 * oraz pole `admin='1'`.
 */
enum Role: string
{
    case Employee = 'pracownik';
    case Manager = 'kierownik';
    case Hr = 'hr';
    case Controlling = 'kontroling';
    case WorkMethods = 'work_methods';
    case ProdManager = 'prod_manager';
    case PowerUser = 'power_user';
    case Admin = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Pracownik',
            self::Manager => 'Kierownik',
            self::Hr => 'Dział personalny (HR)',
            self::Controlling => 'Kontroling',
            self::WorkMethods => 'Work Methods',
            self::ProdManager => 'Kierownik produkcji',
            self::PowerUser => 'Power user',
            self::Admin => 'Administrator',
        };
    }

    /**
     * Czy rola może pełnić funkcję akceptacji HR.
     */
    public function canActAsHr(): bool
    {
        return in_array($this, [self::Hr, self::PowerUser, self::Admin], true);
    }

    public static function fromLegacy(?string $permission, bool $isAdminFlag): self
    {
        if ($isAdminFlag) {
            return self::Admin;
        }

        return match ($permission) {
            'hr' => self::Hr,
            'kierownik' => self::Manager,
            'kontroling' => self::Controlling,
            'work_methods' => self::WorkMethods,
            'prod_manager' => self::ProdManager,
            'power_user' => self::PowerUser,
            'master_admin', 'administrator' => self::Admin,
            default => self::Employee,
        };
    }
}
