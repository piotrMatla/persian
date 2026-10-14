<?php

declare(strict_types=1);

namespace EWnioski\Domain\Navigation;

use EWnioski\Domain\Enum\Role;

/**
 * Pojedynczy kafelek/moduł na pulpicie.
 *
 * Widoczność wynika z ról (allowedRoles) i jest egzekwowana po stronie
 * serwera przez ModuleCatalog – a nie przez ukrywanie elementów w HTML/JS
 * jak w starej wersji (warunek na MPK w szablonie menu).
 */
final class NavItem
{
    /**
     * @param 'prod'|'admin'                   $category
     * @param list<string>                     $badges
     * @param array<string,string>             $info     etykieta => wartość (hover-info)
     * @param list<array{label:string,href:string}> $links grupa linków (otwiera modal)
     * @param list<Role>                       $allowedRoles puste = każdy zalogowany
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $category,
        public readonly string $icon,
        public readonly string $purpose,
        public readonly ?string $href = null,
        public readonly array $badges = [],
        public readonly array $info = [],
        public readonly array $links = [],
        public readonly array $allowedRoles = [],
        public readonly bool $available = true,
    ) {
    }

    public function isGroup(): bool
    {
        return $this->links !== [];
    }

    public function isVisibleFor(Role $role): bool
    {
        return $this->allowedRoles === [] || in_array($role, $this->allowedRoles, true);
    }
}
