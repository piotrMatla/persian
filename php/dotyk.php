<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 15:45
 */

/**
 * Główny plik programu
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/klasy');
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/".$class.".php")) require_once($path."/".$class.".php");
    }
});

$a = new Common;
$m = new Modyfikuj;
$r = new Raporty;
$h = new HTML;
$f = new Filtrowanie;
$a->UstawieniaStandardowe();

if(isset($_GET['wyloguj']))
{
    $a->WylogujDotyk();
}
$a->CzyZalogowanyDotyk();
if(!isset($_SESSION['ewnioski_sortowanie_dotyk_urlop']))
{
    $_SESSION['ewnioski_sortowanie_kierunek_dotyk_urlop']='ASC';
    $f->Sortowanie('ewnioski_sortowanie_dotyk_urlop', 'id','ewnioski_sortowanie_kierunek_dotyk_urlop');
}
if(isset($_GET['sort']) and isset($_GET['raport_urlopow']) )
{
    $f->Sortowanie('ewnioski_sortowanie_dotyk_urlop', $_GET['sort'],'ewnioski_sortowanie_kierunek_dotyk_urlop');
}
if(!isset($_SESSION['ewnioski_sortowanie_dotyk_zwolnienia']))
{
    $_SESSION['ewnioski_sortowanie_kierunek_dotyk_zwolnienia']='DESC';
    $f->Sortowanie('ewnioski_sortowanie_dotyk_zwolnienia', 'id','ewnioski_sortowanie_kierunek_dotyk_zwolnienia');
}
if(isset($_GET['sort'])  and isset($_GET['raport_zwolnien']))
{
    $f->Sortowanie('ewnioski_sortowanie_dotyk_zwolnienia', $_GET['sort'],'ewnioski_sortowanie_kierunek_dotyk_zwolnienia');
}
if(!isset($_SESSION['ewnioski_sortowanie_dotyk_stan']))
{
    $_SESSION['ewnioski_sortowanie_kierunek_dotyk_stan']='ASC';
    $f->Sortowanie('ewnioski_sortowanie_dotyk_stan', 'id','ewnioski_sortowanie_kierunek_dotyk_stan');
}
if(isset($_GET['sort']) and isset($_GET['stan_godzin']))
{
    $f->Sortowanie('ewnioski_sortowanie_dotyk_stan', $_GET['sort'],'ewnioski_sortowanie_kierunek_dotyk_stan');
}
if(isset($_POST['id_cofnij_kierownik_zwolnienie']))
{
    $r->CofnijZwolnienie();
}
if(isset($_POST['id_cofnij_kierownik']))
{
    $r->CofnijUrlop();
}
$h->PoczatekStronyDotyk();
?>
<tr>
        <td colspan="6" align="center"><?php
            if (isset($_SESSION['ewnioski_komunikat'])) {
                $a->WyswietlKomunikat();
            }
if(isset($_GET['dodaj_urlop']))
{
    $m->FormularzNowyWniosekDotyk();
}
else if(isset($_GET['dodaj_zwolnienie']))
{
    $m->FormularzZwolnienieDotyk();
}
else if(isset($_GET['raport_zwolnien']))
{
    $r->RaportZwolnienDotyk();
}
elseif (isset($_GET['raport_urlopow']))
{
    $r->RaportUrlopowDotyk();
}
elseif (isset($_GET['stan_godzin']))
{
    $r->RaportStanGodzinDotyk();
}
else{
    $h->LogoWDotyk();
}
            ?>
      <?php
$h->KoniecStronyDotyk();