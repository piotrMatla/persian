<?php

declare(strict_types=1);

namespace EWnioski\Domain\Leave;

use EWnioski\Domain\Enum\LeaveStatus;
use EWnioski\Domain\Enum\LeaveType;

/**
 * Wniosek odczytany z bazy (do raportów i akceptacji).
 */
final class LeaveRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $employeeNumber,
        public readonly string $employeeName,
        public readonly LeaveType $type,
        public readonly LeaveStatus $status,
        public readonly \DateTimeImmutable $start,
        public readonly \DateTimeImmutable $end,
        public readonly float $workingDays,
        public readonly string $managerKey,        // dane_mpk
        public readonly string $submittedByNumber, // numer_ewidencyjny_wpisujacy
    ) {
    }
}
