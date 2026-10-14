<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 18.01.2019
 * Time: 12:01
 */
function __autoload($x)
{
    require_once("../klasy/$x.php");
}
$a = new Common;
$a->UstawieniaStandardowe();
$polaczenie=$a->MysqliConnect();

$zapytanie = "SELECT imie, nazwisko, numer_ewidencyjny
    FROM ewidencja_szkolen.osoby 
    WHERE numer_ewidencyjny='".trim($_POST['dane'])."'  LIMIT 1";
$wykonaj = mysqli_query($polaczenie, $zapytanie);
        $a->BladZapytania($polaczenie, $zapytanie,$_SESSION['ewnioski_login']);
if($dane=  mysqli_fetch_array($wykonaj))
{
    echo $dane[0].' '.$dane[1];
}
else
{
    echo 'Brak numeru w systemie';
}
mysqli_close($polaczenie);

?>
