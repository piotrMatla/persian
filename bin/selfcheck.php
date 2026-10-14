<?php

declare(strict_types=1);

/**
 * Lekki smoke-test domeny BEZ zależności (PHPUnit/Composer).
 * Uruchamia te same scenariusze co testy jednostkowe i kończy się
 * kodem != 0 przy pierwszym niepowodzeniu. Przydatne na wdrożeniach
 * offline oraz jako szybki check w CI.
 *
 * Uruchomienie:  php bin/selfcheck.php
 */

use EWnioski\Domain\Enum\LeaveStatus;
use EWnioski\Domain\Enum\LeaveType;
use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\Leave\LeaveRequestDraft;
use EWnioski\Domain\User\User;
use EWnioski\Domain\WorkingDays\WorkingDaysCalculator;
use EWnioski\Security\PasswordHasher;
use EWnioski\Service\LeaveRequestService;
use EWnioski\Service\ValidationException;
use EWnioski\Support\Autoloader;
use EWnioski\Tests\Support\ArrayHolidayProvider;
use EWnioski\Tests\Support\FakeBalance;
use EWnioski\Tests\Support\FakeLeaveStore;
use EWnioski\Tests\Support\FakeUserDirectory;

require __DIR__ . '/../src/Support/Autoloader.php';
Autoloader::register(__DIR__ . '/../src');
require __DIR__ . '/../tests/Support/ArrayHolidayProvider.php';
require __DIR__ . '/../tests/Support/Fakes.php';

$passed = 0;
$failed = 0;

function check(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "  ✓ {$name}\n";
    } catch (\Throwable $e) {
        $failed++;
        echo "  ✗ {$name} -> " . $e->getMessage() . "\n";
    }
}

function assertSame(mixed $expected, mixed $actual, string $msg = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($msg !== '' ? $msg : sprintf('oczekiwano %s, otrzymano %s', var_export($expected, true), var_export($actual, true)));
    }
}

function assertThrows(string $class, callable $fn): void
{
    try {
        $fn();
    } catch (\Throwable $e) {
        if ($e instanceof $class) {
            return;
        }
        throw new \RuntimeException('zły wyjątek: ' . $e::class);
    }
    throw new \RuntimeException("oczekiwano wyjątku {$class}, nie rzucono");
}

$user = static fn (string $nr, Role $role = Role::Employee, bool $forOthers = false): User
    => new User(1, $nr, 'Jan', 'Testowy', 'jan@example.local', $role, null, null, null, $forOthers, null);

$draft = static fn (string $target, string $start, string $end, LeaveType $type = LeaveType::Annual): LeaveRequestDraft
    => new LeaveRequestDraft($target, $type, new \DateTimeImmutable($start), new \DateTimeImmutable($end));

echo "WorkingDaysCalculator:\n";
check('5 dni roboczych w tygodniu', function () {
    $c = new WorkingDaysCalculator(new ArrayHolidayProvider());
    assertSame(5, $c->countWorkingDays(new \DateTimeImmutable('2025-06-02'), new \DateTimeImmutable('2025-06-08')));
});
check('święto w czwartek odejmuje dzień', function () {
    $c = new WorkingDaysCalculator(new ArrayHolidayProvider(['2025-06-19']));
    assertSame(4, $c->countWorkingDays(new \DateTimeImmutable('2025-06-16'), new \DateTimeImmutable('2025-06-20')));
});
check('odwrócony zakres = 0', function () {
    $c = new WorkingDaysCalculator(new ArrayHolidayProvider());
    assertSame(0, $c->countWorkingDays(new \DateTimeImmutable('2025-06-10'), new \DateTimeImmutable('2025-06-02')));
});

echo "PasswordHasher (migracja haseł jawnych):\n";
check('stare hasło jawne weryfikuje się i wymaga rehash', function () {
    $h = new PasswordHasher();
    assertSame(true, $h->verify('tajne123', 'tajne123'));   // legacy plaintext
    assertSame(true, $h->needsRehash('tajne123'));
});
check('hash bcrypt weryfikuje się i nie wymaga rehash', function () {
    $h = new PasswordHasher();
    $hash = $h->hash('tajne123');
    assertSame(true, $h->verify('tajne123', $hash));
    assertSame(false, $h->verify('złe', $hash));
    assertSame(false, $h->needsRehash($hash));
});

echo "LeaveRequestService (reguły HR):\n";
check('własny urlop: status Wpisany, dni liczone serwerowo', function () use ($user, $draft) {
    $a = $user('A1');
    $s = new LeaveRequestService(new FakeLeaveStore(), new FakeBalance(['A1' => 10.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['A1' => $a]));
    $r = $s->submit($a, $draft('A1', '2025-06-02', '2025-06-06'));
    assertSame(LeaveStatus::Submitted, $r->status);
    assertSame(5.0, $r->workingDays);
});
check('za mało dni urlopu -> odrzucone', function () use ($user, $draft) {
    $a = $user('A1');
    $s = new LeaveRequestService(new FakeLeaveStore(), new FakeBalance(['A1' => 2.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['A1' => $a]));
    assertThrows(ValidationException::class, fn () => $s->submit($a, $draft('A1', '2025-06-02', '2025-06-06')));
});
check('wpisywanie za innych bez uprawnień -> odrzucone', function () use ($user, $draft) {
    $a = $user('A1', Role::Employee, false);
    $s = new LeaveRequestService(new FakeLeaveStore(), new FakeBalance(['B2' => 20.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['A1' => $a, 'B2' => $user('B2')], ['B2' => 'ZZ']));
    assertThrows(ValidationException::class, fn () => $s->submit($a, $draft('B2', '2025-06-02', '2025-06-06')));
});
check('bezpośredni przełożony -> auto-akceptacja', function () use ($user, $draft) {
    $m = $user('M1', Role::Manager);
    $s = new LeaveRequestService(new FakeLeaveStore(), new FakeBalance(['B2' => 20.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['M1' => $m, 'B2' => $user('B2')], ['B2' => 'M1']));
    $r = $s->submit($m, $draft('B2', '2025-06-02', '2025-06-06'));
    assertSame(LeaveStatus::ApprovedByManager, $r->status);
});
check('nakładające się wnioski (pełne zawarcie) -> odrzucone', function () use ($user, $draft) {
    $a = $user('A1');
    $s = new LeaveRequestService(new FakeLeaveStore([['nr' => 'A1', 'start' => '2025-06-03', 'end' => '2025-06-04']]), new FakeBalance(['A1' => 20.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['A1' => $a]));
    assertThrows(ValidationException::class, fn () => $s->submit($a, $draft('A1', '2025-06-02', '2025-06-06')));
});
check('zakres na przełomie lat -> odrzucone', function () use ($user, $draft) {
    $a = $user('A1');
    $s = new LeaveRequestService(new FakeLeaveStore(), new FakeBalance(['A1' => 50.0]), new ArrayHolidayProvider(), new FakeUserDirectory(['A1' => $a]));
    assertThrows(ValidationException::class, fn () => $s->submit($a, $draft('A1', '2025-12-29', '2026-01-05')));
});

echo "\nWynik: {$passed} OK, {$failed} błędów\n";
exit($failed === 0 ? 0 : 1);
