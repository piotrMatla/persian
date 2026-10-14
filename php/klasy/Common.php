<?php

/**
 *  Podstawowe funkcje programu
 * \author Jakub Duda
 * \version 1.4
 * \date 07-2017
 */
class Common extends Filtrowanie {

    /**
     * Typy plików wysyłanych do pobrania
     */
    const TYPY_PLIKOW = [
        'pdf' => 'application/pdf',
        'exe' => 'application/octet-stream',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'txt' => 'text/plain',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
    ];

    /**
     *  Ustawienei sesji i strefy czasowej
     */
    function UstawieniaStandardowe() {
        set_time_limit(21600);
        ini_set("memory_limit", "3072M");
        if (!headers_sent()) header("Content-Type: text/html; charset=utf-8");
        date_default_timezone_set('Europe/Warsaw');
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        // zmiana zakładu z listy w menu bocznym działa na każdej stronie
        if (isset($_SESSION['ewnioski_login'], $_POST['ewnioski_profil'])) {
            $_SESSION['ewnioski_profil'] = $_POST['ewnioski_profil'];
        }
    }

    /**
     * Sprawdzenie czy użytkownik jest zalogowany
     * @param string $stronaLogowania Strona, na którą trafia niezalogowany użytkownik
     */
    function CzyZalogowany($stronaLogowania = 'login.php') {
        $skrypt = basename($_SERVER['PHP_SELF'] ?? '');
        if (isset($_SESSION['ewnioski_login'])) {
            if ($skrypt != 'index.php' and isset($_POST['login'])) {
                header('Location: index.php');
                exit;
            }
        } elseif ($skrypt != $stronaLogowania) {
            header('Location: ' . $stronaLogowania);
            exit;
        }
    }

    /**
     * Sprawdzenie czy użytkownik jest zalogowany
     */
    function CzyZalogowanyDotyk() {
        $this->CzyZalogowany('login_dotyk.php');
    }

    /**
     * Czy zalogowany użytkownik należy do działu HR (MPK-i z conf/config.php)
     */
    function JestHR(): bool {
        return in_array($_SESSION['ewnioski_mpk'] ?? '', Konfiguracja::Pobierz('mpk_hr', []), true);
    }

    /**
     * Zmiana hasła
     */
    function ZmianaHasla() {
        $pola = [
            'zmiana_login' => 'login',
            'zmiana_stare_haslo' => 'stare hasło',
            'zmiana_nowe_haslo' => 'nowe hasło',
            'zmiana_nowe_haslo_powtorz' => 'powtórz nowe hasło',
        ];
        $braki = [];
        foreach ($pola as $pole => $opis) {
            if (trim($_POST[$pole] ?? '') == '') $braki[] = 'Pole ' . $opis . ' nie zostało uzupełnione';
        }
        if ($braki) {
            $this->Alert('Wystąpił problem ze zmianą hasła', implode('<br/>', $braki), 'error');
            return;
        }
        $polaczenie = $this->MysqliConnect();
        $login = trim($_POST['zmiana_login']);
        $zapytanie = "SELECT * FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . $this->Esc($polaczenie, $login) . "'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $login);
        $wiersz = mysqli_fetch_array($wykonaj);
        if ($wiersz and $wiersz['numer_ewidencyjny'] == $login and $wiersz['haslo_urlopy'] == trim($_POST['zmiana_stare_haslo'])
            and trim($_POST['zmiana_nowe_haslo']) == trim($_POST['zmiana_nowe_haslo_powtorz'])) {
            $zapytanie = "UPDATE ewidencja_szkolen.osoby SET haslo_urlopy='" . $this->Esc($polaczenie, $_POST['zmiana_nowe_haslo']) . "' WHERE id='" . $wiersz['id'] . "'";
            mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $login);
            $this->ZapiszLog('Zapytanie; ' . $zapytanie, $login);
            $this->Alert('Hasło zostało zmienione.', '', 'success');
        } else {
            $this->Alert('Wystąpiły problemy ze zmianą hasła', 'Podany login nie zgadza się z hasłem.', 'error');
        }
        mysqli_close($polaczenie);
    }

    /**
     *  Tworzenie połączenia do bazy danych (dane w conf/config.php)
     * @return mysqli Połączenie z bazą
     */
    function MysqliConnect() {
        $db = Konfiguracja::Pobierz('db');
        $polaczenie = mysqli_connect($db['host'], $db['user'], $db['haslo'], $db['baza'], $db['port'] ?? 3306) or die('Brak połączenia z serwerem MySQL.');
        mysqli_query($polaczenie, "SET CHARSET utf8") or die(mysqli_error($polaczenie));
        mysqli_query($polaczenie, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'") or die(mysqli_error($polaczenie));
        return $polaczenie;
    }

    /**
     * Zabezpieczenie tekstu wstawianego do zapytania SQL
     */
    function Esc($polaczenie, $tekst): string {
        return mysqli_real_escape_string($polaczenie, (string)$tekst);
    }

    /**
     *  Komunikat o błędzie zapytania do bazy danych MySQL
     * @param mysqli $polaczenie Połączenie do bazy
     * @param string $zapytanie Zapytanie do bazy
     * @param string $login Login użytkownika który wykonał zapytanie
     */
    function BladZapytania($polaczenie, $zapytanie, $login) {
        if (mysqli_error($polaczenie)) {
            $this->ZglosBladZapytania(is_string($zapytanie) ? $zapytanie : '(brak treści zapytania)', (string)$login, mysqli_error($polaczenie));
        }
    }

    /**
     * Wysłanie maila o błędzie zapytania, wyświetlenie komunikatu i zakończenie skryptu
     */
    private function ZglosBladZapytania(string $zapytanie, string $login, string $blad): never {
        $confW = $this->ParseIniE();
        $this->WyslijMailBladSQL($confW['MailProgramista'] ?? '', $zapytanie, $login, $blad);
        $this->Alert(
            'Błąd z zapytaniem',
            htmlspecialchars($zapytanie) . '<br/>Informacja została już wysłana do działu IT. Jak problem będzie się powtarzał skontaktuj się z działem IT.<br/>' . htmlspecialchars($blad),
            'error'
        );
        die;
    }

    /**
     *  Komunikat o błędzie zapytania do bazy danych MSSQL
     * @param resource $polaczenie Połączenie do bazy
     * @param string $zapytanie Zapytanie do bazy
     * @param string $login Login użytkownika który wykonał zapytanie
     */
    function BladZapytaniaMSSQL($polaczenie, $zapytanie, $login) {
        $blad = '';
        $code = '';
        $wiadomosc = '';

        if ($polaczenie === false) {
            $blad .= "Błąd połączenia <br>";
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    if (isset($error['SQLSTATE'])) {
                        $blad = $blad . $error['SQLSTATE'] . '<br>';
                        $code = $code . $error['code'] . '<br>';
                        $wiadomosc = $wiadomosc . $error['message'] . '<br>';
                    } else $blad = json_encode($error);
                }
            }
        }

        $stmt = sqlsrv_query($polaczenie, $zapytanie);
        if ($stmt === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    if (isset($error['SQLSTATE'])) {
                        $blad .= $error['SQLSTATE'];
                        $code .= $error['code'];
                        $wiadomosc .= $error['message'];
                    } else $blad = json_encode($error);
                }
            }
            $this->ZglosBladZapytania($zapytanie, (string)$login, $blad . '<br>' . $code . '<br>' . $wiadomosc);
        }
    }

    /**
     *  Komunikat o błędzie zapytania ODBC
     */
    function BladZapytaniaODBC($polaczenie, $zapytanie, $login) {
        if (odbc_error($polaczenie) != '') {
            $this->ZglosBladZapytania($zapytanie, (string)$login, json_encode(odbc_error($polaczenie)));
        }
    }

    /**
     *  Stopka strony
     */
    function StopkaJezyki() {
        ?>
        <footer class="page-footer">eWnioski <?php echo date('Y'); ?></footer>
        <?php
    }

    /**
     * Numer wersji programu z pliku wersja/wersja.txt
     */
    function WersjaProgramu(): string {
        $wersja = explode("\n", (string)@file_get_contents(EWNIOSKI_ROOT . '/wersja/wersja.txt'));
        $wersja = explode(":", $wersja[0]);
        return isset($wersja[1]) ? trim($wersja[1]) : 'bd.';
    }

    /**
     *  Informacje o użytkowniku i wersji programu
     */
    function PokazDaneUzytkownika() {
        $dane = [
            'Zalogowany' => $_SESSION['ewnioski_full_name'] ?? null,
            'E-mail' => $_SESSION['ewnioski_mail'] ?? null,
            'Przełożony' => $_SESSION['ewnioski_kierownik_imie'] ?? null,
            'Logowanie' => isset($_SESSION['ewnioski_data']) ? $_SESSION['ewnioski_data'] . ' ' . ($_SESSION['ewnioski_czas'] ?? '') : null,
        ];
        ?>
        <dl class="user-info">
            <?php foreach ($dane as $etykieta => $wartosc) {
                if ($wartosc === null) continue; ?>
                <div><dt><?php echo $etykieta; ?></dt><dd><?php echo htmlspecialchars((string)$wartosc); ?></dd></div>
            <?php } ?>
        </dl>
        <?php
        if (($_SESSION['ewnioski_zmiana_profilu'] ?? 0) == 1) {
            ?>
            <div class="field-label">Zakład</div>
            <form method="POST">
                <select name="ewnioski_profil" onchange="this.form.submit();" class="site-select">
                    <?php
                    $polaczenie = $this->MysqliConnect();
                    $zapytanie = "SELECT * FROM profil";
                    $wykonaj = mysqli_query($polaczenie, $zapytanie);
                    $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
                    while ($dane = mysqli_fetch_array($wykonaj)) {
                        ?>
                        <option <?php if ($dane['id'] == $_SESSION['ewnioski_profil']) echo "SELECTED"; ?> value="<?php echo $dane['id']; ?>"><?php echo $dane['nazwa']; ?></option>
                        <?php
                    }
                    mysqli_close($polaczenie);
                    ?>
                </select>
            </form>
            <?php
        }
    }

    /**
     *  Funkcja zapisjąca dane do logu
     * @param string $dane Wartosc która zostanie wpisana do logu
     * @param string $uzytkownik Login uzytkownika który wpisuje dane do logu
     */
    function ZapiszLog($dane, $uzytkownik) {
        $this->ZapiszDoPlikuLogu('', $dane, $uzytkownik);
    }

    /**
     *  Funkcja zapisjąca dane z importu do osobnego logu
     * @param string $dane Wartosc która zostanie wpisana do logu
     * @param string $uzytkownik Login uzytkownika który wpisuje dane do logu
     */
    function ZapiszLogImport($dane, $uzytkownik) {
        $this->ZapiszDoPlikuLogu('Import-', $dane, $uzytkownik);
    }

    /**
     * Dopisanie linii do tygodniowego pliku logu w folderze FolderLogs
     */
    private function ZapiszDoPlikuLogu(string $prefiks, $dane, $uzytkownik): void {
        $confW = $this->ParseIniE();
        $sciezka = ($confW['FolderLogs'] ?? '') . $prefiks . date('W') . '.txt';
        $linia = $dane . ';' . ' IP: ' . $this->PokazIp() . '; Uzytkownik ' . $uzytkownik . '; Data ' . date('Y-m-d H:i') . "\r\n";
        if (@file_put_contents($sciezka, $linia, FILE_APPEND) === false) {
            error_log('eWnioski: nie można zapisać logu do ' . $sciezka);
        }
    }

    /**
     *  Pokazanie adresu IP klienta
     * @return string Adres IP klienta
     */
    function PokazIp() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Brak IP';
    }

    /**
     * Wysłanie maila w formacie HTML
     * @param string $adresat Adresaci oddzieleni przecinkami
     * @param string $temat Temat maila
     * @param string $tresc Treść HTML
     * @param string|null $nadawca Nadawca (domyślnie z conf/config.php)
     */
    function WyslijMailHtml($adresat, $temat, $tresc, $nadawca = null) {
        $naglowki = "From: " . ($nadawca ?? Konfiguracja::Pobierz('mail_nadawca')) . "\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8";
        if (preg_match('/[^\x20-\x7e]/', $temat)) {
            $temat = mb_encode_mimeheader($temat, 'UTF-8', 'B');
        }
        return @mail((string)$adresat, $temat, $tresc, $naglowki);
    }

    /**
     * Szablon HTML maili wysyłanych z programu
     */
    function TrescMaila(string $srodek): string {
        return "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
                                $srodek
                                </center>
                                </body></html>";
    }

    /**
     *  Wysłanie maila o błędzie zapytania
     * @param string $adresat Adres mailowy na który ma być wysłany mail
     * @param string $tresc Treść maila
     * @param string $login Osoba u której wystąpił błąd w zapytaniu
     *  @param string $blad Rodzaj błedy przy zapytaniu
     */
    function WyslijMailBladSQL($adresat, $tresc, $login, $blad) {
        $page = $this->TrescMaila("<h2>eWnioski</h2><br/>
                                Mail został wygenerowany: " . date('Y-m-d H:i:s') . "<br/><br/>
                                Przez użytkownika: " . $login . "<br/>
                                <b>
                                <hr/>
                                <h2>Błąd z następującym zapytaniem <br/></h2>
                                $tresc <br/><br/>
                                $blad");
        $this->WyslijMailHtml($adresat, 'eWnioski blad wykonania zapytania', $page, Konfiguracja::Pobierz('mail_nadawca_bledy'));
    }

    /**
     * Wysanie maila (z kopią na adres z 'mail_kopia')
     * @param string $adresat Do kogo ma bywysany mail
     * @param string $temat Temat maila
     * @param string $page Treść maila
     */
    function Wyslij($adresat, $temat, $page) {
        $kopia = Konfiguracja::Pobierz('mail_kopia');
        if ($kopia) $adresat .= ',' . $kopia;
        $this->WyslijMailHtml($adresat, $temat, $page);
    }

    /**
     * Mail do pracownika i przełożonego o nowym wniosku
     * @param string $adresat Adresaci
     * @param string $rodzaj Np. 'urlopowy'
     * @param string $osoba Imię i nazwisko osoby, której dotyczy wniosek
     * @param string $przedzial Opis terminu
     * @param string $raport Raport, do którego prowadzi link (np. 'urlop')
     */
    function WyslijMailNowyWniosek($adresat, $rodzaj, $osoba, $przedzial, $raport) {
        $adres = Konfiguracja::Pobierz('adres_programu') . 'raporty.php?' . $raport;
        $page = $this->TrescMaila("<h2>Wpisano nowy wniosek $rodzaj dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=$adres > eWniosków</a>");
        $this->Wyslij($adresat, 'Wpisano nowy wniosek ' . $rodzaj, $page);
    }

    /**
     *  Wyświetlenie komunikatu po zmianach w bazie danych
     */
    function WyswietlKomunikat() {
        if (!isset($_SESSION['ewnioski_komunikat'])) return;
        echo '<div class="notice">' . $_SESSION['ewnioski_komunikat'] . '</div>';
        unset($_SESSION['ewnioski_komunikat']);
    }

    /**
     * Wyświetlenie komunikatu
     * @param string $tytul Nagłówek komunikatu
     * @param string $tresc Treść (HTML)
     * @param string $typ success | error | info
     */
    function Alert(string $tytul, string $tresc = '', string $typ = 'info'): void {
        ?>
        <div class="alert alert-<?php echo $typ; ?>">
            <?php if ($tytul !== '') { ?><div class="alert-title"><?php echo $tytul; ?></div><?php } ?>
            <?php if ($tresc !== '') { ?><div><?php echo $tresc; ?></div><?php } ?>
        </div>
        <?php
    }

    /**
     * Przejście na inną stronę po wykonaniu akcji i zakończenie skryptu
     * @param string $adres Adres strony
     */
    function Przekieruj(string $adres): never {
        if (!headers_sent()) {
            header('Location: ' . $adres);
        } else {
            echo '<script type="text/javascript">window.location.href = ' . json_encode($adres) . ';</script>';
        }
        exit;
    }

    /**
     *  Pobranie pliku konfiguracyjnego (ścieżka w conf/config.php)
     * @return array Dane konfiguracyjne
     */
    function ParseIniE() {
        $ini_path = Konfiguracja::Pobierz('ini');
        $dane = is_file($ini_path) ? parse_ini_file($ini_path) : false;
        if ($dane === false) {
            error_log("eWnioski: nie można odczytać pliku konfiguracyjnego: " . $ini_path);
            return [];
        }
        return $dane;
    }

    /**
     * Wyświetlenie wartości albo "bd." gdy jej brak
     */
    function BrakujaceDane($variable) {
        if ($variable == '' or $variable == NULL) {
            ?>
            <span class="text_red"><b>bd.</b></span>
            <?php
        } else {
            echo $variable;
        }
    }

    /**
     * Wylgowanie się z programu, usunięcie zmiennych sesyjnych
     */
    function Wyloguj() {
        $this->WyczyscFiltry();
        $this->WyczyscFiltryLogowanie();
        header('Location: login.php');
        exit;
    }

    function WylogujDotyk() {
        $this->WyczyscFiltry();
        $this->WyczyscFiltryLogowanie();
        header('Location: ' . Konfiguracja::Pobierz('wylogowanie_dotyk'));
        exit;
    }

    /**
     * Zamiana polskich liter na łacińskie (i usunięcie spacji)
     * @param string $dane
     * @return string
     */
    function PodmienPolskieLitery($dane) {
        $array1 = array('ą', 'ś', 'ż', 'ź', 'ć', 'ń', 'ł', 'ó', 'ę', ' ', 'Ą', 'Ś', 'Ż', 'Ź', 'Ć', 'Ń', 'Ł', 'Ó', 'Ę');
        $array2 = array('a', 's', 'z', 'z', 'c', 'n', 'l', 'o', 'e', '', 'A', 'S', 'Z', 'Z', 'C', 'N', 'L', 'O', 'E');
        return str_replace($array1, $array2, (string)$dane);
    }

    /**
     * Kolor wiersza w tabeli raportu (wiersze na przemian)
     * @param int $licznik Pozycja z raportu
     * @return string Kolor wiersza (w app.css zamieniany na kolory motywu)
     */
    function KolorWiersza($licznik)
    {
        return $licznik % 2 == 0 ? '#b5d4b4' : '#d6dbd6';
    }

    /**
     * Kolor wyróżnionego (czerwonego) wiersza w tabeli raportu
     * @param int $licznik Pozycja z raportu
     * @return string Kolor wiersza
     */
    function KolorWierszaCzerwony($licznik)
    {
        return $licznik % 2 == 0 ? 'FF0033' : 'CC0033';
    }

    /**
     * Liczba sformatowana do wyświetlenia (puste wartości z bazy jako 0)
     */
    function Liczba($wartosc, int $miejsca = 0): string {
        return number_format(is_numeric($wartosc) ? (float)$wartosc : 0.0, $miejsca);
    }

    /**
     * Sprawdzanie czy dany numer ewidencyjny jest na liscei wyjątków
     * @param $numer Nume ewidencyjny osoby która ma być sprawdzony
     * @param $login Login osoby kóry wykonuje zapytanie
     * @return string|null Zwrócony wynik z tabeli
     */
    function SprawdzOsobe($numer, $login)
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT o.imie, o.nazwisko, o.email, s.numer_kierownik
                        FROM stan_godzin s
                        LEFT JOIN ewidencja_szkolen.osoby o on s.numer_kierownik=o.numer_ewidencyjny
                        WHERE s.numer_ewidencyjny='" . $this->Esc($polaczenie, trim((string)$numer)) . "'  ORDER BY s.ID DESC LIMIT 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $login);
        $wiersz = mysqli_fetch_array($wykonaj);
        mysqli_close($polaczenie);
        if ($wiersz !== null) {
            return $wiersz['imie'] . ' ' . $wiersz['nazwisko'] . ';' . $wiersz['email'] . ';' . $wiersz['numer_kierownik'];
        }
        return null;
    }

    /**
     * Czas pracy zdalnej w danym dniu: od rozpoczęcia do zakończenia, bez przerwy
     * @param array $dane Wiersz z kolumnami data_rozpoczecia, data_zakonczenia, data_wstrzymania, data_wznowienia
     * @return array [godziny, minuty] - godziny ujemne oznaczają brak zakończenia pracy
     */
    function CzasPracyZdalnej(array $dane): array
    {
        $czas_pracy = date_create((string)$dane['data_zakonczenia'])->diff(date_create((string)$dane['data_rozpoczecia']));
        $czas_przerwy = date_create((string)$dane['data_wznowienia'])->diff(date_create((string)$dane['data_wstrzymania']));
        $godzina = (int)$czas_pracy->format('%h') - (int)$czas_przerwy->format('%h');
        $minuta = (int)$czas_pracy->format('%i') - (int)$czas_przerwy->format('%i');
        if ($minuta < 0) {
            $godzina--;
            $minuta = 60 + $minuta;
        }
        return [$godzina, $minuta];
    }

    /**
     * Zapamiętanie zapytania SQL w sesji, żeby formularz eksportu przesyłał tylko jego identyfikator
     * (wcześniej całe zapytanie szło w ukrytym polu formularza i serwer wykonywał to, co przyszło).
     * @param string $zapytanie Zapytanie SQL
     * @return string Identyfikator do ukrytego pola formularza
     */
    function ZapamietajZapytanie(string $zapytanie): string {
        $klucz = substr(hash('sha256', $zapytanie), 0, 32);
        $_SESSION['ewnioski_zapytania'][$klucz] = $zapytanie;
        // trzymamy tylko ostatnie zapytania
        if (count($_SESSION['ewnioski_zapytania']) > 50) {
            $_SESSION['ewnioski_zapytania'] = array_slice($_SESSION['ewnioski_zapytania'], -50, null, true);
        }
        return $klucz;
    }

    /**
     * Zapytanie zapamiętane przez ZapamietajZapytanie()
     * @param string|null $klucz Identyfikator z formularza
     */
    function PobierzZapytanie($klucz): string {
        $zapytanie = $_SESSION['ewnioski_zapytania'][(string)$klucz] ?? null;
        if ($zapytanie === null) {
            $this->Alert('Eksport niedostępny', 'Dane do eksportu wygasły. Odśwież raport i spróbuj ponownie.', 'error');
            exit;
        }
        return $zapytanie;
    }

    /**
     * Otwarcie pliku z przykładami (folder doc)
     */
    function OtwarciePliku()
    {
        $this->WyslijPlik(EWNIOSKI_ROOT . '/doc/' . basename((string)($_POST['przyklad'] ?? '')));
    }

    /**
     * Wysłanie pliku do pobrania
     * @param string $dir Pełna ścieżka do pliku
     */
    function OtwarcieTXT($dir)
    {
        $this->WyslijPlik($dir);
    }

    /**
     * Wysłanie pliku do przeglądarki jako załącznik
     * @param string $sciezka Pełna ścieżka do pliku
     */
    function WyslijPlik(string $sciezka): never
    {
        if (!is_file($sciezka)) {
            die('File Not Found');
        }
        if (headers_sent()) {
            die('Headers Sent');
        }
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        $ext = strtolower(pathinfo($sciezka, PATHINFO_EXTENSION));
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: " . (self::TYPY_PLIKOW[$ext] ?? 'application/octet-stream'));
        header("Content-Disposition: attachment; filename=\"" . basename($sciezka) . "\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($sciezka));
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        readfile($sciezka);
        exit;
    }

    /**
     * Przypomnienie hasła
     */
    function PrzypomnienieHasla() {
        $polaczenie = $this->MysqliConnect();
        $login = trim((string)($_POST['login_przypomnienie'] ?? ''));
        $email = trim((string)($_POST['email_przypomnienie'] ?? ''));
        $zapytanie = "SELECT email,numer_ewidencyjny, haslo_urlopy FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . $this->Esc($polaczenie, $login) . "'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $login);
        $wiersz = mysqli_fetch_array($wykonaj);
        mysqli_close($polaczenie);
        if ($wiersz and $wiersz['numer_ewidencyjny'] == $login and strtoupper((string)$wiersz['email']) == strtoupper($email)) {
            $this->WyslijHaslo($email, $wiersz['haslo_urlopy']);
            $this->Alert('Hasło zostało wysłane na maila: ' . htmlspecialchars($email) . '.', '', 'success');
        } elseif ($wiersz) {
            $this->Alert('Dla numeru ewidencyjnego ' . htmlspecialchars($login) . ' jest przypisany inny adres email.', 'Wprowadzony adres to: ' . htmlspecialchars($email), 'error');
        } else {
            $this->Alert('Nie znaleziono takiego numeru ewidencyjnego w bazie: ' . htmlspecialchars($login), '', 'error');
        }
    }

    function WyslijHaslo($email, $haslo)
    {
        $page = $this->TrescMaila("<h2>eWnioski przypomnienie hasła</h2><br/>
                                Mail został wygenerowany: " . date('Y-m-d H:i:s') . "<br/><br/>
                                Twoje hasło do eWniosków to: " . htmlspecialchars((string)$haslo) . "<br/>
                              <br/>");
        $this->WyslijMailHtml($email, 'eWnioski przypomnienie hasla', $page);
    }

    function BrakDanych($text) {
        if (strlen(trim((string)$text)) > 0) return $text;
        else return "<span class='text_red'><b>b.d.</b></span>";
    }

    /**
     * Arkusz XLSX z ustawionymi właściwościami, szerokościami kolumn i nagłówkami w pierwszym wierszu
     * @param string $tytul Tytuł dokumentu i nazwa arkusza
     * @param array $szerokosci Szerokości kolumn, np. ['A' => 20, 'B' => 10]
     * @param array $naglowki Nagłówki kolejnych kolumn od A
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    function NowyArkuszXlsx(string $tytul, array $szerokosci = [], array $naglowki = [])
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            die('Brak biblioteki PhpSpreadsheet. Uruchom "composer install" w katalogu programu.');
        }
        $arkusz = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $arkusz->getProperties()
            ->setCreator("PHP server")
            ->setLastModifiedBy("PHP server")
            ->setTitle($tytul)
            ->setSubject($tytul)
            ->setDescription($tytul);
        $arkusz->setActiveSheetIndex(0);
        $strona = $arkusz->getActiveSheet();
        foreach ($szerokosci as $kolumna => $szerokosc) {
            $strona->getColumnDimension(strtoupper($kolumna))->setWidth($szerokosc);
        }
        foreach (array_values($naglowki) as $i => $naglowek) {
            $strona->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1', $naglowek);
        }
        return $arkusz;
    }

    /**
     * Wypełnienie komórek wiersza kolorem
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $arkusz
     * @param int $wiersz Numer wiersza
     * @param string $odKolumny Pierwsza kolumna, np. 'A'
     * @param string $doKolumny Ostatnia kolumna, np. 'J'
     * @param string $kolor Kolor ARGB, np. 'FFFF0000'
     */
    function KolorujWierszXlsx($arkusz, int $wiersz, string $odKolumny, string $doKolumny, string $kolor): void
    {
        $arkusz->getActiveSheet()->getStyle($odKolumny . $wiersz . ':' . $doKolumny . $wiersz)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB($kolor);
    }

    /**
     * Wysłanie arkusza XLSX do przeglądarki i zakończenie skryptu
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $arkusz
     * @param string $nazwaPliku Nazwa pobieranego pliku
     * @param string|null $nazwaArkusza Nazwa zakładki
     */
    function WyslijArkuszXlsx($arkusz, string $nazwaPliku, ?string $nazwaArkusza = null): never
    {
        if ($nazwaArkusza !== null) {
            $arkusz->getActiveSheet()->setTitle($nazwaArkusza);
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nazwaPliku . '"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($arkusz);
        $writer->save('php://output');
        exit;
    }
}
