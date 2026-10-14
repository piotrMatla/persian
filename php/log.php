<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 14:25
 */

/**
 *  Plik odpowiedzialny za zalogowanie się do programu i odpowiednie przekierowanie
 */
require_once __DIR__ . '/bootstrap.php';

$a = new Common();
$h = new HTML();
$a->UstawieniaStandardowe();
$login = (string)($_POST['login'] ?? 'brak hasła');
$password = (string)($_POST['password'] ?? 'brak hasła');

$_SESSION['ewnioski_profil_wpisz_dane'] = 22;
$polaczenie = $a->MysqliConnect();
$loginSql = $a->Esc($polaczenie, $login);
$komunikat = '';
$tym = '0';
if (isset($_POST['utworz_haslo'])) {
    if ($_POST['utworz_haslo'] == ($_POST['powtorz_haslo'] ?? null)) {
        $zapytaniehaslo = "UPDATE ewidencja_szkolen.osoby SET haslo_urlopy='" . $a->Esc($polaczenie, $_POST['utworz_haslo']) . "', email='" . $a->Esc($polaczenie, $_POST['email'] ?? '') . "' where numer_ewidencyjny='" . $loginSql . "'";
        mysqli_query($polaczenie, $zapytaniehaslo);
        $a->BladZapytania($polaczenie, $zapytaniehaslo, $login);
        $a->ZapiszLog('Zapytanie;' . $zapytaniehaslo, $login);
        $password = $_POST['utworz_haslo'];
        $tym = '1';
    } else {
        $komunikat = "Hasła różnią się między sobą";
    }
}

$zapytanie = "SELECT  o.* , m.mail as mailkierownik, os.numer_ewidencyjny as numerkierownik,  m.mpk as mpkewnioski, m.kierownik, us.permisions, us.mpk, us.dir
FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id
LEFT JOIN ewidencja_szkolen.osoby os on m.mail=os.email
LEFT JOIN oddelegowania.users us on o.numer_ewidencyjny=us.numer_ewidencyjny
 WHERE o.numer_ewidencyjny='$loginSql' LIMIT 1";

$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, $login);

$wynik = mysqli_fetch_array($wykonaj);
if ($wynik === null) {
    $a->ZapiszLog('Brak użytkownika;', $login);
    header('Location: login.php?fail=2');
    exit;
}

if ($wynik['haslo_urlopy'] == '') {
    $h->PoczatekStronyUtworzenie($wynik['imie'], $wynik['nazwisko'], $login, $komunikat);
    $h->KoniecStronyLogowanie();
    exit;
}

if ($login != $wynik['numer_ewidencyjny'] or $password != $wynik['haslo_urlopy']) {
    $a->ZapiszLog('Błędne logowanie;', $login);
    header('Location: login.php?fail=1');
    exit;
} else if ($wynik['mpkewnioski'] == 'ZWOLNIONY') {
    $a->ZapiszLog('Konto wygasło;', $login);
    header('Location: login.php?fail=5');
    exit;
} else {
    session_regenerate_id(true);
    $a->ZapiszLog('Prawidłowe logowanie;', $login);
    $_SESSION['ewnioski_full_name'] = $wynik['imie'] . ' ' . $wynik['nazwisko'];
    $_SESSION['ewnioski_login'] = $login;
    $_SESSION['ewnioski_kierownik'] = $wynik['numerkierownik'];
    $_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
    $_SESSION['ewnioski_kierownik_imie'] = $wynik['kierownik'];
    $_SESSION['ewnioski_stanowisko'] = $wynik['typ_stanowiska'];
    $_SESSION['ewnioski_mpk'] = $wynik['mpkewnioski'];
    $_SESSION['ewnioski_mail'] = (string)$wynik['email'];
    $_SESSION['ewnioski_czas'] = date('H:i');
    $_SESSION['ewnioski_data'] = date('Y-m-d');
    $_SESSION['ewnioski_admin'] = $wynik['admin'];
    $_SESSION['ewnioski_limit_stron'] = 100;
    $_SESSION['ewnioski_widocznosc'] = (string)$wynik['widocznosc'];
    $_SESSION['inny_pracownik'] = $wynik['wnioski_dla_innych'];
    $_SESSION['praca_zdalna'] = $wynik['praca_zdalna'];
    $_SESSION['ewnioski_oddelegowania_permision'] = $wynik['permisions'];
    $_SESSION['ewnioski_profil_wpisz_dane'] = $wynik['profil'];
    $_SESSION['ewnioski_zmiana_profilu'] = $wynik['zmiana_profilu'];
    $_SESSION['ewnioski_profil'] = $wynik['profil'];
    $_SESSION['ewnioski_profil_mail_rcp'] = $wynik['mail_rcp'];
    $_SESSION['ewnioski_oddelegowania_mpk'] = $wynik['mpk'];
    $_SESSION['ewnioski_oddelegowania_dir'] = $wynik['dir'] ?? 'dir';

    if ($_SESSION['ewnioski_mail_kierownik'] == '') {
        $_SESSION['ewnioski_kierownik'] = '';
    }

    $dane = $a->SprawdzOsobe($_SESSION['ewnioski_login'], $login);
    if ($dane !== null) {
        $dane = explode(';', $dane);
        $_SESSION['ewnioski_kierownik'] = $dane[2];
        $_SESSION['ewnioski_mail_kierownik'] = $dane[1];
        $_SESSION['ewnioski_kierownik_imie'] = $dane[0];
    }
    header('Location: index.php?tym=' . $tym);
    exit;
}
