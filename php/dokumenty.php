<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 11.05.2023
 * Time: 11:06
 */
require_once __DIR__ . '/bootstrap.php';
$d = new Dokumenty;
$a = new Common;
$h = new HTML;

$f = new Filtrowanie;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();
if (isset($_POST['wyczysc_filtry'])) { // Usunięcie zmiennych sesyjnych z filtró
    $f->WyczyscFiltry();
}
if(isset($_POST['zalacznik']))
{
    $d->OtwarcieZalacznika($_POST['zalacznik']);
}
$f->UstawienieSortowaniaFiltrow();
$h->PoczatekStrony();
$d->PierwszyWierszTablica();
$d->DanePliki();
$h->KoniecStronyRaporty();