<?php

declare(strict_types=1);

namespace EWnioski\Repository;

use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\User\User;
use EWnioski\Database\Database;
use EWnioski\Repository\Contract\UserDirectory;
use PDO;

/**
 * Dostęp do danych użytkowników. Wyłącznie zapytania przygotowane.
 *
 * Odpowiednik starego zapytania z log.php (JOIN osoby/mpk/users),
 * ale sparametryzowany – koniec z SQL injection na loginie.
 */
final class UserRepository implements UserDirectory
{
    public function __construct(private readonly Database $db)
    {
    }

    private function pdo(): PDO
    {
        return $this->db->connection();
    }

    public function findByEmployeeNumber(string $employeeNumber): ?User
    {
        $sql = <<<SQL
            SELECT o.id, o.numer_ewidencyjny, o.imie, o.nazwisko, o.email,
                   o.haslo_urlopy, o.wnioski_dla_innych, o.admin,
                   m.mail       AS manager_email,
                   m.kierownik  AS manager_name,
                   os.numer_ewidencyjny AS manager_number,
                   us.permisions AS permission
            FROM ewidencja_szkolen.osoby o
            LEFT JOIN ewidencja_szkolen.mpk m ON o.id_mpk = m.id
            LEFT JOIN ewidencja_szkolen.osoby os ON m.mail = os.email
            LEFT JOIN oddelegowania.users us ON o.numer_ewidencyjny = us.numer_ewidencyjny
            WHERE o.numer_ewidencyjny = :nr
            LIMIT 1
            SQL;

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['nr' => $employeeNumber]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Zapisuje przehaszowane hasło (migracja z jawnego lub rehash).
     */
    public function updatePasswordHash(int $userId, string $hash): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE ewidencja_szkolen.osoby SET haslo_urlopy = :hash WHERE id = :id'
        );
        $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }

    /**
     * Numer ewidencyjny bezpośredniego przełożonego pracownika
     * (z najnowszego wiersza stan_godzin). Używane do weryfikacji,
     * kto może zatwierdzać wniosek.
     */
    public function directManagerNumber(string $employeeNumber): ?string
    {
        $stmt = $this->pdo()->prepare(
            'SELECT numer_kierownik FROM stan_godzin
             WHERE numer_ewidencyjny = :nr ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['nr' => $employeeNumber]);
        $value = $stmt->fetchColumn();

        return $value !== false && $value !== null ? (string) $value : null;
    }

    public function isBrigadeLeader(string $employeeNumber): bool
    {
        $stmt = $this->pdo()->prepare(
            'SELECT brygadzista FROM stan_godzin
             WHERE numer_ewidencyjny = :nr ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['nr' => $employeeNumber]);
        $value = $stmt->fetchColumn();

        return (bool) $value;
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            employeeNumber: (string) $row['numer_ewidencyjny'],
            firstName: (string) ($row['imie'] ?? ''),
            lastName: (string) ($row['nazwisko'] ?? ''),
            email: $row['email'] !== null ? (string) $row['email'] : null,
            role: Role::fromLegacy(
                $row['permission'] !== null ? (string) $row['permission'] : null,
                (string) ($row['admin'] ?? '') === '1',
            ),
            managerEmployeeNumber: $row['manager_number'] !== null ? (string) $row['manager_number'] : null,
            managerEmail: $row['manager_email'] !== null ? (string) $row['manager_email'] : null,
            managerName: $row['manager_name'] !== null ? (string) $row['manager_name'] : null,
            canSubmitForOthers: (string) ($row['wnioski_dla_innych'] ?? '') === '1',
            passwordHash: $row['haslo_urlopy'] !== null ? (string) $row['haslo_urlopy'] : null,
        );
    }
}
