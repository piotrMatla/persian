<?php

declare(strict_types=1);

namespace EWnioski\Domain\Leave;

use EWnioski\Domain\Enum\LeaveType;

/**
 * Dane nowego wniosku zebrane z formularza, już otypowane.
 * Saldo urlopu NIE jest tu przyjmowane z formularza (stara luka) –
 * liczy je serwis na podstawie bazy.
 */
final class LeaveRequestDraft
{
    public function __construct(
        public readonly string $targetEmployeeNumber,
        public readonly LeaveType $type,
        public readonly \DateTimeImmutable $start,
        public readonly \DateTimeImmutable $end,
        public readonly ?string $childName = null,
        public readonly ?\DateTimeImmutable $childBirthDate = null,
        public readonly ?string $relationship = null,
        public readonly ?string $address = null,
        public readonly ?float $hours = null,
    ) {
    }
}
