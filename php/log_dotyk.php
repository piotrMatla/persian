<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 14:25
 */

/**
 *  Plik odpowiedzialny za zalogowanie się do programu i odpowiednie przekierowanie
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/klasy');
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/".$class.".php")) require_once($path."/".$class.".php");
    }
});

$a = new Common();
$h = new HTML();
$a->UstawieniaStandardowe();
if (!isset($_POST['login'])) {
    $login = 'brak hasła';
} else {
    $login = $_POST['login'];
}

if (!isset($_POST['password'])) {
    $password = 'brak hasła';
} else {
    $password = $_POST['password'];
}
$polaczenie = $a->MysqliConnect();
$komunikat='';
$_SESSION['ewnioski_profil_wpisz_dane']=22;
$rcp_polaczenie = $a->RCPPolaczenie();
if($_POST['przepystka']=='')$_POST['przepystka']='brak';
$zapytanie = "SELECT p.nazwisko, p.imie,p.numerIdentyfikatora, k.NumerFizyczny FROM [RCP-PKC].[dbo].[PracownikView] p
LEFT JOIN [RCP-PKC].[dbo].[RCP_Karty_PracownikaView] k on p.id=k.IdPrac
where k.NumerFizyczny LIKE'%".trim($_POST['przepystka'])."'  and k.do is null";
$e1 = odbc_exec($rcp_polaczenie, $zapytanie) OR DIE(odbc_error());
$dane = odbc_fetch_array($e1,1);
$login=$dane['numerIdentyfikatora'];
if($dane=== false)
{
    $a->ZapiszLog('Brak użytkownika;' , $login);
    header('Location: login_dotyk.php?fail=3');
    exit;
}


$zapytanie = "SELECT  o.* , m.mail as mailkierownik, os.numer_ewidencyjny as numerkierownik,  m.mpk, m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id LEFT JOIN ewidencja_szkolen.osoby os on m.mail=os.email WHERE o.numer_ewidencyjny='$login' LIMIT 1";

$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, $login);

$wynik = mysqli_fetch_array($wykonaj);
if($wynik=== null)
{
    $a->ZapiszLog('Brak użytkownika;' , $login);
    header('Location: login_dotyk.php?fail=4');
    exit;
}

    $czas=  date('H:i');
    $data= date('Y-m-d');
    $a->ZapiszLog('Prawidłowe logowanie;' , $wynik['numer_ewidencyjny']);
    $_SESSION['ewnioski_full_name'] = $wynik['imie'].' '.$wynik['nazwisko'];
    $_SESSION['ewnioski_login'] = $wynik['numer_ewidencyjny'];
    $_SESSION['ewnioski_kierownik'] = $wynik['numerkierownik'];
    $_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
    $_SESSION['ewnioski_kierownik_imie'] = $wynik['kierownik'];
    $_SESSION['ewnioski_stanowisko'] = $wynik['typ_stanowiska'];
    $_SESSION['ewnioski_mpk'] = $wynik['mpk'];
    $_SESSION['ewnioski_mail'] = $wynik['email'];
    $_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
    $_SESSION['ewnioski_czas'] = $czas;
    $_SESSION['ewnioski_data'] = $data;
    $_SESSION['ewnioski_admin'] = $wynik['admin'];
    $_SESSION['ewnioski_limit_stron']=100;
    $_SESSION['inny_pracownik']=0;
    $_SESSION['ewnioski_widocznosc'] = $wynik['widocznosc'];
$_SESSION['ewnioski_profil_wpisz_dane']= $wynik['profil'];
$_SESSION['ewnioski_zmiana_profilu']= $wynik['zmiana_profilu'];
$_SESSION['ewnioski_profil']= $wynik['profil'];
$_SESSION['ewnioski_profil_mail_rcp']= $wynik['profil'];
    if($_SESSION['ewnioski_mail_kierownik']=='')
    {
        $_SESSION['ewnioski_kierownik']='';
    }
  /*  $dane=$a->SprawdzMPK($_SESSION['ewnioski_mpk'], $wynik['numer_ewidencyjny']);
    if($dane!==null)
    {
        $dane=explode(';',$dane);
        $_SESSION['ewnioski_mail_kierownik']=$dane[2];
        $_SESSION['ewnioski_kierownik']=$dane[3];
        $_SESSION['inny_pracownik']=$dane[1];
        $_SESSION['ewnioski_kierownik_imie'] = $dane[0];
    }*/
    $dane=$a->SprawdzOsobe($_SESSION['ewnioski_login'], $wynik['numer_ewidencyjny']);
    if($dane!==null)
    {
        $dane=explode(';',$dane);
        $_SESSION['ewnioski_kierownik']=$dane[2];
        $_SESSION['ewnioski_mail_kierownik']=$dane[1];
      //j  $_SESSION['inny_pracownik']=$dane[3];
        $_SESSION['ewnioski_kierownik_imie'] = $dane[0];
    }

    header('Location: dotyk.php?');
    exit;


echo "Wyjątek";

?>