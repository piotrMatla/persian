<?php

function __autoload($x)
{
    require_once("../klasy/$x.php");
}
$a = new Nadgodziny;
$array = array();
$a->UstawieniaStandardowe();
$polaczenie = $a->MysqliConnect();

$zapytanie = "SELECT * FROM ewidencja_szkolen.osoby os LEFT JOIN ewidencja_szkolen.mpk mp ON(mp.id=os.id_mpk) WHERE  mp.mpk IS not null AND ( /* os.imie like '%".$_GET['term']."%' OR os.nazwisko like '%".$_GET['term']."%' OR */ os.numer_ewidencyjny like '%".$_GET['term']."%')  LIMIT 20";
$wykonaj = mysqli_query($polaczenie, $zapytanie) ;
$a->BladZapytania($polaczenie, $zapytanie, 'Autocomplete pracownicy');
if(mysqli_num_rows($wykonaj) > 0)
{
    while($dane = mysqli_fetch_array($wykonaj))
    {
        $array[] = $dane['imie']."---".$dane['nazwisko']."---".$dane['numer_ewidencyjny']."---".$dane['mpk'];
    }
}
else
{
    $array[] = "Brak wyników podaj numer ewidencyjny";
}
echo json_encode($array);
?>
