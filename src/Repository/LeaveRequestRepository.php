<?php

declare(strict_types=1);

namespace EWnioski\Repository;

use EWnioski\Domain\Enum\LeaveStatus;
use EWnioski\Domain\Enum\LeaveType;
use EWnioski\Domain\Leave\LeaveRequest;
use EWnioski\Database\Database;
use EWnioski\Repository\Contract\LeaveRequestStore;
use PDO;

/**
 * Dostęp do tabeli `wniosek`. Wyłącznie zapytania przygotowane.
 */
final class LeaveRequestRepository implements LeaveRequestStore
{
    public function __construct(private readonly Database $db)
    {
    }

    private function pdo(): PDO
    {
        return $this->db->connection();
    }

    /**
     * Czy w podanym zakresie istnieje już aktywny (nieanulowany) wniosek
     * tej osoby. Wykrywa także nakładanie obejmujące istniejący wniosek
     * w całości (stara wersja łapała tylko przypadki brzegowe).
     */
    public function hasOverlapping(
        string $employeeNumber,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): bool {
        $sql = 'SELECT COUNT(*) FROM wniosek
                WHERE numer_ewidencyjny = :nr
                  AND id_status != :cancelled
                  AND poczatek_wolnego <= :end
                  AND koniec_wolnego  >= :start';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'nr' => $employeeNumber,
            'cancelled' => LeaveStatus::Cancelled->value,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function insert(array $data): int
    {
        $sql = 'INSERT INTO wniosek
                    (id_kategorii, data_wpisania, osoba_wpisujaca, imie_nazwisko,
                     poczatek_wolnego, koniec_wolnego, liczba_dni_eksport,
                     imie_dziecka, data_urodzenia_dziecka, numer_ewidencyjny,
                     dane_mpk, id_status, numer_ewidencyjny_wpisujacy,
                     przyczyna, stopien_pokrewienstwa, adres_zamieszkania,
                     liczba_godzin_enova, data_kierownik, login_kierownik)
                VALUES
                    (:type, NOW(), :submitted_by_name, :employee_name,
                     :start, :end, :days,
                     :child_name, :child_birth, :employee_number,
                     :manager_key, :status, :submitted_by_number,
                     :reason, :relationship, :address,
                     :hours, :manager_date, :manager_login)';

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($data);

        return (int) $this->pdo()->lastInsertId();
    }

    public function find(int $id): ?LeaveRequest
    {
        $sql = 'SELECT id, numer_ewidencyjny, imie_nazwisko, id_kategorii, id_status,
                       poczatek_wolnego, koniec_wolnego, liczba_dni_eksport,
                       dane_mpk, numer_ewidencyjny_wpisujacy
                FROM wniosek WHERE id = :id LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Akceptacja z kontrolą bieżącego statusu wprost w WHERE – nie da się
     * zaakceptować wniosku anulowanego ani przeskoczyć etapu.
     *
     * @param list<int> $allowedCurrent  dozwolone statusy wyjściowe
     */
    public function transitionStatus(
        int $id,
        LeaveStatus $to,
        array $allowedCurrent,
        string $actorField,
        string $actorName,
    ): bool {
        $placeholders = [];
        $params = ['id' => $id, 'to' => $to->value, 'actor' => $actorName];
        foreach ($allowedCurrent as $i => $status) {
            $key = "cur{$i}";
            $placeholders[] = ':' . $key;
            $params[$key] = $status;
        }
        $inClause = implode(', ', $placeholders);

        // $actorField jest z listy dozwolonych nazw kolumn (patrz serwis), nie z inputu.
        $sql = "UPDATE wniosek
                SET id_status = :to, {$actorField} = :actor, {$actorField}_data = NOW()
                WHERE id = :id AND id_status IN ({$inClause})";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): LeaveRequest
    {
        return new LeaveRequest(
            id: (int) $row['id'],
            employeeNumber: (string) $row['numer_ewidencyjny'],
            employeeName: (string) $row['imie_nazwisko'],
            type: LeaveType::from((int) $row['id_kategorii']),
            status: LeaveStatus::from((int) $row['id_status']),
            start: new \DateTimeImmutable((string) $row['poczatek_wolnego']),
            end: new \DateTimeImmutable((string) $row['koniec_wolnego']),
            workingDays: (float) $row['liczba_dni_eksport'],
            managerKey: (string) $row['dane_mpk'],
            submittedByNumber: (string) $row['numer_ewidencyjny_wpisujacy'],
        );
    }
}
