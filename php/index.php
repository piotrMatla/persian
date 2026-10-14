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
require_once __DIR__ . '/bootstrap.php';

$a = new Common;
$h = new HTML;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();

if (isset($_GET['wyloguj'])) {
    $a->Wyloguj();
}
if (isset($_GET['tym']) && $_GET['tym'] == 1) {
    $h->PoczatekStrony('Instrukcja');
    $h->Instrukcja();
} else {
    $h->PoczatekStrony('Strona główna');
    $h->LogoW();
}

$h->KoniecStrony();
