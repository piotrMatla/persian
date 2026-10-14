<?php
/**
 * Project name: eWnioski
 * Programmer: kduda
 * Create date: 29.09.2021
 * Create time: 10:40
 **/

/**
 * Podpowiadanie numerów MPK
 */
function __autoload($x)
{
    require_once("klasy/$x.php");
}

$a = new Common;

if (isset($_POST['lang'])) {
    $a->UstawJezyk();
}
$a->UstawieniaStandardowe();
$polaczenie = $a->MysqliConnect();
//Podpowiadanie głównego MPK-u z bazy
//Podpowiadanie końcówki do MPK-u głownego


$zapytanie = "SELECT id,nazwa_II   FROM godziny_nadliczbowe_przyczyny where nazwa_I='Koszt PKC'";
$tablica = array();
$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
$dane = mysqli_fetch_row($wykonaj);
echo '<option>Wybierz</option>';
while ($dane = mysqli_fetch_array($wykonaj)) {
    echo "<option value=" .str_replace(' ', '_',$dane[1]) . ">" . $dane[1] . "</option>";
}


mysqli_close($polaczenie);

