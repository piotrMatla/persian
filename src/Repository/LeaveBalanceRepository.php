<?php

declare(strict_types=1);

namespace EWnioski\Repository;

use EWnioski\Database\Database;
use EWnioski\Repository\Contract\LeaveBalanceProvider;
use PDO;

/**
 * Saldo urlopu/nadgodzin z tabeli `stan_godzin`.
 *
 * KLUCZOWA zmiana wobec starej wersji: dostępne dni urlopu czytamy z bazy
 * po stronie serwera, a nie z pola `$_POST['stan_urlopu']`, które pracownik
 * mógł dowolnie podmienić w przeglądarce.
 */
final class LeaveBalanceRepository implements LeaveBalanceProvider
{
    public function __construct(private readonly Database $db)
    {
    }

    private function pdo(): PDO
    {
        return $this->db->connection();
    }

    /**
     * Dostępne dni urlopu wypoczynkowego. null = brak wiersza w stan_godzin
     * (np. pracownik agencyjny – wtedy limit nie jest egzekwowany).
     */
    public function availableAnnualDays(string $employeeNumber): ?float
    {
        $stmt = $this->pdo()->prepare(
            'SELECT stan_urlopu FROM stan_godzin
             WHERE numer_ewidencyjny = :nr ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['nr' => $employeeNumber]);
        $value = $stmt->fetchColumn();

        if ($value === false || $value === null) {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}
