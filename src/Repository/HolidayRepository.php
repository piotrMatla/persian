<?php

declare(strict_types=1);

namespace EWnioski\Repository;

use EWnioski\Database\Database;
use EWnioski\Domain\WorkingDays\HolidayProvider;
use PDO;

/**
 * Dni świąteczne z tabeli `dni_wolne`.
 *
 * Stara wersja odpytywała bazę osobno dla KAŻDEGO dnia urlopu (2 zapytania
 * na dzień). Tutaj jedno zapytanie na cały zakres wniosku.
 *
 * Obsługa kolumny `stale` (święto „ruchome"/jednorazowe vs. „stałe"
 * powtarzalne co rok) zachowuje semantykę oryginału:
 *  - stale = 1  -> święto w danym dniu-miesiącu każdego roku,
 *  - stale = 0  -> święto tylko w konkretnej dacie (dzień-miesiąc-rok).
 */
final class HolidayRepository implements HolidayProvider
{
    public function __construct(private readonly Database $db)
    {
    }

    private function pdo(): PDO
    {
        return $this->db->connection();
    }

    public function holidaysBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT DATE_FORMAT(dzien, '%Y-%m-%d') AS exact_day,
                    DATE_FORMAT(dzien, '%m-%d')    AS month_day,
                    stale
             FROM dni_wolne"
        );
        $stmt->execute();

        $recurring = [];   // 'm-d' => true
        $oneOff = [];      // 'Y-m-d' => true
        foreach ($stmt as $row) {
            if ((int) $row['stale'] === 1) {
                $recurring[(string) $row['month_day']] = true;
            } else {
                $oneOff[(string) $row['exact_day']] = true;
            }
        }

        $result = [];
        $cursor = $from->setTime(0, 0);
        $end = $to->setTime(0, 0);
        $guard = 0;
        while ($cursor <= $end) {
            if (++$guard > 400) {
                break;
            }
            $ymd = $cursor->format('Y-m-d');
            if (isset($oneOff[$ymd]) || isset($recurring[$cursor->format('m-d')])) {
                $result[$ymd] = true;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $result;
    }
}
