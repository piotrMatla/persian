<?php
/**
 * Okno edycji pozycji nadgodzin
 */
require_once __DIR__ . '/bootstrap.php';
$a = new Nadgodziny;
$h = new HTML;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();
$h->PoczatekStronyOkna('Edycja nadgodzin');
$a->FormularzPoprawyDanych($_GET['id'] ?? '');
if (isset($_POST['data_wykonywania_nadgodzin'])) {
    $ilosc_godzin_decimal = (int)($_POST['godziny_h'] ?? 0) + round((int)($_POST['godziny_m'] ?? 0) / 60, 4);
    $a->ZapiszZmienioneDane($_POST['id'], $ilosc_godzin_decimal, $_POST['data_wykonywania_nadgodzin']);
}
$h->KoniecStronyOkna();
