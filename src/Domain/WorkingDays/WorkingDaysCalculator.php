<?php

declare(strict_types=1);

namespace EWnioski\Domain\WorkingDays;

/**
 * Jedno, testowalne miejsce liczenia dni roboczych.
 *
 * Zastępuje cztery różne, powielone pętle w Modyfikuj::WpiszUrlop
 * (z zaszytą datą '2022-04-23', dwoma zapytaniami do bazy na każdy dzień
 * i błędem porównywania $tymdata zamiast $tymdataliczba).
 *
 * Reguły: dzień roboczy = pon–pt, który nie jest dniem świątecznym.
 * Dni świąteczne dostarcza HolidayProvider (z bazy lub z atrapy w testach).
 */
final class WorkingDaysCalculator
{
    private const MAX_RANGE_DAYS = 400; // zabezpieczenie przed zakresem bez końca

    public function __construct(private readonly HolidayProvider $holidays)
    {
    }

    public function countWorkingDays(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        if ($to < $from) {
            return 0;
        }

        $from = $from->setTime(0, 0);
        $to = $to->setTime(0, 0);

        $holidays = $this->holidays->holidaysBetween($from, $to);

        $count = 0;
        $cursor = $from;
        $guard = 0;

        while ($cursor <= $to) {
            if (++$guard > self::MAX_RANGE_DAYS) {
                break;
            }
            if ($this->isWorkingDay($cursor, $holidays)) {
                $count++;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $count;
    }

    /**
     * @param array<string,true> $holidays
     */
    private function isWorkingDay(\DateTimeImmutable $day, array $holidays): bool
    {
        $weekday = (int) $day->format('N'); // 1 (pon) .. 7 (nd)
        if ($weekday >= 6) {
            return false;
        }

        return !isset($holidays[$day->format('Y-m-d')]);
    }
}
