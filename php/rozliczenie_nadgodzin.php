<?php
function __autoload($x)
{
    require_once("klasy/$x.php");
}
$a = new Nadgodziny;
$e = new Excel;
$h = new HTML;
$a->UstawieniaStandardowe();
$a->UstawienieSortowaniaFiltrow();
$a->CzyZalogowany();
if(!isset($_SESSION['ewnioski_full_name'])) {
    session_destroy();
    header("Location: login.php");
}
if (isset($_POST['excel_export'])) $e->NadgodzinyExcelExportXLSX($_POST['excel_export']);
?>
<?php
$h->PoczatekStrony();
?>
    <tr>
        <td align="center" valign="middle" class="text font2 bottom" colspan="7">
            <h2>Rozliczenie nadgodzin</h2>
            <?php
            if (isset($_POST['kierownik_globalna_zmiana'])) $a->ZapiszGlobalnaModyfikacjeKierownik($_POST['zaznaczenie_ids'], $_POST['kierownik_globalna_zmiana']);
            if (isset($_POST['hr_globalna_zmiana'])) $a->ZapiszGlobalnaModyfikacjeHR($_POST['zaznaczenie_ids'], $_POST['hr_globalna_zmiana']);
            if (isset($_POST['delete_id'])) $a->KasowanieLinieNadgodzin($_POST['delete_id']);
            if (isset($_POST['nowy_rekord'])) $a->ZapiszNadgodziny();
            if (isset($_POST['anulowanie_id'])) $a->AnulowaniePozycji($_POST['anulowanie_id'], $_POST['anulowanie_full_name'], $_POST['anulowanie_notatka']);
            if (!isset($_POST['nowe_nadgodziny'])
                AND ($_SESSION['ewnioski_oddelegowania_permision'] == 'power_user'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'work_methods'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'administrator'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'kontroling'
                    OR $_SESSION['ewnioski_oddelegowania_permision'] == 'hr'
                    or $_SESSION['ewnioski_kierownik_imie']=='WANDA JAGIEŁA'
                    or $_SESSION['ewnioski_kierownik_imie']=='EWA KĘCKA'
                    or $_SESSION['ewnioski_kierownik_imie']=='MICHAŁ KITA'
                    )) {
                $a->GuzikPoleceniePracyWGodzinachNadliczbowych();
            }
            if (isset($_POST['nowe_nadgodziny'])) $a->FormularzWprowadzaniaGodzinNadliczbowych();
            if (!isset($_POST['nowe_nadgodziny'])) {
                $a->RaportNadgodziny();
            }
            ?>
        </td>
    </tr>
    <td align="center" valign="middle" class="low_menu_4 up font" colspan="7">
    </td>
    </tr>
</table>
<br/>
<?php
$a->StopkaJezyki();
?>
</body>
</html>