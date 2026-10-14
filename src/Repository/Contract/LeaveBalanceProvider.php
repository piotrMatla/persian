<?php

declare(strict_types=1);

namespace EWnioski\Repository\Contract;

interface LeaveBalanceProvider
{
    /**
     * Dostępne dni urlopu wypoczynkowego; null = limit nieegzekwowany
     * (np. pracownik agencyjny / brak wiersza stanu).
     */
    public function availableAnnualDays(string $employeeNumber): ?float;
}
