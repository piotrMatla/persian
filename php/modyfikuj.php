<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 10.12.2018
 * Time: 09:15
 */

/**
 * Formularze nowych wniosków i ich zapis
 */
require_once __DIR__ . '/bootstrap.php';

$a = new Common;
$h = new HTML;
$m = new Modyfikuj;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();

// Zapis danych - przed wyświetleniem strony, bo kończy się przekierowaniem
if (isset($_POST['rozpocznij_prace_zdalna'])) {
    $m->ZmianaPracaZdalna($_POST['rozpocznij_prace_zdalna']);
}
if (isset($_GET['wpisz'])) {
    $m->WpiszUrlop();//Zapis wniosku urlopowego
}
if (isset($_GET['wpiszzwolnienie'])) {
    $m->WpiszZwolnienie();//Zapis wniosku o zwolnienie, zmianę czasu lub pracę zdalną
}

$h->PoczatekStrony();
if (isset($_GET['urlop'])) {
    $m->FormularzUrlop();//Formularz wpisania wniosku urlopowego
}
if (isset($_GET['zwolnienie'])) {
    $m->FormularzZwolnienie();//Formularz zwolnienia z godzin / zmiany czasu
}
if (isset($_GET['zdalna'])) {
    $m->FormularzZdalna();//Formularz pracy zdalnej
}
if (isset($_GET['rozpocznij_zdalna'])) {
    $m->FormularzRozpocznijZdalna();//Rozpoczęcie i zakończenie pracy zdalnej
}

$h->KoniecStrony();
