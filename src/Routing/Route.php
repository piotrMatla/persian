<?php

declare(strict_types=1);

namespace EWnioski\Routing;

use EWnioski\Domain\Enum\Role;

/**
 * Pojedyncza trasa + wymagane role. Autoryzacja jest zdefiniowana
 * przy trasie (po stronie serwera), a nie przez ukrywanie przycisków w HTML.
 */
final class Route
{
    /**
     * @param list<Role> $roles  Puste = tylko zalogowany; null = publiczna.
     * @param callable   $handler
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly mixed $handler,
        public readonly ?array $roles = [],
    ) {
    }
}
