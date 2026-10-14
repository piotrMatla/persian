<?php

declare(strict_types=1);

namespace EWnioski\Domain\Navigation;

use EWnioski\Domain\Enum\Role;
use EWnioski\Domain\User\User;

/**
 * Katalog modułów pulpitu. Jedno źródło prawdy o tym, co jest w menu
 * i kto to widzi. Role sprawdzane są tutaj (serwer), nie w widoku.
 *
 * Moduły oznaczone available=false to pozycje jeszcze nieprzeniesione
 * z warstwy 4B (patrz README – kolejne incrementy).
 */
final class ModuleCatalog
{
    /** Role mające dostęp do sekcji administracyjnej/HR. */
    private const ADMIN_ROLES = [Role::Hr, Role::PowerUser, Role::Admin];

    /**
     * @return list<NavItem>
     */
    public function all(): array
    {
        return [
            new NavItem(
                id: 'urlop', name: 'Wniosek o urlop', category: 'prod', icon: 'leave',
                purpose: 'Wpisanie wniosku urlopowego (wypoczynkowy, na dziecko, zwolnienie).',
                href: 'leave', badges: ['Wniosek'],
                info: ['Dostęp' => 'Każdy pracownik', 'Akcja' => 'Nowy wniosek urlopowy'],
                available: true,
            ),
            new NavItem(
                id: 'zwolnienie', name: 'Zwolnienie z godzin', category: 'prod', icon: 'clock',
                purpose: 'Zwolnienie z godzin lub zmiana czasu pracy.',
                href: 'leave/zwolnienie', badges: ['Wniosek'],
                info: ['Dostęp' => 'Każdy pracownik'],
                available: false,
            ),
            new NavItem(
                id: 'zdalna', name: 'Praca zdalna', category: 'prod', icon: 'home',
                purpose: 'Wniosek o pracę zdalną.',
                href: 'leave/zdalna', badges: ['Wniosek'],
                info: ['Dostęp' => 'Uprawnieni do pracy zdalnej'],
                available: false,
            ),
            new NavItem(
                id: 'nadgodziny', name: 'Rozliczenie nadgodzin', category: 'prod', icon: 'clock',
                purpose: 'Ewidencja i akceptacja godzin nadliczbowych.',
                href: 'overtime', badges: ['Nadgodziny'],
                info: ['Dostęp' => 'Pracownik / kierownik / HR'],
                available: false,
            ),
            new NavItem(
                id: 'raporty', name: 'Raporty', category: 'prod', icon: 'report',
                purpose: 'Raporty urlopów, zwolnień, stanu godzin, wejść i pracy zdalnej.',
                badges: ['Raporty', 'Excel'],
                info: ['Zawiera' => '7 raportów', 'Eksport' => 'XLSX'],
                links: [
                    ['label' => 'Raport urlopów', 'href' => 'reports/urlop'],
                    ['label' => 'Raport zwolnień / zmiany czasu', 'href' => 'reports/zwolnienia'],
                    ['label' => 'Stan urlopu i nadgodzin', 'href' => 'reports/stan'],
                    ['label' => 'Stan wejść i wyjść', 'href' => 'reports/wejscia'],
                    ['label' => 'Raport odbić', 'href' => 'reports/odbicia'],
                    ['label' => 'Raport pracy zdalnej', 'href' => 'reports/praca-zdalna'],
                    ['label' => 'Raport pracy zdalnej — czas pracy', 'href' => 'reports/praca-zdalna-czas'],
                ],
                available: false,
            ),
            new NavItem(
                id: 'dokumenty', name: 'Dokumenty i wnioski', category: 'prod', icon: 'folder',
                purpose: 'Formularze i dokumenty do pobrania.',
                href: 'documents', badges: ['Dokumenty'],
                info: ['Dostęp' => 'Każdy pracownik'],
                available: false,
            ),
            new NavItem(
                id: 'instrukcja', name: 'Instrukcja', category: 'prod', icon: 'book',
                purpose: 'Instrukcja obsługi programu eWnioski.',
                href: 'help', badges: ['Pomoc'],
                info: ['Dostęp' => 'Każdy pracownik'],
                available: false,
            ),

            // --- Administracja / HR (dawny warunek MPK Ind.667099 / Ind.667299) ---
            new NavItem(
                id: 'wszystkie', name: 'Wszystkie osoby', category: 'admin', icon: 'people',
                purpose: 'Lista wszystkich osób w systemie.',
                href: 'admin/people', badges: ['HR'],
                info: ['Dostęp' => 'Tylko HR'],
                allowedRoles: self::ADMIN_ROLES, available: false,
            ),
            new NavItem(
                id: 'wczytaj-urlop', name: 'Wczytaj liczbę urlopu', category: 'admin', icon: 'upload',
                purpose: 'Import stanu urlopu z pliku Excel.',
                href: 'admin/import-balance', badges: ['HR', 'Import'],
                info: ['Dostęp' => 'Tylko HR', 'Format' => 'XLSX'],
                allowedRoles: self::ADMIN_ROLES, available: false,
            ),
            new NavItem(
                id: 'zastepca', name: 'Sprawdź zastępcę', category: 'admin', icon: 'people',
                purpose: 'Weryfikacja przypisanych zastępców i uprawnień.',
                href: 'admin/substitutes', badges: ['HR'],
                info: ['Dostęp' => 'Tylko HR'],
                allowedRoles: self::ADMIN_ROLES, available: false,
            ),
        ];
    }

    /**
     * Moduły widoczne dla danego użytkownika (filtr ról po stronie serwera).
     *
     * @return list<NavItem>
     */
    public function visibleFor(User $user): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (NavItem $item): bool => $item->isVisibleFor($user->role),
        ));
    }

    public function hasAdminModules(User $user): bool
    {
        foreach ($this->visibleFor($user) as $item) {
            if ($item->category === 'admin') {
                return true;
            }
        }

        return false;
    }
}
