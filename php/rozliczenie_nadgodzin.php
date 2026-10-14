<?php
require_once __DIR__ . '/bootstrap.php';
$a = new Nadgodziny;
$e = new Excel;
$h = new HTML;
$a->UstawieniaStandardowe();
$a->UstawienieSortowaniaFiltrow();
$a->CzyZalogowany();
if (!isset($_SESSION['ewnioski_full_name'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
if (isset($_POST['excel_export'])) $e->NadgodzinyExcelExportXLSX($a->PobierzZapytanie($_POST['excel_export']));

/** Uprawnienia z programu Oddelegowania, które pozwalają wystawić polecenie pracy w nadgodzinach */
$uprawnieniaPolecenia = ['power_user', 'kierownik', 'work_methods', 'prod_manager', 'master_admin', 'administrator', 'kontroling', 'hr'];
/** Osoby, których podwładni mogą wystawić polecenie bez tych uprawnień */
$przelozeniUprawnieni = ['WANDA JAGIEŁA', 'EWA KĘCKA', 'MICHAŁ KITA'];

$h->PoczatekStrony('Rozliczenie nadgodzin');
if (isset($_POST['kierownik_globalna_zmiana'])) $a->ZapiszGlobalnaModyfikacjeKierownik($_POST['zaznaczenie_ids'], $_POST['kierownik_globalna_zmiana']);
if (isset($_POST['hr_globalna_zmiana'])) $a->ZapiszGlobalnaModyfikacjeHR($_POST['zaznaczenie_ids'], $_POST['hr_globalna_zmiana']);
if (isset($_POST['delete_id'])) $a->KasowanieLinieNadgodzin($_POST['delete_id']);
if (isset($_POST['nowy_rekord'])) $a->ZapiszNadgodziny();
if (isset($_POST['anulowanie_id'])) $a->AnulowaniePozycji($_POST['anulowanie_id'], $_POST['anulowanie_full_name'], $_POST['anulowanie_notatka']);
if (!isset($_POST['nowe_nadgodziny'])
    and (in_array($_SESSION['ewnioski_oddelegowania_permision'] ?? null, $uprawnieniaPolecenia, true)
        or in_array($_SESSION['ewnioski_kierownik_imie'] ?? null, $przelozeniUprawnieni, true))) {
    $a->GuzikPoleceniePracyWGodzinachNadliczbowych();
}
if (isset($_POST['nowe_nadgodziny'])) $a->FormularzWprowadzaniaGodzinNadliczbowych();
if (!isset($_POST['nowe_nadgodziny'])) {
    $a->RaportNadgodziny();
}
$h->KoniecStrony();
