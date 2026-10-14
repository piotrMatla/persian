<?php
function __autoload($x)
{
    require_once("../php/klasy/$x.php");
}
$a = new Nadgodziny();
$a->UstawieniaStandardowe();
$polaczenie = $a->MysqliConnect();

// --- uwzględnienie typu wpisu w sprawdzeniu duplikatu ---
// Dwa różne typy wpisu (nadgodziny / odpracowanie_godzin) dla tej samej
// osoby i tego samego dnia NIE są traktowane jako duplikat - tylko
// dwa wpisy tego samego typu na ten sam dzień są konfliktem.
if (!isset($_POST['typ_wpisu'])) $_POST['typ_wpisu'] = '';

$zapytanie = "SELECT * FROM godziny_nadliczbowe_godziny go WHERE go.numer_ewidencyjny='".$_POST['numer_ewidencyjny']."' AND go.data_wykonywania_nadgodzin='".$_POST['data_wykonania_nadgodzin']."' AND go.typ_wpisu='".$_POST['typ_wpisu']."'";

$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, 'SprawdzenieCzyOsobaJestJuzUzupelniona');
if(mysqli_num_rows($wykonaj) > 0) echo "true";
else echo "false";