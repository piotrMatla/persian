<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 18.12.2018
 * Time: 11:06
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__);
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/klasy/".$class.".php")) require_once($path."/klasy/".$class.".php");
    }
});
$r = new Raporty;
$a = new Common;
$h = new HTML;

$f = new Filtrowanie;
$p = new PDF;
$e = new Excel;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();
if (isset($_POST['wyczysc_filtry'])) { // Usunięcie zmiennych sesyjnych z filtrów
    $f->WyczyscFiltry();
}
$f->UstawienieSortowaniaFiltrow();
if (isset($_POST['przyklad'])) {
    $r->OtwarciePliku($_POST['przyklad']);//Otwarcie pliku z przykładami
    exit;
}
/*if(!isset($_SESSION['ewnioski_sortowanie']))
{
    $_SESSION['ewnioski_sortowanie_kierunek']='ASC';
    $f->Sortowanie('ewnioski_sortowanie', 'id','ewnioski_sortowanie_kierunek');
}*/

if(isset($_POST['id_do_zmiany_praca_zdalna'])) $r->ModyfikujPracaZdalnaHR();
if(isset($_POST['usun_praca_zdalna']))
{
    $r->UsunPracZdalna();
}
if(isset($_GET['sort']))
{
    $f->Sortowanie('ewnioski_sortowanie_'.$_GET['raport'], $_GET['sort'],'ewnioski_sortowanie_kierunek');
}
if(isset($_POST['limit_stron']))
{
    $_SESSION['ewnioski_limit_stron']=$_POST['limit_stron'];
}
if(isset($_POST['limit_stron']))
{
    $_SESSION['ewnioski_limit_stron']=$_POST['limit_stron'];
}
if(isset($_POST['wydruk_zwolnienie']))
{
    $p->ZwolnieniePDF();
}
if(isset($_POST['wydruk_urlop']))
{
    $p->UrlopPDF();
}
if(isset($_POST['akcptuj_id_ewniosek']))
{
    $r->ZaakceptujUrlop();
}
if(isset($_POST['akcptuj_id_zwolnienie']))
{
    $r->ZaakceptujZwolnienie();
}
if(isset($_POST['id_global_kierownik']))
{
    $r->ZaakceptujUrlopGlobalnie();
}
if(isset($_POST['id_global_hr']))
{
    $r->ZaakceptujUrlopGlobalnieHR();
}
if(isset($_POST['id_global_kierownik_zwolnienie']))
{
    $r->ZaakceptujZwolnienieGlobalnie();
}
if(isset($_POST['id_global_hr_zwolnienie']))
{
    $r->ZaakceptujZwolnienieGlobalnieHR();
}
if(isset($_POST['zapytanie_excel']))
{
    $e->EksportSaturnXLSX();
}
if(isset($_POST['zapytanie_excel_stan_godzin']))
{
    $e->EksportStanGodzin();
}

if(isset($_POST['zapytanie_excel2']))
{
    $e->EksportSaturnXLSXWniosekWRCP();
}
if(isset($_POST['zapytanie_excel2_enova']))
{
    $e->EksportEnovaXLSXWniosekWRCP();
}
if(isset($_POST['zapytanie_excel_zwolnienia']))
{
    $e->EksportZwolnieniaXLSX();
}
if(isset($_POST['zapytanie_excel_widok']))
{
    $e->EksportZwolnieniaWidokXLSX();
}
if(isset($_POST['zapytanie_excel_praca_zdalna_czas_pracy']))
{
    $e->EksportPracaZdalnaCzasPracyXLSX();
}
if(isset($_POST['zapytanie_excel_praca_zdalna']))
{
    $e->EksportPracaZdalnaAkcjeXLSX();
}
if(isset($_POST['id_cofnij_kierownik']))
{
    $r->CofnijUrlop();
}
if(isset($_POST['id_cofnij_kierownik_anuluj']))
{
    $r->AnulujUrlop();
}
if(isset($_POST['id_cofnij_kierownik_zwolnienie']))
{
    $r->CofnijZwolnienie();
}
if(isset($_POST['id_cofnij_kierownik_zwolnienie_anuluj']))
{
    $r->AnulujZwolnienie();
}
if(isset($_POST['id_cofnij_kierownik_zwolnienie_anuluj']))
{
    $r->AnulujZwolnienie();
}
if(isset($_POST['mpk_wpisz']))
{
    $r->WpiszMPK();
}
if(isset($_POST['usun_mpk']))
{
    $r->UsunMPK();
}
if(isset($_POST['numer_wpisz'])) {
    if ($_POST['id_edytuj']) {
        $r->EdytujOsoba();
    } else {
    $r->WpiszOsoba();
}
}
if(isset($_POST['usun_osoba']))
{
    $r->UsunOsoba();
}
if(isset($_POST['usun_data']))
{
    $r->UsunDaneZDnia();
}
if(isset($_POST['osoba_wpisz']))
{
    $r->AktualizujZaastepstwoStanGodzin();
}
if(isset($_POST['id_mail']))
{
    $r->ZmianaKierownikaMail($_POST['mail_osoba'],$_POST['mail_numer'],$_POST['mail_kierownik']);
}
if(isset($_POST['numer_id_pisanie_dla_inncyh']))
{
    $r->ModyfikujPisanieDlaInncyh();
}
if(isset($_POST['numer_id_praca_zdalna']))
{
    $r->ModyfikujPracaZdalna();
}
if(isset($_POST['usun_zastepstwo_numer']))
{
    $r->UsunZastepstwo();
}
if(isset($_POST['dodaj_praca_zdalna']))
{
    $r->DodajPracaZdalna();
}
if(isset($_POST['excel_export_wejscia']))
{
    $e->RaportWejscXLSX($_POST['excel_export_wejscia']);
    exit;
}if(isset($_POST['zapytanie_excel_widoczne']))
{
    $e->RaportUrlopyWidoczneXLSX($_POST['zapytanie_excel_widoczne']);
    exit;
}
$h->PoczatekStrony();
    ?><tr>
        <td colspan="7" align="center"><?php
             if(isset($_GET['wejscia'])) {
                $r->PokazMenuSzukaniaDanychWejscia();
                 $r->RaportWejscLokalnie();//Raport wejść z systemu RCP
            }
            else if(isset($_GET['odbicia'])) {
                $r->PokazMenuSzukaniaDanychWejscia();
                $r->RaportOdbic();//Raport wejść z systemu RCP
            }
            else {
                $r->PokazMenuSzukaniaDanych();
            }
            if (isset($_SESSION['ewnioski_komunikat'])) {
                $a->WyswietlKomunikat();
            }
    if(isset($_GET['urlop'])) {
                $r->RaportUrlopow();//Raport urlopów wpisanych przez użytkownika
            }
            if(isset($_GET['stan_urlopu'])) {
                $r->RaportStanGodzin();//Raportstanu nadgodzin i urlopu
            }
            if(isset($_GET['zwolnienia'])) {
                $r->RaportZwolnien();//Raport urlopów wpisanych przez użytkownika
            }
            if(isset($_GET['mpk'])) {
                $r->RaportMPK();//Raport MPK-ów z wyjaątkami akceptacji urlopów
            }
            if(isset($_GET['osoby'])) {
                $r->RaportOsoby();//Raport osób z wyjaątkami akceptacji urlopów
            }
            if(isset($_GET['wszystkie'])) {
                $r->RaportOsobyWszystkie();//Raportwszystkich osób w ewnioskach
            }
            if(isset($_GET['zastepstwo']))
            {
                $r->FormularzZastepcy();
                if(isset($_POST['zastepca_1_wpsiz']))
                {
                    $r->AktualizujZaastepstwo();
                }
            }
            if(isset($_GET['nowa_osoba']))
            {
                $r->FormularzZastepcyStanGodzin();
            }
            if(isset($_GET['wczytaj_urlop'])) {
                if (!isset($_POST['opisexcel'])) {
                    $r->FormularzWczytaniaUrlopu();// Formularz wczytania liczby dniu urlopu
                } else {
                    $r->WeryfikacjaExcel(); // Wstępna weryfikacja danych do opisu faktury z pliku excel
                }
            }
            if(isset($_GET['wpisz_urlop'])) {
                $r->ZaciagnijDane();
            }
            if(isset($_GET['sprawdz_zastepce']))
            {
                $r->SprawdzZastepceHR();
                $r->PisanieWnioskowDlaInnych();
                $r->ZastepcaHR();
            }
            if(isset($_GET['praca_zdalna'])) {
                if(isset($_POST['modyfikacja_praca_zdalna_data_rozpoczecia']))
                {
                    $r->FormualrzEducjiPracyZdalne();
                }
                else if(isset($_GET['dodaj_praca_zdalna']))
                {
                    $r->FormualrzDodajPracaZdalna();
                }
                $r->RaportPracaZdalna();//Raportstanu Pracy Zdalnej
            }
            if(isset($_GET['praca_zdalna_czas_pracy'])) {

                $r->RaportPracaZdalnaCzasPracy();//Raportstanu Pracy Zdalnej
            }
    ?>
        </td>
</tr><?php
$h->KoniecStronyRaporty();