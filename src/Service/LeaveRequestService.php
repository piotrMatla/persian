<?php

declare(strict_types=1);

namespace EWnioski\Service;

use EWnioski\Domain\Enum\LeaveStatus;
use EWnioski\Domain\Enum\LeaveType;
use EWnioski\Domain\Leave\LeaveRequestDraft;
use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\User\User;
use EWnioski\Domain\WorkingDays\HolidayProvider;
use EWnioski\Domain\WorkingDays\WorkingDaysCalculator;
use EWnioski\Repository\Contract\LeaveBalanceProvider;
use EWnioski\Repository\Contract\LeaveRequestStore;
use EWnioski\Repository\Contract\UserDirectory;

/**
 * Reguły biznesowe wniosków urlopowych – w jednym miejscu, bez HTML i SQL.
 *
 * Naprawia błędy logiki ze starej wersji:
 *  1. Saldo urlopu liczone z bazy, nie z $_POST['stan_urlopu'].
 *  2. Limit sprawdzany zawsze (brak „dziury" grudzień->styczeń).
 *  3. Wpisanie wniosku za kogoś innego wymaga uprawnienia/roli (autoryzacja).
 *  4. Wykrywanie nakładających się wniosków obejmuje też pełne zawarcie.
 *  5. Jeden algorytm liczenia dni roboczych (WorkingDaysCalculator).
 *  6. Walidacja: koniec >= początek, ten sam rok kalendarzowy.
 */
final class LeaveRequestService
{
    private readonly WorkingDaysCalculator $workingDays;

    public function __construct(
        private readonly LeaveRequestStore $requests,
        private readonly LeaveBalanceProvider $balances,
        HolidayProvider $holidays,
        private readonly UserDirectory $users,
    ) {
        $this->workingDays = new WorkingDaysCalculator($holidays);
    }

    public function submit(User $actor, LeaveRequestDraft $draft): LeaveSubmissionResult
    {
        $this->assertDatesValid($draft);

        $isForSelf = $draft->targetEmployeeNumber === $actor->employeeNumber;
        if (!$isForSelf) {
            $this->assertCanSubmitForOthers($actor, $draft->targetEmployeeNumber);
        }

        if ($this->requests->hasOverlapping($draft->targetEmployeeNumber, $draft->start, $draft->end)) {
            throw new ValidationException(
                'Dla tej osoby w podanym okresie istnieje już aktywny wniosek. '
                . 'Nie można wpisać dwóch nakładających się wniosków.'
            );
        }

        $workingDays = $this->resolveWorkingDays($draft);

        if ($draft->type->consumesAnnualBalance()) {
            $this->assertBalanceSufficient($draft->targetEmployeeNumber, $workingDays);
        }

        $status = $this->resolveStatus($actor, $draft, $isForSelf);
        $managerKey = $this->users->directManagerNumber($draft->targetEmployeeNumber) ?? '';

        $requestId = $this->requests->insert([
            'type' => $draft->type->value,
            'submitted_by_name' => $actor->fullName(),
            'employee_name' => $this->targetName($draft, $actor, $isForSelf),
            'start' => $draft->start->format('Y-m-d'),
            'end' => $draft->end->format('Y-m-d'),
            'days' => $workingDays,
            'child_name' => $draft->childName,
            'child_birth' => $draft->childBirthDate?->format('Y-m-d'),
            'employee_number' => $draft->targetEmployeeNumber,
            'manager_key' => $managerKey,
            'status' => $status->value,
            'submitted_by_number' => $actor->employeeNumber,
            'reason' => null,
            'relationship' => $draft->relationship,
            'address' => $draft->address,
            'hours' => $draft->hours !== null ? abs($draft->hours) : null,
            'manager_date' => $status === LeaveStatus::ApprovedByManager ? date('Y-m-d H:i:s') : null,
            'manager_login' => $status === LeaveStatus::ApprovedByManager ? $actor->fullName() : null,
        ]);

        return new LeaveSubmissionResult($requestId, $status, $workingDays);
    }

    private function resolveWorkingDays(LeaveRequestDraft $draft): float
    {
        // Wniosek godzinowy: dni = godziny / 8 (jak w oryginale, ale w jednym miejscu).
        if ($draft->hours !== null && $draft->hours != 0.0) {
            return round(abs($draft->hours) / 8, 4);
        }

        return (float) $this->workingDays->countWorkingDays($draft->start, $draft->end);
    }

    private function assertDatesValid(LeaveRequestDraft $draft): void
    {
        if ($draft->end < $draft->start) {
            throw new ValidationException('Data końcowa nie może być wcześniejsza niż początkowa.');
        }
        if ($draft->start->format('Y') !== $draft->end->format('Y')) {
            throw new ValidationException(
                'Wniosek nie może obejmować dwóch różnych lat kalendarzowych. '
                . 'Proszę wpisać osobny wniosek na każdy rok.'
            );
        }
    }

    private function assertCanSubmitForOthers(User $actor, string $targetNumber): void
    {
        if ($actor->canSubmitForOthers) {
            return;
        }
        if ($actor->role->canActAsHr() || $actor->is(Role::Manager)) {
            return;
        }
        // Bezpośredni przełożony też może.
        if ($this->users->directManagerNumber($targetNumber) === $actor->employeeNumber) {
            return;
        }

        throw new ValidationException('Nie masz uprawnień do wpisywania wniosków za inne osoby.');
    }

    private function assertBalanceSufficient(string $targetNumber, float $requestedDays): void
    {
        $available = $this->balances->availableAnnualDays($targetNumber);
        // null = pracownik agencyjny / brak wiersza stanu – limit nieegzekwowany.
        if ($available === null) {
            return;
        }
        if ($available < $requestedDays) {
            throw new ValidationException(sprintf(
                'Niewystarczająca liczba dni urlopu. Dostępne: %s, wnioskowane: %s.',
                rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.'),
                rtrim(rtrim(number_format($requestedDays, 2, '.', ''), '0'), '.'),
            ));
        }
    }

    private function resolveStatus(User $actor, LeaveRequestDraft $draft, bool $isForSelf): LeaveStatus
    {
        if ($isForSelf) {
            return LeaveStatus::Submitted;
        }

        // Typ „opieka/na dziecko" idzie bezpośrednio do HR.
        if ($draft->type === LeaveType::ChildCare) {
            return LeaveStatus::SpecialPendingHr;
        }

        // Jeśli wpisujący jest bezpośrednim przełożonym – od razu zaakceptowany przez przełożonego.
        if ($this->users->directManagerNumber($draft->targetEmployeeNumber) === $actor->employeeNumber) {
            return LeaveStatus::ApprovedByManager;
        }

        // Brygadzista: wniosek musi czekać na właściwego kierownika (zakaz auto-akceptacji).
        if ($this->users->isBrigadeLeader($draft->targetEmployeeNumber)) {
            return LeaveStatus::Submitted;
        }

        return LeaveStatus::ApprovedByManager;
    }

    private function targetName(LeaveRequestDraft $draft, User $actor, bool $isForSelf): string
    {
        if ($isForSelf) {
            return $actor->fullName();
        }
        $target = $this->users->findByEmployeeNumber($draft->targetEmployeeNumber);

        return $target?->fullName() ?? $draft->targetEmployeeNumber;
    }
}
