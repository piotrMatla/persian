<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 24.01.2019
 * Time: 14:05
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/klasy');
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/".$class.".php")) require_once($path."/".$class.".php");
    }
});

$dzien=date("N");
if($dzien!='7' and $dzien!='6')
{
$a = new Common;
$polaczenie=$a->MysqliConnect();

    $zapytanie = "SELECT   COUNT(id) as suma, dane_mpk
FROM ewnioski.wniosek w 
WHERE id_status='1'
GROUP BY dane_mpk";
    $wykonaj = mysqli_query($polaczenie, $zapytanie);
    $a->BladZapytania($polaczenie, $zapytanie, 'Raport eWnioski');
    if (mysqli_num_rows($wykonaj) > 0) {
        while ($wiersz = mysqli_fetch_array($wykonaj)) {
            $zastepca=',';
            $zapytaniemail="SELECT email, mail_zastepcy,numer_zastepcy_stan_godzin  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$wiersz['dane_mpk']."' and email!='' order by id desc LIMIT 1 ";

            $suma=$wiersz['suma'];
            $wykonajmail = mysqli_query($polaczenie, $zapytaniemail);
            $a->BladZapytania($polaczenie, $zapytaniemail, 'Raport eWnioski');
            $danemail=mysqli_fetch_row($wykonajmail);
$numery=explode(',',$danemail[2]);
            foreach ($numery as $wartosc )
            {
                $zapytaniemailzastepca="SELECT  email  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$wartosc."' and email!='' order by id desc LIMIT 1 ";
                $wykonajmailzastepca = mysqli_query($polaczenie, $zapytaniemailzastepca);
                $a->BladZapytania($polaczenie, $zapytaniemailzastepca, 'Raport eWnioski');
                $danemailzastepca=mysqli_fetch_row($wykonajmailzastepca);
                $zastepca=$zastepca.$danemailzastepca[0].',';
            }
           // if($danemail[1]!='' and $danemail[1]!=',')$zastepca=','.$danemail[1];
             if(strlen($zastepca)==2)$zastepca='';
            if(substr($zastepca,-1)==',')$zastepca=substr($zastepca,0,-1);
            $page="<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
        <h2>Masz do zaakceptowania następującą liczbę wniosków urlopowych: <br>$suma </h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/ > eWniosków</a>
         </center>
                                </body></html>
             
                                ";
            $a->Wyslij( $danemail[0].$zastepca,'Przypomnienie o akceptacji',$page);
        }
        }
$zapytanie = "SELECT   COUNT(id) as suma, dane_mpk
FROM ewnioski.zwolnienie w 
WHERE id_status='1'
GROUP BY dane_mpk";
$wykonaj = mysqli_query($polaczenie, $zapytanie);
$a->BladZapytania($polaczenie, $zapytanie, 'Raport eWnioski');
if (mysqli_num_rows($wykonaj) > 0) {
    while ($wiersz = mysqli_fetch_array($wykonaj)) {
        $zastepca='';
        $zapytaniemail="SELECT email, mail_zastepcy,numer_zastepcy_stan_godzin  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$wiersz['dane_mpk']."' and email!='' order by id desc LIMIT 1 ";
        $suma=$wiersz['suma'];
        $wykonajmail = mysqli_query($polaczenie, $zapytaniemail);
        $a->BladZapytania($polaczenie, $zapytaniemail, 'Raport eWnioski');
        $danemail=mysqli_fetch_row($wykonajmail);
        $numery=explode(',',$danemail[2]);
        foreach ($numery as $wartosc )
        {
            $zapytaniemailzastepca="SELECT  email  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$wartosc."' and email!='' order by id desc LIMIT 1 ";
            $wykonajmailzastepca = mysqli_query($polaczenie, $zapytaniemailzastepca);
            $a->BladZapytania($polaczenie, $zapytaniemailzastepca, 'Raport eWnioski');
            $danemailzastepca=mysqli_fetch_row($wykonajmailzastepca);
            $zastepca=$zastepca.$danemailzastepca[0].',';
        }

      //  if($danemail[1]!='' and $danemail[1]!=',')$zastepca=','.$danemail[1];
        if(strlen($zastepca)==2)$zastepca='';
        if(substr($zastepca,-1)==',')$zastepca=substr($zastepca,0,-1);
        $page="<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
        <h2>Masz do zaakceptowania następującą liczbę wniosków zwolnienia z godzin/ zmiany czasu pracy: <br>$suma </h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/ > eWniosków</a>
         </center>
                                </body></html>
             
                                ";
        if($zastepca!='')$zastepca=','.$zastepca;
       $a->Wyslij( $danemail[0].$zastepca,'Przypomnienie o akceptacji',$page);
    }
}
    mysqli_close($polaczenie);
}
