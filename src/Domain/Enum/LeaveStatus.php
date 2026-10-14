<?php

declare(strict_types=1);

namespace EWnioski\Domain\Enum;

/**
 * Status wniosku urlopowego. Zastępuje magiczne liczby (1,2,3,6,8)
 * rozsiane po Raporty.php / Modyfikuj.php. Wartości zachowują zgodność
 * z kolumną `wniosek.id_status` w istniejącej bazie.
 */
enum LeaveStatus: int
{
    case Submitted = 1;              // wpisany, czeka na przełożonego
    case ApprovedByManager = 2;      // zaakceptowany przez przełożonego
    case ApprovedByHr = 3;           // zaakceptowany przez HR (finalny)
    case Cancelled = 6;              // anulowany
    case SpecialPendingHr = 8;       // typ specjalny – czeka bezpośrednio na HR

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Wpisany',
            self::ApprovedByManager => 'Zaakceptowany przez przełożonego',
            self::ApprovedByHr => 'Zaakceptowany przez HR',
            self::Cancelled => 'Anulowany',
            self::SpecialPendingHr => 'Oczekuje na HR',
        };
    }

    /**
     * Czy z tego statusu przełożony może zaakceptować wniosek.
     */
    public function isManagerActionable(): bool
    {
        return $this === self::Submitted;
    }

    /**
     * Czy z tego statusu HR może zatwierdzić wniosek.
     */
    public function isHrActionable(): bool
    {
        return $this === self::ApprovedByManager || $this === self::SpecialPendingHr;
    }

    public function isFinal(): bool
    {
        return $this === self::ApprovedByHr || $this === self::Cancelled;
    }
}
