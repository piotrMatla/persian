<?php

declare(strict_types=1);

namespace EWnioski\Domain\Enum;

/**
 * Rodzaj wniosku. Wartości zgodne z kolumną `wniosek.id_kategorii`
 * (stare `rodzaj_urlopu`: 1 = wypoczynkowy, 2 = na dziecko / opieka,
 * 3 = zwolnienie z obowiązku świadczenia pracy).
 */
enum LeaveType: int
{
    case Annual = 1;                 // urlop wypoczynkowy (liczony z puli dni)
    case ChildCare = 2;             // opieka / urlop na dziecko
    case ExemptFromWork = 3;        // zwolnienie z obowiązku świadczenia pracy

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Urlop wypoczynkowy',
            self::ChildCare => 'Opieka / urlop na dziecko',
            self::ExemptFromWork => 'Zwolnienie z obowiązku świadczenia pracy',
        };
    }

    /**
     * Czy ten typ pomniejsza pulę urlopu wypoczynkowego (wymaga sprawdzenia salda).
     */
    public function consumesAnnualBalance(): bool
    {
        return $this === self::Annual;
    }
}
