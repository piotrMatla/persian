<?php

declare(strict_types=1);

namespace EWnioski\Tests\Support;

use EWnioski\Domain\WorkingDays\HolidayProvider;

/**
 * Atrapa dostawcy dni świątecznych do testów – bez bazy danych.
 */
final class ArrayHolidayProvider implements HolidayProvider
{
    /** @param list<string> $holidays daty 'Y-m-d' */
    public function __construct(private readonly array $holidays = [])
    {
    }

    public function holidaysBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $map = [];
        foreach ($this->holidays as $day) {
            $map[$day] = true;
        }

        return $map;
    }
}
