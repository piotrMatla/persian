<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 11:38
 */

/**
 * Ekran logowania i zmiany hasła
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/klasy');
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/".$class.".php")) require_once($path."/".$class.".php");
    }
});

$a = new Common;
$h = new HTML;
$Browser = new Browser;
$a->UstawieniaStandardowe();

$h->PoczatekStronyLogowanieDotyk();
if($Browser->getBrowser() == 'Internet Explorer' OR $Browser->getBrowser() == 'Edge') $h->ZlaPrzegladarka();
else if($Browser->getBrowser() == 'Firefox' AND $Browser->getVersion() <57) $h->StaraPrzegladarka($Browser->getVersion());

else{
    $h->FormularzLogowaniaDotyk();
}
if (isset($_GET['fail'])) {//Komunikat o nieprawidłowym loginie
    $h->NieprawidlowyLogin();
}
if (isset($_POST['zmiana_hasla'])) { //Formularz zmiany hasłasss
    $h->FormularzZmianyhasla();
}
if (isset($_POST['zmiana'])) {// Zmiana hasła
    $h->ZmianaHasla();
}
if (isset($_POST['przypomnienie_hasla'])) { //Formularz przypomnienia hasła
    $h->FormularzPrzypomnieniaHasla();
}
if(isset($_POST['przypomnij']))
{
    $h->PrzypomnienieHasla();
}
$h->KoniecStronyLogowanie();

