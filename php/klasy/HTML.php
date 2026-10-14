<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 13:10
 */

/**
 * Szkielet stron: nagłówki, menu boczne, ekran logowania i strona główna
 */
class HTML extends Common
{
    /**
     * Ścieżki SVG ikon (viewBox 0 0 24 24, obrys w kolorze tekstu)
     */
    const IKONY = [
        'home' => '<path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/>',
        'urlop' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'zwolnienie' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'zdalna' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'start' => '<circle cx="12" cy="12" r="9"/><path d="M10 8l6 4-6 4z"/>',
        'nadgodziny' => '<path d="M12 8v4l3 2"/><path d="M3.05 11a9 9 0 1 1 .5 4"/><path d="M3 4v5h5"/>',
        'raport' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'osoby' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-6 7-6s7 2.5 7 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M22 20c0-3-2-5.2-5-5.8"/>',
        'import' => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 21h16"/>',
        'zastepca' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-6 7-6 1.5 0 2.8.3 3.9.9"/><path d="M16 17l2 2 4-4"/>',
        'instrukcja' => '<path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z"/>',
        'dokumenty' => '<path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'wejscia' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h6v18h-6"/>',
        'wyloguj' => '<path d="M14 7l5 5-5 5M19 12H8"/><path d="M10 3H4v18h6"/>',
        'szukaj' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'gwiazda' => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
    ];

    /**
     * Pozycje menu. Grupy: kategoria 'prod' - wnioski i raporty pracownika, 'admin' - funkcje działu HR.
     * @return array Lista grup [tytuł, pozycje]
     */
    function Menu(): array
    {
        $menu = [
            ['Wnioski', [
                ['modyfikuj.php', 'urlop', 'Wniosek urlopowy', 'urlop', 'prod', 'Urlop wypoczynkowy, na żądanie, okolicznościowy, opieka nad dzieckiem i inne nieobecności.'],
                ['modyfikuj.php', 'zwolnienie', 'Zwolnienie z godzin / zmiana czasu', 'zwolnienie', 'prod', 'Zwolnienie z części dnia pracy z odpracowaniem albo zmiana godzin pracy.'],
                ['modyfikuj.php', 'zdalna', 'Praca zdalna', 'zdalna', 'prod', 'Wniosek o pracę zdalną okazjonalną lub hybrydową.'],
                ['modyfikuj.php', 'rozpocznij_zdalna', 'Rozpocznij / zakończ pracę zdalną', 'start', 'prod', 'Zarejestrowanie rozpoczęcia, przerwy i zakończenia pracy zdalnej.'],
                ['rozliczenie_nadgodzin.php', '', 'Rozliczenie nadgodzin', 'nadgodziny', 'prod', 'Polecenia pracy w godzinach nadliczbowych i ich akceptacja.'],
            ]],
            ['Raporty', [
                ['raporty.php', 'urlop', 'Raport urlopów', 'raport', 'prod', 'Wnioski urlopowe, akceptacja przez przełożonego i HR, wydruk PDF.'],
                ['raporty.php', 'zwolnienia', 'Raport zwolnień / zmiany czasu', 'raport', 'prod', 'Wnioski o zwolnienie z godzin i zmianę czasu pracy.'],
                ['raporty.php', 'stan_urlopu', 'Stan urlopu i nadgodzin', 'raport', 'prod', 'Aktualny stan urlopu, urlopu zaległego i nadgodzin pracowników.'],
                ['raporty.php', 'wejscia', 'Stan wejść i wyjść', 'wejscia', 'prod', 'Wejścia i wyjścia pracowników z systemu RCP.'],
                ['raporty.php', 'odbicia', 'Raport odbić', 'wejscia', 'prod', 'Odbicia kart na kołowrotkach i czytnikach RCP.'],
                ['raporty.php', 'praca_zdalna', 'Raport pracy zdalnej', 'raport', 'prod', 'Zarejestrowane rozpoczęcia i zakończenia pracy zdalnej.'],
                ['raporty.php', 'praca_zdalna_czas_pracy', 'Praca zdalna - czas pracy', 'raport', 'prod', 'Czas pracy zdalnej w poszczególnych dniach.'],
            ]],
        ];
        if ($this->JestHR()) {
            $menu[] = ['Administracja HR', [
                ['raporty.php', 'wszystkie', 'Wszystkie osoby', 'osoby', 'admin', 'Lista wszystkich osób w eWnioskach z przełożonymi i uprawnieniami.'],
                ['raporty.php', 'wczytaj_urlop', 'Wczytaj liczbę urlopu', 'import', 'admin', 'Import stanu urlopu i nadgodzin z arkusza Excel.'],
                ['raporty.php', 'sprawdz_zastepce', 'Sprawdź zastępcę', 'zastepca', 'admin', 'Zastępcy przełożonych i pisanie wniosków za innych pracowników.'],
            ]];
        }
        $menu[] = ['Pomoc', [
            ['index.php', 'tym=1', 'Instrukcja', 'instrukcja', 'prod', 'Instrukcja obsługi programu eWnioski.'],
            ['dokumenty.php', '', 'Dokumenty i wnioski', 'dokumenty', 'prod', 'Wzory dokumentów i wniosków do pobrania.'],
        ]];
        return $menu;
    }

    /**
     * Ikona SVG
     */
    function Ikona(string $nazwa, int $rozmiar = 18): string
    {
        return '<svg width="' . $rozmiar . '" height="' . $rozmiar . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . (self::IKONY[$nazwa] ?? '') . '</svg>';
    }

    /**
     * Adres pozycji menu
     */
    private function AdresPozycji(array $pozycja): string
    {
        return $pozycja[0] . ($pozycja[1] !== '' ? '?' . $pozycja[1] : '');
    }

    /**
     * Czy pozycja menu odpowiada aktualnej stronie
     */
    private function CzyAktywna(array $pozycja): bool
    {
        if (basename($_SERVER['PHP_SELF'] ?? '') !== $pozycja[0]) return false;
        if ($pozycja[1] === '') return true;
        [$klucz, $wartosc] = array_pad(explode('=', $pozycja[1], 2), 2, null);
        return isset($_GET[$klucz]) && ($wartosc === null || $_GET[$klucz] == $wartosc);
    }

    /**
     * Tytuł bieżącej strony na podstawie menu
     */
    private function TytulStrony(): string
    {
        foreach ($this->Menu() as [, $pozycje]) {
            foreach ($pozycje as $pozycja) {
                if ($this->CzyAktywna($pozycja)) return $pozycja[2];
            }
        }
        return 'Strona główna';
    }

    /**
     * Wspólny nagłówek HTML wszystkich stron
     * @param string $tytul Tytuł w karcie przeglądarki
     * @param array $skrypty Dodatkowe skrypty (ścieżki względem php/)
     * @param array $style Dodatkowe arkusze stylów
     */
    function NaglowekHtml(string $tytul, array $skrypty = [], array $style = []): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>
            <title><?php echo htmlspecialchars($tytul); ?></title>
            <script>
                (function () {
                    try {
                        var motyw = localStorage.getItem('ewnioski-motyw');
                        if (motyw === 'dark' || (motyw === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                            document.documentElement.classList.add('dark');
                        }
                    } catch (e) {}
                })();
            </script>
            <link rel="preconnect" href="https://fonts.googleapis.com"/>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"/>
            <?php foreach ($style as $arkusz) { ?>
                <link rel="stylesheet" type="text/css" href="<?php echo $arkusz; ?>"/>
            <?php } ?>
            <link rel="stylesheet" type="text/css" href="../css/app.css?v=<?php echo @filemtime(EWNIOSKI_ROOT . '/css/app.css'); ?>"/>
            <?php foreach ($skrypty as $skrypt) { ?>
                <script type="text/javascript" src="<?php echo $skrypt; ?>"></script>
            <?php } ?>
            <script type="text/javascript" src="../js/app.js?v=<?php echo @filemtime(EWNIOSKI_ROOT . '/js/app.js'); ?>" defer></script>
        </head>
        <?php
    }

    /**
     * Przełącznik trybu ciemnego
     */
    function PrzelacznikMotywu(): void
    {
        ?>
        <div class="sidebar-footer">
            <span>Tryb ciemny</span>
            <button type="button" class="toggle" aria-pressed="false" aria-label="Tryb ciemny" data-przelacznik-motywu>
                <span class="toggle-knob"></span>
            </button>
        </div>
        <?php
    }

    /**
     * Logo programu
     */
    function Logo(): void
    {
        ?>
        <div class="brand">
            <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
                <rect width="28" height="28" rx="8" fill="var(--red)"/>
                <path d="M8 9h12M8 14h12M8 19h7" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
            <span class="brand-name">eWnioski</span>
        </div>
        <?php
    }

    /**
     * Wyświetlenie formularza do logowania
     */
    function PoczatekStronyLogowanie()
    {
        $this->NaglowekHtml('eWnioski ' . date("Y"));
        ?>
        <body class="auth-page">
        <main class="auth-shell">
        <?php
    }

    function PoczatekStronyLogowanieDotyk()
    {
        $this->NaglowekHtml('eWnioski ' . date("Y"), ['../js/Common.js?6']);
        ?>
        <body class="auth-page" onclick="WyczyscLicznik()">
        <?php $this->LicznikWylogowania(60); ?>
        <main class="auth-shell">
        <?php
    }

    /**
     * Licznik automatycznego wylogowania na ekranach dotykowych
     * @param int $sekundy Czas do wylogowania
     */
    function LicznikWylogowania(int $sekundy): void
    {
        ?>
        <input type="hidden" name="pozostalo" id="pozostalo" value="<?php echo $sekundy; ?>">
        <script>
            function WyczyscLicznik() {
                document.getElementById('pozostalo').value = '<?php echo $sekundy; ?>';
            }
            setInterval(function () {
                var pole = document.getElementById('pozostalo');
                var pozostalo = parseInt(pole.value, 10) - 1;
                pole.value = pozostalo;
                var czas = document.getElementById('czas');
                if (czas) czas.textContent = pozostalo;
                if (pozostalo <= 0) window.location.href = "dotyk.php?wyloguj";
            }, 1000);
        </script>
        <?php
    }

    /**
     * Karta formularza na stronie logowania
     */
    private function KartaLogowania(string $tytul, string $podtytul, callable $tresc): void
    {
        ?>
        <section class="auth-card">
            <?php $this->Logo(); ?>
            <h1><?php echo $tytul; ?></h1>
            <?php if ($podtytul !== '') { ?><p class="auth-sub"><?php echo $podtytul; ?></p><?php } ?>
            <?php $tresc(); ?>
        </section>
        <?php
    }

    /**
     * Wyświetlenie formularza do logowania
     */
    function FormularzLogowania()
    {
        $this->KartaLogowania('Logowanie', 'W przypadku pierwszego logowania do programu używamy swojego numeru ewidencyjnego i pustego hasła.', function () {
            ?>
            <form action="log.php" method="POST" class="form-stack">
                <label class="field">
                    <span class="field-label">Numer ewidencyjny</span>
                    <input type="text" name="login" id="login" autocomplete="username" autofocus/>
                </label>
                <label class="field">
                    <span class="field-label">Hasło</span>
                    <input type="password" name="password" autocomplete="current-password"/>
                </label>
                <button type="submit" class="btn btn-primary">Zaloguj</button>
            </form>
            <div class="auth-links">
                <form action="login.php" method="POST">
                    <input type="hidden" name="zmiana_hasla" value="zmiana_hasla">
                    <button type="submit" class="btn btn-link">Zmiana hasła</button>
                </form>
                <form action="login.php" method="POST">
                    <input type="hidden" name="przypomnienie_hasla" value="przypomnienie_hasla">
                    <button type="submit" class="btn btn-link">Przypomnienie hasła</button>
                </form>
            </div>
            <?php
        });
    }

    /**
     * Wyświetlenie formularza do logowania na urzadzeniach dotykowych
     */
    function FormularzLogowaniaDotyk()
    {
        $this->KartaLogowania('Proszę zeskanować przepustkę', '', function () {
            ?>
            <p class="countdown">Pozostało do wylogowania: <strong id="czas">60</strong> s</p>
            <form action="log_dotyk.php" method="POST" class="form-stack">
                <label class="field">
                    <span class="field-label">Numer przepustki</span>
                    <input type="password" name="przepystka" id="przepystka" autocomplete="off" autofocus/>
                </label>
                <button type="submit" class="btn btn-primary btn-large">Zaloguj</button>
            </form>
            <?php
        });
    }

    /**
     * Formularz utworzenia hasła przy pierwszym logowaniu
     */
    function PoczatekStronyUtworzenie($imie, $nazwisko, $numerewidencyjny, $komunikat)
    {
        $this->PoczatekStronyLogowanie();
        $this->KartaLogowania('Utwórz hasło', 'Login nie ma jeszcze utworzonego hasła.', function () use ($imie, $nazwisko, $numerewidencyjny, $komunikat) {
            ?>
            <p>Tworzysz hasło dla: <strong><?php echo htmlspecialchars($imie . ' ' . $nazwisko . ' - ' . $numerewidencyjny); ?></strong></p>
            <?php $this->Alert('Proszę wpisać prawidłowy firmowy adres e-mail.', '', 'info'); ?>
            <?php if ($komunikat != '') $this->Alert($komunikat, '', 'error'); ?>
            <form action="log.php" method="POST" class="form-stack">
                <label class="field">
                    <span class="field-label">E-mail</span>
                    <input type="email" name="email" id="email" value="" autocomplete="email" required>
                </label>
                <label class="field">
                    <span class="field-label">Hasło</span>
                    <input type="password" name="utworz_haslo" id="utworz_haslo" autocomplete="new-password">
                </label>
                <label class="field">
                    <span class="field-label">Powtórz hasło</span>
                    <input type="password" name="powtorz_haslo" id="powtorz_haslo" autocomplete="new-password">
                </label>
                <input type="hidden" name="login" value="<?php echo htmlspecialchars((string)$numerewidencyjny); ?>">
                <button type="submit" class="btn btn-primary">Utwórz hasło</button>
            </form>
            <?php
        });
    }

    /**
     * Domknięcie tagów końca strony logowania
     */
    function KoniecStronyLogowanie()
    {
        ?>
        <p class="auth-footer"><?php $this->Opracowal(); ?></p>
        </main>
        </body>
        </html>
        <?php
    }

    /**
     * Informacja o dacie programu i programiście
     */
    function Opracowal()
    {
        echo 'Opracował Jakub Duda 2019 r.';
    }

    /**
     * Komunikat o nieprawidłowym loginie
     */
    function NieprawidlowyLogin()
    {
        $komunikaty = [
            1 => 'Wpisane złe dane logowania',
            2 => 'Nie znaleziono takiego numeru ewidencyjnego w bazie',
            3 => 'Nie znaleziono aktywnej karty z takim numerem',
            4 => 'Użytkownik tej karty nie ma przypisanego przełożonego. Zgłoś się do działu HR',
            5 => 'Użytkownik już nie pracuje w PKC',
        ];
        $komunikat = $komunikaty[(int)($_GET['fail'] ?? 0)] ?? null;
        if ($komunikat !== null) {
            $this->Alert($komunikat, '', 'error');
        }
    }

    function ZlaPrzegladarka()
    {
        $przegladarki = [
            ['https://www.mozilla.org/pl/firefox/new/', '../img/firefox.png', 'Mozilla Firefox'],
            ['https://www.google.pl/chrome/browser/desktop/index.html', '../img/chrome.png', 'Google Chrome'],
            ['http://www.opera.com/pl/download', '../img/opera.png', 'Opera'],
        ];
        ?>
        <section class="auth-card auth-card-wide">
            <h1>Nieprawidłowa przeglądarka!</h1>
            <p class="auth-sub">Przeglądarka Internet Explorer, Microsoft Edge i Safari nie są wspierane przez tą aplikację. Proszę użyć jednej z poniższych przeglądarek:</p>
            <div class="browser-list">
                <?php foreach ($przegladarki as [$adres, $obrazek, $nazwa]) { ?>
                    <a href="<?php echo $adres; ?>" target="_blank" class="app-card">
                        <img src="<?php echo $obrazek; ?>" alt="" width="48" height="48">
                        <span class="app-name"><?php echo $nazwa; ?></span>
                    </a>
                <?php } ?>
            </div>
        </section>
        <?php
    }

    function StaraPrzegladarka($wersja)
    {
        ?>
        <section class="auth-card">
            <h1>Stara wersja przeglądarki!</h1>
            <p class="auth-sub">Program wymaga wersji Firefox co najmniej 57, natomiast ty masz <?php echo htmlspecialchars((string)$wersja); ?>. Proszę zaktualizować przeglądarkę.</p>
        </section>
        <?php
    }

    /**
     * Formularz zmiany hasła
     */
    function FormularzZmianyhasla()
    {
        ?>
        <section class="auth-card">
            <h2>Zmiana hasła</h2>
            <form method="POST" class="form-stack">
                <label class="field"><span class="field-label">Login</span><input type="text" name="zmiana_login"/></label>
                <label class="field"><span class="field-label">Stare hasło</span><input type="password" name="zmiana_stare_haslo"/></label>
                <label class="field"><span class="field-label">Nowe hasło</span><input type="password" name="zmiana_nowe_haslo"/></label>
                <label class="field"><span class="field-label">Powtórz nowe hasło</span><input type="password" name="zmiana_nowe_haslo_powtorz"/></label>
                <button type="submit" name="zmiana" value="Zmień hasło" class="btn btn-primary">Zmień hasło</button>
            </form>
        </section>
        <?php
    }

    function FormularzPrzypomnieniaHasla()
    {
        ?>
        <section class="auth-card">
            <h2>Przypomnienie hasła</h2>
            <form method="POST" class="form-stack">
                <label class="field"><span class="field-label">Login (numer ewidencyjny) podany przy rejestracji</span><input type="text" name="login_przypomnienie"/></label>
                <label class="field"><span class="field-label">E-mail podany przy rejestracji</span><input type="email" name="email_przypomnienie"/></label>
                <button type="submit" name="przypomnij" value="Przypomnij hasło" class="btn btn-primary">Przypomnij hasło</button>
            </form>
        </section>
        <?php
    }

    /**
     * Początek strony, nagłówki, menu boczne
     * @param string|null $tytul Nagłówek strony (domyślnie nazwa pozycji menu)
     */
    function PoczatekStrony($tytul = null)
    {
        $tytul ??= $this->TytulStrony();
        $this->NaglowekHtml($tytul . ' - eWnioski', [
            '../js/jquery.js',
            '../js/ajax.js',
            '../js/Common.js?v=' . @filemtime(EWNIOSKI_ROOT . '/js/Common.js'),
            '../js/jquery.simplemodal.js',
            '../js/jquery_1.10.1.js',
            '../js/jquery-ui-1.10.3.custom.js',
            '../css/scw.js?2',
        ], ['../css/jquery-ui.css']);
        ?>
        <body>
        <div class="app-shell">
            <aside class="sidebar">
                <div class="sidebar-top">
                    <a href="index.php" class="brand-link"><?php $this->Logo(); ?></a>
                    <button type="button" class="menu-toggle" aria-expanded="false" data-menu-toggle>Menu</button>
                </div>
                <div class="search-box">
                    <?php echo $this->Ikona('szukaj', 14); ?>
                    <input type="search" placeholder="Szukaj w menu..." data-szukaj-menu aria-label="Szukaj w menu"/>
                </div>
                <nav class="category-nav">
                    <a href="index.php" class="tab-btn<?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' && !isset($_GET['tym'])) ? ' active' : ''; ?>" data-category="all">Strona główna</a>
                    <?php foreach ($this->Menu() as [$grupa, $pozycje]) { ?>
                        <div class="field-label" data-grupa-menu><?php echo $grupa; ?></div>
                        <?php foreach ($pozycje as $pozycja) { ?>
                            <a href="<?php echo $this->AdresPozycji($pozycja); ?>" data-category="<?php echo $pozycja[4]; ?>"
                               class="tab-btn<?php echo $this->CzyAktywna($pozycja) ? ' active' : ''; ?>"><?php echo $pozycja[2]; ?></a>
                        <?php } ?>
                    <?php } ?>
                </nav>
                <div class="sidebar-user">
                    <?php $this->PokazDaneUzytkownika(); ?>
                </div>
                <?php $this->PrzelacznikMotywu(); ?>
                <a class="admin-link" href="#" data-otworz-modal="modal-wersja">Wersja programu <?php echo $this->WersjaProgramu(); ?></a>
                <a class="admin-link" href="index.php?wyloguj">Wyloguj</a>
            </aside>
            <main class="content">
                <div class="content-header">
                    <h1><?php echo htmlspecialchars($tytul); ?></h1>
                    <span class="results-count"><?php echo date('d.m.Y'); ?></span>
                </div>
                <?php
                $this->WyswietlKomunikat();
                if (isset($_SESSION['ezla_komunikat'])) {
                    echo '<div class="notice">' . $_SESSION['ezla_komunikat'] . '</div>';
                    unset($_SESSION['ezla_komunikat']);
                }
                ?>
                <div class="page-body">
        <?php
    }

    /**
     * Początek prostej strony otwieranej w osobnym oknie (np. edycja pozycji nadgodzin)
     * @param string $tytul Tytuł okna
     */
    function PoczatekStronyOkna(string $tytul): void
    {
        $this->NaglowekHtml($tytul, [
            '../js/jquery_1.10.1.js',
            '../js/jquery-ui-1.10.3.custom.js',
            '../css/scw.js',
            '../js/Common.js?v=' . @filemtime(EWNIOSKI_ROOT . '/js/Common.js'),
        ], ['../css/jquery-ui.css']);
        ?>
        <body class="popup-page">
        <main class="content popup-content">
            <div class="page-body">
        <?php
    }

    /**
     * Koniec strony otwieranej w osobnym oknie
     */
    function KoniecStronyOkna(): void
    {
        ?>
            </div>
        </main>
        </body>
        </html><?php
    }

    /**
     * Okno z historią zmian programu
     */
    function OknoWersji(): void
    {
        ?>
        <div class="modal-overlay" id="modal-wersja" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-wersja-tytul">
                <div class="modal-header">
                    <h2 id="modal-wersja-tytul">Historia wersji</h2>
                    <button type="button" data-zamknij-modal aria-label="Zamknij">&times;</button>
                </div>
                <div class="modal-body"><?php echo nl2br(htmlspecialchars((string)@file_get_contents(EWNIOSKI_ROOT . '/wersja/wersja.txt'))); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Początek strony na ekranach dotykowych
     */
    function PoczatekStronyDotyk()
    {
        $czas = isset($_GET['czas']) ? 10 : 60;
        $this->NaglowekHtml('eWnioski ' . date('Y'), [
            '../js/jquery.js',
            '../js/ajax.js',
            '../js/Common.js?v=' . @filemtime(EWNIOSKI_ROOT . '/js/Common.js'),
            '../js/jquery.simplemodal.js',
            '../css/scwdotyk.js',
        ], ['../css/jsKeyboard.css']);
        $kafelki = [
            ['dotyk.php?dodaj_urlop', 'Dodaj urlop', 'urlop', 'dodaj_urlop'],
            ['dotyk.php?dodaj_zwolnienie', 'Dodaj zwolnienie', 'zwolnienie', 'dodaj_zwolnienie'],
            ['dotyk.php?raport_urlopow', 'Raport urlopów', 'raport', 'raport_urlopow'],
            ['dotyk.php?raport_zwolnien', 'Raport zwolnień', 'raport', 'raport_zwolnien'],
            ['dotyk.php?stan_godzin', 'Stan nadgodzin', 'nadgodziny', 'stan_godzin'],
            ['dotyk.php?wyloguj', 'Wyloguj', 'wyloguj', 'wyloguj'],
        ];
        ?>
        <body class="kiosk" onclick="WyczyscLicznik()">
        <?php $this->LicznikWylogowania($czas); ?>
        <header class="kiosk-header">
            <?php $this->Logo(); ?>
            <?php $this->PokazDaneUzytkownika(); ?>
            <span class="countdown">Pozostało do wylogowania: <strong id="czas"><?php echo $czas; ?></strong> s</span>
        </header>
        <nav class="kiosk-nav">
            <?php foreach ($kafelki as [$adres, $nazwa, $ikona, $klucz]) { ?>
                <a href="<?php echo $adres; ?>" class="app-card<?php echo isset($_GET[$klucz]) ? ' active' : ''; ?>" data-category="<?php echo $klucz == 'wyloguj' ? 'admin' : 'prod'; ?>">
                    <span class="app-icon"><?php echo $this->Ikona($ikona, 26); ?></span>
                    <span class="app-name"><?php echo $nazwa; ?></span>
                </a>
            <?php } ?>
        </nav>
        <main class="content kiosk-content">
            <div class="page-body">
        <?php
    }

    /**
     * Koniec strony głównej
     */
    function KoniecStrony()
    {
        // Raporty kończą ostatnią tabelę bez </table> (dawniej domykał ją układ strony).
        // Nadmiarowy </table> przeglądarka pomija, a brakujący przesunąłby stopkę nad tabelę.
        ?>
                </table>
                </div>
                <?php $this->StopkaJezyki(); ?>
            </main>
        </div>
        <?php $this->OknoWersji(); ?>
        </body>
        </html><?php
    }

    /**
     * Koniec strony na ekranach dotykowych
     */
    function KoniecStronyDotyk()
    {
        ?>
            </table>
            </div>
        </main>
        </body>
        </html><?php
    }

    /**
     * Koniec strony z raportami
     */
    function KoniecStronyRaporty()
    {
        $this->KoniecStrony();
    }

    /**
     * Strona główna: kafelki z funkcjami programu i ulubione
     */
    function LogoW()
    {
        $liczba = 0;
        foreach ($this->Menu() as [, $pozycje]) $liczba += count($pozycje);
        ?>
        <div class="favorites-row" data-ulubione hidden>
            <div class="section-label">Ulubione</div>
            <div class="favorites-track" data-ulubione-lista></div>
        </div>
        <?php foreach ($this->Menu() as [$grupa, $pozycje]) { ?>
            <div class="section-label section-gap"><?php echo $grupa; ?></div>
            <div class="app-grid">
                <?php foreach ($pozycje as $pozycja) {
                    $adres = $this->AdresPozycji($pozycja); ?>
                    <a href="<?php echo $adres; ?>" class="app-card" data-category="<?php echo $pozycja[4]; ?>" data-skrot="<?php echo htmlspecialchars($adres); ?>" data-nazwa="<?php echo htmlspecialchars($pozycja[2]); ?>">
                        <button type="button" class="fav-star" aria-label="Dodaj do ulubionych" data-gwiazdka>
                            <?php echo $this->Ikona('gwiazda', 16); ?>
                        </button>
                        <span class="app-icon"><?php echo $this->Ikona($pozycja[3]); ?></span>
                        <span class="app-name"><?php echo $pozycja[2]; ?></span>
                        <span class="app-purpose"><?php echo $pozycja[5]; ?></span>
                        <span class="app-meta"><span class="badge"><?php echo $pozycja[4] == 'admin' ? 'HR' : $grupa; ?></span></span>
                        <span class="app-hover-info">
                            <span class="hover-info-title"><?php echo $pozycja[2]; ?></span>
                            <span class="hover-info-row"><span class="hover-info-label">Do czego służy</span><?php echo $pozycja[5]; ?></span>
                        </span>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
        <p class="results-count section-gap"><?php echo $liczba; ?> funkcji dostępnych w programie. Gwiazdką oznaczysz te, których używasz najczęściej.</p>
        <?php
    }

    /**
     * Strona startowa na ekranach dotykowych
     */
    function LogoWDotyk()
    {
        ?>
        <div class="panel kiosk-info">
            <h1>UWAGA !!!</h1>
            <h2>Wprowadzony przez ciebie wniosek nie oznacza jego akceptacji. <br> Skontaktuj się z przełożonym w celu potwierdzenia udzielenia urlopu.</h2>
            <h3>W przypadku braku akceptacji przełożonego zostanie wprowadzona</h3>
            <h2 class="text_red">nieobecność nieusprawiedliwiona.</h2>
        </div>
        <?php
    }

    function Instrukcja()
    {
        ?>
        <div class="panel panel-flush">
            <iframe src="../doc/instrukcja_ewnioski.pdf#toolbar=0&amp;statusbar=0&amp;messages=0&amp;navpanes=0" class="pdf-frame" title="Instrukcja"></iframe>
        </div>
        <?php
    }
}
