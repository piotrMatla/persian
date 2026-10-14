<?php

declare(strict_types=1);

namespace EWnioski\Domain\WorkingDays;

interface HolidayProvider
{
    /**
     * Zbiór dni świątecznych jako daty w formacie 'Y-m-d'.
     * Kalkulator pyta tylko o potrzebny zakres, dzięki czemu da się go
     * testować bez bazy (patrz testy jednostkowe).
     *
     * @return array<string,true> mapa 'Y-m-d' => true dla szybkiego wyszukiwania
     */
    public function holidaysBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
}
