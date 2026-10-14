<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 18.01.2019
 * Time: 12:01
 */
require_once dirname(__DIR__) . '/bootstrap.php';
$a = new Common;
$a->UstawieniaStandardowe();
$polaczenie=$a->MysqliConnect();
$zapytaniezastepstwo="SELECT numer_ewidencyjny FROM ewidencja_szkolen.osoby WHERE numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%'";
$wykonajzastepstwo = mysqli_query($polaczenie, $zapytaniezastepstwo);
$a->BladZapytania($polaczenie, $zapytaniezastepstwo, $_SESSION['ewnioski_login']);
$warunek=" numer_kierownik='".$_SESSION['ewnioski_login']."'";
while ($dane = mysqli_fetch_array($wykonajzastepstwo)) {
   $warunek.=" or numer_kierownik='".$dane[0]."'";
}
if($warunek!='')$warunek=" and (".$warunek." or numer_ewidencyjny='".$_SESSION['ewnioski_login']."')";

$zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly, uz,numer_kierownik, stan_urlopu_godziny
FROM stan_godzin
where numer_ewidencyjny='" . trim($_POST['dane']) . "' $warunek  order by id desc limit 1";
$wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
$a->BladZapytania($polaczenie, $zapytanieurlop,$_SESSION['ewnioski_login']);
if($dane=  mysqli_fetch_array($wykonaj))
{
    if($dane[3]===NULL)$dane[3]='Pracownik agencyjny';
    echo $dane[2].' '.$dane[1].';'.$dane[3].';'.$dane[4].';'.$dane[5].';'.$dane[6].';'.$dane[8];
}
else
{
    $zapytanieurlop = "SELECT o.imie, o.nazwisko
FROM stan_godzin s 
LEFT JOIN ewidencja_szkolen.osoby o on s.numer_kierownik= o.numer_ewidencyjny
where s.numer_ewidencyjny='" . trim($_POST['dane']) . "'  order by s.id desc limit 1";
    $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
    $a->BladZapytania($polaczenie, $zapytanieurlop,$_SESSION['ewnioski_login']);
    if($dane=  mysqli_fetch_array($wykonaj))
    {
       echo  'Ta osoba ma przypisanego następującego kierownika '.$dane[0].' '.$dane[1].'<br> A ty nie masz dostępu do stanu godzin tego kierownika;0;0;0;0;0';
    }
    else
    {
        echo 'Brak osoby z takim numerem;0;0;0;0;0';
    }

}
mysqli_close($polaczenie);

?>
