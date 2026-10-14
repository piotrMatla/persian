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
$h = new HTML;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();

if(isset($_GET['wyloguj']))
{
    $a->Wyloguj();
}
$h->PoczatekStrony();
if(isset($_GET['tym']))
{
    if($_GET['tym']==1) $h->Instrukcja();
    else        $h->LogoW();

}
else{
$h->LogoW();
}

$h->KoniecStrony();