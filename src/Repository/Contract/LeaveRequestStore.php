<?php

declare(strict_types=1);

namespace EWnioski\Repository\Contract;

interface LeaveRequestStore
{
    public function hasOverlapping(
        string $employeeNumber,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): bool;

    /**
     * @param array<string,mixed> $data
     * @return int ID nowego wniosku
     */
    public function insert(array $data): int;
}
