<?php
/**
 * Okno edycji notatki do pozycji nadgodzin
 */
require_once __DIR__ . '/bootstrap.php';
$a = new Nadgodziny;
$h = new HTML;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();
$h->PoczatekStronyOkna('Edycja notatki');
$a->FormularzEdycjiNotatki(base64_decode((string)($_GET['id'] ?? '')), base64_decode((string)($_GET['kolumna'] ?? '')));
if (isset($_POST['id'])) $a->ZapiszNotatke($_POST['id'], $_POST['kolumna'], trim((string)$_POST['notatka']));
$h->KoniecStronyOkna();
