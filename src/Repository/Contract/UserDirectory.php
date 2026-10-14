<?php

declare(strict_types=1);

namespace EWnioski\Repository\Contract;

use EWnioski\Domain\User\User;

interface UserDirectory
{
    public function findByEmployeeNumber(string $employeeNumber): ?User;

    public function directManagerNumber(string $employeeNumber): ?string;

    public function isBrigadeLeader(string $employeeNumber): bool;
}
