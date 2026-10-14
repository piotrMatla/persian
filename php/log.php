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
$_SESSION['ewnioski_profil_wpisz_dane']=22;
$polaczenie = $a->MysqliConnect();
$komunikat='';
$tym='0';
if(isset($_POST['utworz_haslo'])) {
    if ($_POST['utworz_haslo'] == $_POST['powtorz_haslo']) {
    $zapytaniehaslo = "UPDATE ewidencja_szkolen.osoby SET haslo_urlopy='" . $_POST['utworz_haslo'] . "', email='" . $_POST['email'] . "' where numer_ewidencyjny='".$_POST['login']."'";
        $wykonaj = mysqli_query($polaczenie, $zapytaniehaslo);
    $a->BladZapytania($polaczenie, $zapytaniehaslo, $login);
    $a->ZapiszLog('Zapytanie;' . $zapytaniehaslo, $login);
        $password=$_POST['utworz_haslo'];
        $tym='1';
}
else{
    $komunikat= "Hasłą różnią sie między sobą";
}
}

$zapytanie = "SELECT  o.* , m.mail as mailkierownik, os.numer_ewidencyjny as numerkierownik,  m.mpk as mpkewnioski, m.kierownik, us.permisions, us.mpk, us.dir  
FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id 
LEFT JOIN ewidencja_szkolen.osoby os on m.mail=os.email
LEFT JOIN oddelegowania.users us on o.numer_ewidencyjny=us.numer_ewidencyjny
 WHERE o.numer_ewidencyjny='$login' LIMIT 1";

$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, $login);

$wynik = mysqli_fetch_array($wykonaj);
if($wynik=== null)
{
    $a->ZapiszLog('Brak użytkownika;' , $login);
    header('Location: login.php?fail=2');
    exit;
}

$password=$password;
if($wynik['haslo_urlopy']=='')
{
    $h->PoczatekStronyUtworzenie($wynik['imie'],$wynik['nazwisko'], $login,$komunikat);
    $h->KoniecStronyLogowanie();
    exit;
}

if ($login != $wynik['numer_ewidencyjny'] or $password != $wynik['haslo_urlopy']) {
    $a->ZapiszLog('Błędne logowanie;' , $login.'-'.$password);
    header('Location: login.php?fail=1');
    exit;
}
else if($wynik['mpkewnioski']=='ZWOLNIONY')
{
    $a->ZapiszLog('Konto wygasło;' , $login.'-'.$password);
    header('Location: login.php?fail=5');
    exit;
}
else {
    $czas=  date('H:i');
    $data= date('Y-m-d');
    $a->ZapiszLog('Prawidłowe logowanie;' , $login);
    $_SESSION['ewnioski_full_name'] = $wynik['imie'].' '.$wynik['nazwisko'];
    $_SESSION['ewnioski_login'] = $login;
    $_SESSION['ewnioski_kierownik'] = $wynik['numerkierownik'];
    $_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
    $_SESSION['ewnioski_kierownik_imie'] = $wynik['kierownik'];
    $_SESSION['ewnioski_stanowisko'] = $wynik['typ_stanowiska'];
    $_SESSION['ewnioski_mpk'] = $wynik['mpkewnioski'];
    $_SESSION['ewnioski_mail'] = $wynik['email'];
    $_SESSION['ewnioski_mail_kierownik'] = $wynik['mailkierownik'];
    $_SESSION['ewnioski_czas'] = $czas;
    $_SESSION['ewnioski_data'] = $data;
    $_SESSION['ewnioski_admin'] = $wynik['admin'];
    $_SESSION['ewnioski_limit_stron']=100;
    $_SESSION['inny_pracownik']=0;
    $_SESSION['ewnioski_widocznosc'] = $wynik['widocznosc'];
    $_SESSION['inny_pracownik']= $wynik['wnioski_dla_innych'];
    $_SESSION['praca_zdalna']= $wynik['praca_zdalna'];
    $_SESSION['ewnioski_oddelegowania_permision']= $wynik['permisions'];
    $_SESSION['ewnioski_profil_wpisz_dane']= $wynik['profil'];
    $_SESSION['ewnioski_zmiana_profilu']= $wynik['zmiana_profilu'];
    $_SESSION['ewnioski_profil']= $wynik['profil'];
    $_SESSION['ewnioski_profil_mail_rcp']= $wynik['mail_rcp'];
     $_SESSION['ewnioski_oddelegowania_mpk']= $wynik['mpk'];
    if( $wynik['dir']===null)$wynik['dir']='dir';
    $_SESSION['ewnioski_oddelegowania_dir']= $wynik['dir'];


    if($_SESSION['ewnioski_mail_kierownik']=='')
    {
        $_SESSION['ewnioski_kierownik']='';
    }

/*$dane=$a->SprawdzMPK($_SESSION['ewnioski_mpk'], $login);
    if($dane!==null)
    {
        $dane=explode(';',$dane);
        $_SESSION['ewnioski_mail_kierownik']=$dane[2];
        $_SESSION['ewnioski_kierownik']=$dane[3];
    }*/


    $dane=$a->SprawdzOsobe($_SESSION['ewnioski_login'], $login);
    if($dane!==null)
    {
        $dane=explode(';',$dane);
        $_SESSION['ewnioski_kierownik']=$dane[2];
        $_SESSION['ewnioski_mail_kierownik']=$dane[1];
        $_SESSION['ewnioski_kierownik_imie'] = $dane[0];
       // $_SESSION['inny_pracownik']=$dane[3];s
    }
     header('Location: index.php?tym='.$tym);
    exit;

}
echo "Wyjątek";

?>