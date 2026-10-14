<?php

declare(strict_types=1);

namespace EWnioski\Tests;

use EWnioski\Domain\Enum\LeaveStatus;
use EWnioski\Domain\Enum\LeaveType;
use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\Leave\LeaveRequestDraft;
use EWnioski\Domain\User\User;
use EWnioski\Service\LeaveRequestService;
use EWnioski\Service\ValidationException;
use EWnioski\Tests\Support\ArrayHolidayProvider;
use EWnioski\Tests\Support\FakeBalance;
use EWnioski\Tests\Support\FakeLeaveStore;
use EWnioski\Tests\Support\FakeUserDirectory;
use PHPUnit\Framework\TestCase;

final class LeaveRequestServiceTest extends TestCase
{
    private function user(string $nr, Role $role = Role::Employee, bool $forOthers = false): User
    {
        return new User(1, $nr, 'Jan', 'Testowy', 'jan@example.local', $role, null, null, null, $forOthers, null);
    }

    private function draft(string $target, string $start, string $end, LeaveType $type = LeaveType::Annual): LeaveRequestDraft
    {
        return new LeaveRequestDraft(
            targetEmployeeNumber: $target,
            type: $type,
            start: new \DateTimeImmutable($start),
            end: new \DateTimeImmutable($end),
        );
    }

    public function test_self_annual_leave_is_submitted_and_balance_from_server(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            $store = new FakeLeaveStore(),
            new FakeBalance(['A1' => 10.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $result = $service->submit($actor, $this->draft('A1', '2025-06-02', '2025-06-06'));

        $this->assertSame(LeaveStatus::Submitted, $result->status);
        $this->assertSame(5.0, $result->workingDays);
        // Saldo NIE pochodzi z inputu – 'days' policzone serwerowo.
        $this->assertSame(5.0, $store->lastInsert['days']);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['A1' => 2.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $this->expectException(ValidationException::class);
        $service->submit($actor, $this->draft('A1', '2025-06-02', '2025-06-06')); // 5 dni > 2
    }

    public function test_agency_worker_without_balance_row_is_allowed(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance([]), // brak wiersza => null => limit nieegzekwowany
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $result = $service->submit($actor, $this->draft('A1', '2025-06-02', '2025-06-06'));
        $this->assertSame(LeaveStatus::Submitted, $result->status);
    }

    public function test_cannot_submit_for_others_without_permission(): void
    {
        $actor = $this->user('A1', Role::Employee, forOthers: false);
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['B2' => 20.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor, 'B2' => $this->user('B2')], managers: ['B2' => 'ZZ']),
        );

        $this->expectException(ValidationException::class);
        $service->submit($actor, $this->draft('B2', '2025-06-02', '2025-06-06'));
    }

    public function test_direct_manager_submission_is_auto_approved(): void
    {
        $manager = $this->user('M1', Role::Manager);
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['B2' => 20.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(
                ['M1' => $manager, 'B2' => $this->user('B2')],
                managers: ['B2' => 'M1'], // M1 jest bezpośrednim przełożonym B2
            ),
        );

        $result = $service->submit($manager, $this->draft('B2', '2025-06-02', '2025-06-06'));
        $this->assertSame(LeaveStatus::ApprovedByManager, $result->status);
    }

    public function test_brigade_leader_request_waits_for_manager(): void
    {
        $hr = $this->user('H1', Role::Hr);
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['B2' => 20.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(
                ['H1' => $hr, 'B2' => $this->user('B2')],
                managers: ['B2' => 'OTHER'],
                brigadeLeaders: ['B2'],
            ),
        );

        $result = $service->submit($hr, $this->draft('B2', '2025-06-02', '2025-06-06'));
        $this->assertSame(LeaveStatus::Submitted, $result->status);
    }

    public function test_overlapping_request_is_rejected(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            new FakeLeaveStore([['nr' => 'A1', 'start' => '2025-06-03', 'end' => '2025-06-04']]),
            new FakeBalance(['A1' => 20.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $this->expectException(ValidationException::class);
        // Nowy zakres obejmuje istniejący w całości – stara wersja by to przepuściła.
        $service->submit($actor, $this->draft('A1', '2025-06-02', '2025-06-06'));
    }

    public function test_cross_year_range_is_rejected(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['A1' => 50.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $this->expectException(ValidationException::class);
        $service->submit($actor, $this->draft('A1', '2025-12-29', '2026-01-05'));
    }

    public function test_reversed_dates_are_rejected(): void
    {
        $actor = $this->user('A1');
        $service = new LeaveRequestService(
            new FakeLeaveStore(),
            new FakeBalance(['A1' => 50.0]),
            new ArrayHolidayProvider(),
            new FakeUserDirectory(['A1' => $actor]),
        );

        $this->expectException(ValidationException::class);
        $service->submit($actor, $this->draft('A1', '2025-06-10', '2025-06-02'));
    }
}
