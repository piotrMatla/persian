<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 13.02.2019
 * Time: 14:46
 */
/**
 * Zmiana widoczności kolumn w tabeli przez Ajax
 * @param string $x Lista kolumn do ukrycia
 */

function __autoload($x) {
    require_once("klasy/$x.php");
}

$a = new Common;
$a->UstawieniaStandardowe();
$polaczenie = $a->MysqliConnect();
$warunek = '';
$widocznosc = array();
$widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
//Zmiana widoczności kolumn
if (array_search($_POST['widocznosc'], $widocznosc) !== FALSE) {
    unset($widocznosc[array_search($_POST['widocznosc'], $widocznosc)]);
} else {
    array_push($widocznosc, $_POST['widocznosc']);
}
asort($widocznosc);
$warunek = implode(",", $widocznosc);
$zapytaniespr = "UPDATE  ewidencja_szkolen.osoby SET widocznosc='" . $warunek . "' where numer_ewidencyjny='" . trim($_SESSION['ewnioski_login']) . "' ";
$wykonaj = mysqli_query($polaczenie, $zapytaniespr);
$a->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
$a->ZapiszLog('Zapytanie;' . $zapytaniespr, $_SESSION['ewnioski_login']);
mysqli_close($polaczenie);
$_SESSION['ewnioski_widocznosc'] = $warunek;
?>
