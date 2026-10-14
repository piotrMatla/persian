<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 14:25
 */

/**
 *  Logowanie przepustką na urządzeniach dotykowych
 */
require_once __DIR__ . '/bootstrap.php';

$a = new Common();
$h = new HTML();
$a->UstawieniaStandardowe();

$polaczenie = $a->MysqliConnect();
$_SESSION['ewnioski_profil_wpisz_dane'] = 22;
$rcp_polaczenie = $a->RCPPolaczenie();
$przepustka = trim((string)($_POST['przepystka'] ?? ''));
if ($przepustka == '') $przepustka = 'brak';
$zapytanie = "SELECT p.nazwisko, p.imie,p.numerIdentyfikatora, k.NumerFizyczny FROM [RCP-PKC].[dbo].[PracownikView] p
LEFT JOIN [RCP-PKC].[dbo].[RCP_Karty_PracownikaView] k on p.id=k.IdPrac
where k.NumerFizyczny LIKE'%" . str_replace("'", "''", $przepustka) . "'  and k.do is null";
$e1 = odbc_exec($rcp_polaczenie, $zapytanie) or die(odbc_errormsg($rcp_polaczenie));
$dane = odbc_fetch_array($e1, 1);
if ($dane === false) {
    $a->ZapiszLog('Brak użytkownika;', $przepustka);
    header('Location: login_dotyk.php?fail=3');
    exit;
}
$login = (string)$dane['numerIdentyfikatora'];

$zapytanie = "SELECT  o.* , m.mail as mailkierownik, os.numer_ewidencyjny as numerkierownik,  m.mpk, m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id LEFT JOIN ewidencja_szkolen.osoby os on m.mail=os.email WHERE o.numer_ewidencyjny='" . $a->Esc($polaczenie, $login) . "' LIMIT 1";

$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, $login);

$wynik = mysqli_fetch_array($wykonaj);
if ($wynik === null) {
    $a->ZapiszLog('Brak użytkownika;', $login);
    header('Location: login_dotyk.php?fail=4');
    exit;
}

session_regenerate_id(true);
$a->ZapiszLog('Prawidłowe logowanie;', $wynik['numer_ewidencyjny']);
$_SESSION['ewnioski_full_name'] = $wynik['imie'] . ' ' . $wynik['nazwisko'];
$_SESSION['ewnioski_login'] = $wynik['numer_ewidencyjny'];
$_SESSION['ewnioski_kierownik'] = $wynik['numerkierownik'];
$_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
$_SESSION['ewnioski_kierownik_imie'] = $wynik['kierownik'];
$_SESSION['ewnioski_stanowisko'] = $wynik['typ_stanowiska'];
$_SESSION['ewnioski_mpk'] = $wynik['mpk'];
$_SESSION['ewnioski_mail'] = (string)$wynik['email'];
$_SESSION['ewnioski_czas'] = date('H:i');
$_SESSION['ewnioski_data'] = date('Y-m-d');
$_SESSION['ewnioski_admin'] = $wynik['admin'];
$_SESSION['ewnioski_limit_stron'] = 100;
$_SESSION['inny_pracownik'] = 0;
$_SESSION['ewnioski_widocznosc'] = (string)$wynik['widocznosc'];
$_SESSION['ewnioski_profil_wpisz_dane'] = $wynik['profil'];
$_SESSION['ewnioski_zmiana_profilu'] = $wynik['zmiana_profilu'];
$_SESSION['ewnioski_profil'] = $wynik['profil'];
$_SESSION['ewnioski_profil_mail_rcp'] = $wynik['profil'];
if ($_SESSION['ewnioski_mail_kierownik'] == '') {
    $_SESSION['ewnioski_kierownik'] = '';
}
$dane = $a->SprawdzOsobe($_SESSION['ewnioski_login'], $wynik['numer_ewidencyjny']);
if ($dane !== null) {
    $dane = explode(';', $dane);
    $_SESSION['ewnioski_kierownik'] = $dane[2];
    $_SESSION['ewnioski_mail_kierownik'] = $dane[1];
    $_SESSION['ewnioski_kierownik_imie'] = $dane[0];
}

header('Location: dotyk.php?');
exit;
