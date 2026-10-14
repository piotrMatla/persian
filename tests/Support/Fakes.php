<?php

declare(strict_types=1);

namespace EWnioski\Tests\Support;

use EWnioski\Domain\User\User;
use EWnioski\Repository\Contract\LeaveBalanceProvider;
use EWnioski\Repository\Contract\LeaveRequestStore;
use EWnioski\Repository\Contract\UserDirectory;

/**
 * Atrapy repozytoriów trzymające dane w pamięci – pozwalają testować
 * reguły biznesowe LeaveRequestService bez bazy danych.
 */
final class FakeUserDirectory implements UserDirectory
{
    /** @param array<string,User> $users @param array<string,?string> $managers @param list<string> $brigadeLeaders */
    public function __construct(
        private array $users = [],
        private array $managers = [],
        private array $brigadeLeaders = [],
    ) {
    }

    public function findByEmployeeNumber(string $employeeNumber): ?User
    {
        return $this->users[$employeeNumber] ?? null;
    }

    public function directManagerNumber(string $employeeNumber): ?string
    {
        return $this->managers[$employeeNumber] ?? null;
    }

    public function isBrigadeLeader(string $employeeNumber): bool
    {
        return in_array($employeeNumber, $this->brigadeLeaders, true);
    }
}

final class FakeLeaveStore implements LeaveRequestStore
{
    public ?array $lastInsert = null;
    public int $nextId = 100;

    /** @param list<array{nr:string,start:string,end:string}> $existing */
    public function __construct(private array $existing = [])
    {
    }

    public function hasOverlapping(string $employeeNumber, \DateTimeImmutable $start, \DateTimeImmutable $end): bool
    {
        foreach ($this->existing as $row) {
            if ($row['nr'] !== $employeeNumber) {
                continue;
            }
            if ($row['start'] <= $end->format('Y-m-d') && $row['end'] >= $start->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    public function insert(array $data): int
    {
        $this->lastInsert = $data;

        return $this->nextId++;
    }
}

final class FakeBalance implements LeaveBalanceProvider
{
    /** @param array<string,?float> $balances */
    public function __construct(private array $balances = [])
    {
    }

    public function availableAnnualDays(string $employeeNumber): ?float
    {
        return $this->balances[$employeeNumber] ?? null;
    }
}
