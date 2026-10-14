<?php
session_start();
require_once dirname(__DIR__) . '/php/bootstrap.php';
$a = new Common;
$a->UstawieniaStandardowe();
$_SESSION['ewnioski_profil_wpisz_dane']=22;
$polaczenie = $a->MysqliConnect();
$rcp_polaczenie = $a->SQLRCPPolaczenie();

$zapytanie_ostatnie_id = "SELECT id_rejestracji from odbicia_rcp.odbicia order by id_rejestracji desc LIMIT 1";
$wykonaj_ostatnie_id = mysqli_query($polaczenie, $zapytanie_ostatnie_id);
$a->BladZapytania($polaczenie, $zapytanie_ostatnie_id, 'Skrypt pobierz dane eWnioski');
$dane_ostatnie_id=mysqli_fetch_row($wykonaj_ostatnie_id);
$id_ostatnie_id=$dane_ostatnie_id[0];
if($id_ostatnie_id===NULL)
{
    $id_ostatnie_id=30068617;
}
$miesieacwstecz=date ("Y-m",mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"))).'-01';
$zapytanie_usun = "SELECT id_rejestracji  from odbicia_rcp.odbicia where data_odbicia<'$miesieacwstecz '";
$wykonaj_usun = mysqli_query($polaczenie, $zapytanie_usun);
$a->BladZapytania($polaczenie, $zapytanie_usun, 'Skrypt pobierz dane eWnioski');
$dane_usun=mysqli_fetch_row($wykonaj_usun);
$id_usun_stare_dane=$dane_usun[0];
if($id_usun_stare_dane!=NULL)
{
    $zapyanie_usun_stare="DELETE  from odbicia_rcp.odbicia where data_odbicia<'$miesieacwstecz '";
    $wykonaj_usun_stare = mysqli_query($polaczenie, $zapyanie_usun_stare);
    $a->BladZapytania($polaczenie, $zapyanie_usun_stare, 'Skrypt pobierz dane eWnioski');
    $a->ZapiszLog('Zapytanie_skrypt;' . $zapyanie_usun_stare,'Skrypt pobierz dane eWnioski');
}

// Pobranie ID RCP z odbicia_rcp.kolowrotek

$pobierz_id_kolowrotek = "select DISTINCT id_rcp from odbicia_rcp.kolowrotek;";
$wykonaj_kolowrotki = mysqli_query($polaczenie, $pobierz_id_kolowrotek);

$id_kolowrotkow = array();

while ($wiersz = $wykonaj_kolowrotki->fetch_assoc()) {
    $id_kolowrotkow[] = $wiersz['id_rcp'];
}

$wykonaj_kolowrotki->free();

$lista_id_kolowrotek = implode(",", $id_kolowrotkow);

$zapytanie_odbicia="SELECT TOP 10000
idRejestracji,
Data,
Wejscie,
Wyjscie,
[Numer identyfikatora],
[Nazwisko],
[Imie],
[Nazwa czytnika],
DeviceID
FROM [dbo].[RCP_RegistrationsView_Ewnioski] rv
where [Status weryfikacji] ='Ok'
/* 
and DeviceID IN (6,7,8,9,11,12,13,15,16,17,18,19,20,21,22,24,29,30,35,38,39,40,42,43,44,45,46,47,50,51,52,54,56,59,60,61,63,64,68,69,70,72,73,74,80,81,85,86,87,89) 
   */
and DeviceID IN ($lista_id_kolowrotek) 
and idRejestracji>$id_ostatnie_id 
  ORDER BY [idRejestracji] asc";
$tablica=array();
$wykonaj_odbicia = sqlsrv_query( $rcp_polaczenie, $zapytanie_odbicia, array(), array( "Scrollable" => 'static' ) ) ;
$a->BladZapytaniaMSSQL($rcp_polaczenie, $zapytanie_odbicia, 'Skrypt pobierz dane eWnioski');
$liczba_rekordow = sqlsrv_num_rows( $wykonaj_odbicia );

$zapytanie_kolowrotki = "SELECT id,nazwa  from odbicia_rcp.kolowrotek  order by id";
$wykonaj_kolowrotki = mysqli_query($polaczenie, $zapytanie_kolowrotki);
$a->BladZapytania($polaczenie, $zapytanie_kolowrotki, 'Skrypt pobierz dane eWnioski');
while ($dane_kolowrotki = mysqli_fetch_array($wykonaj_kolowrotki)) {
    $tablica[$dane_kolowrotki['nazwa']]=$dane_kolowrotki['id'];
}
while ($dane_odbicia = sqlsrv_fetch_array($wykonaj_odbicia)) {

if(isset($tablica[$dane_odbicia['Nazwa czytnika']]))
{
    $id_kolowrotka=$tablica[$dane_odbicia['Nazwa czytnika']];
}
else
{
    if(strpos($dane_odbicia['Nazwa czytnika'],'RCP')===false)$typ=2;
    else $typ=1;
    $zapytanie = "INSERT INTO odbicia_rcp.	kolowrotek (nazwa, typ, id_rcp)
           values ('".trim($dane_odbicia['Nazwa czytnika'])."','".$typ."' ,'".trim( $dane_odbicia['DeviceID'])."')";
    $wykonaj = mysqli_query($polaczenie, $zapytanie);
    $id_kolowrotka=mysqli_insert_id($polaczenie);
    $tablica[$dane_odbicia['Nazwa czytnika']]=$id_kolowrotka;
    $a->BladZapytania($polaczenie, $zapytanie, 'Skrypt pobierz dane eWnioski');
}
    if($dane_odbicia['Data']) $data=$dane_odbicia['Data']->format('Y-m-d H:i:s');
    else   $data='';
    if($dane_odbicia['Wejscie']) $wejscie=$dane_odbicia['Wejscie']->format('Y-m-d H:i:s');
    else $wejscie='';
    if($dane_odbicia['Wyjscie']) $wyjscie=$dane_odbicia['Wyjscie']->format('Y-m-d H:i:s');
    else $wyjscie='';
    $zapytanie_wpisz = "INSERT INTO odbicia_rcp.odbicia (id_rejestracji, data_odbicia, data_wejscia, data_wyjscia, numer_ewidencyjny,id_kolowrotka)
           values ('".trim($dane_odbicia['idRejestracji'])."','".trim($data)."' ,'".trim($wejscie)."','".trim($wyjscie)."'
           ,'".trim( $dane_odbicia['Numer identyfikatora'])."','".$id_kolowrotka."')";
    $wykonaj_wpisz = mysqli_query($polaczenie, $zapytanie_wpisz);
    $a->BladZapytania($polaczenie, $zapytanie_wpisz, 'Skrypt pobierz dane eWnioski');
}
