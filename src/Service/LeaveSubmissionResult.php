<?php

declare(strict_types=1);

namespace EWnioski\Service;

use EWnioski\Domain\Enum\LeaveStatus;

final class LeaveSubmissionResult
{
    public function __construct(
        public readonly int $requestId,
        public readonly LeaveStatus $status,
        public readonly float $workingDays,
    ) {
    }
}
