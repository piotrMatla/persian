<?php

declare(strict_types=1);

namespace EWnioski\Tests;

use EWnioski\Domain\WorkingDays\WorkingDaysCalculator;
use EWnioski\Tests\Support\ArrayHolidayProvider;
use PHPUnit\Framework\TestCase;

final class WorkingDaysCalculatorTest extends TestCase
{
    public function test_counts_single_working_day(): void
    {
        $calc = new WorkingDaysCalculator(new ArrayHolidayProvider());
        // Poniedziałek
        $this->assertSame(1, $calc->countWorkingDays(
            new \DateTimeImmutable('2025-06-02'),
            new \DateTimeImmutable('2025-06-02'),
        ));
    }

    public function test_excludes_weekend(): void
    {
        $calc = new WorkingDaysCalculator(new ArrayHolidayProvider());
        // pon 2 cze – nd 8 cze 2025 => 5 dni roboczych
        $this->assertSame(5, $calc->countWorkingDays(
            new \DateTimeImmutable('2025-06-02'),
            new \DateTimeImmutable('2025-06-08'),
        ));
    }

    public function test_excludes_holiday(): void
    {
        // 2025-06-19 (Boże Ciało, czwartek) jako dzień wolny
        $calc = new WorkingDaysCalculator(new ArrayHolidayProvider(['2025-06-19']));
        // pon 16 – pt 20 cze => 5 dni, minus czwartek-święto => 4
        $this->assertSame(4, $calc->countWorkingDays(
            new \DateTimeImmutable('2025-06-16'),
            new \DateTimeImmutable('2025-06-20'),
        ));
    }

    public function test_reversed_range_returns_zero(): void
    {
        $calc = new WorkingDaysCalculator(new ArrayHolidayProvider());
        $this->assertSame(0, $calc->countWorkingDays(
            new \DateTimeImmutable('2025-06-10'),
            new \DateTimeImmutable('2025-06-02'),
        ));
    }

    public function test_full_working_week_with_holiday_on_weekend_is_unaffected(): void
    {
        // święto w sobotę nie zmienia liczby dni roboczych
        $calc = new WorkingDaysCalculator(new ArrayHolidayProvider(['2025-06-07']));
        $this->assertSame(5, $calc->countWorkingDays(
            new \DateTimeImmutable('2025-06-02'),
            new \DateTimeImmutable('2025-06-08'),
        ));
    }
}
