<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 11:38
 */

/**
 * Ekran logowania na urządzeniach dotykowych
 */
require_once __DIR__ . '/bootstrap.php';

$a = new Common;
$h = new HTML;
$Browser = new Browser;
$a->UstawieniaStandardowe();

$h->PoczatekStronyLogowanieDotyk();
if (isset($_GET['fail'])) {//Komunikat o nieprawidłowym loginie
    $h->NieprawidlowyLogin();
}
if ($Browser->getBrowser() == 'Internet Explorer' or $Browser->getBrowser() == 'Edge') $h->ZlaPrzegladarka();
else if ($Browser->getBrowser() == 'Firefox' and (float)$Browser->getVersion() < 57) $h->StaraPrzegladarka($Browser->getVersion());
else {
    $h->FormularzLogowaniaDotyk();
}
if (isset($_POST['zmiana_hasla'])) { //Formularz zmiany hasła
    $h->FormularzZmianyhasla();
}
if (isset($_POST['zmiana'])) {// Zmiana hasła
    $h->ZmianaHasla();
}
if (isset($_POST['przypomnienie_hasla'])) { //Formularz przypomnienia hasła
    $h->FormularzPrzypomnieniaHasla();
}
if (isset($_POST['przypomnij'])) {
    $h->PrzypomnienieHasla();
}
$h->KoniecStronyLogowanie();
