<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 24.01.2019
 * Time: 14:05
 */

/**
 * Przypomnienie przełożonym (i ich zastępcom) o wnioskach czekających na akceptację.
 * Uruchamiane z crona, wysyła maile od poniedziałku do piątku.
 */
require_once __DIR__ . '/bootstrap.php';

/**
 * Adresy przełożonego i jego zastępców (oddzielone przecinkami)
 * @param Common $a
 * @param mysqli $polaczenie
 * @param string $numerKierownika Numer ewidencyjny przełożonego
 */
function AdresaciPrzypomnienia(Common $a, $polaczenie, $numerKierownika): string
{
    $zapytaniemail = "SELECT email, mail_zastepcy,numer_zastepcy_stan_godzin  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . $a->Esc($polaczenie, $numerKierownika) . "' and email!='' order by id desc LIMIT 1 ";
    $wykonajmail = mysqli_query($polaczenie, $zapytaniemail);
    $a->BladZapytania($polaczenie, $zapytaniemail, 'Raport eWnioski');
    $danemail = mysqli_fetch_row($wykonajmail) ?? ['', '', ''];

    $zastepcy = [];
    foreach (explode(',', (string)$danemail[2]) as $numerZastepcy) {
        $zapytaniemailzastepca = "SELECT  email  FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . $a->Esc($polaczenie, $numerZastepcy) . "' and email!='' order by id desc LIMIT 1 ";
        $wykonajmailzastepca = mysqli_query($polaczenie, $zapytaniemailzastepca);
        $a->BladZapytania($polaczenie, $zapytaniemailzastepca, 'Raport eWnioski');
        $danemailzastepca = mysqli_fetch_row($wykonajmailzastepca);
        if ($danemailzastepca and $danemailzastepca[0] != '') $zastepcy[] = $danemailzastepca[0];
    }
    return implode(',', array_merge([(string)$danemail[0]], $zastepcy));
}

$dzien = date("N");
if ($dzien != '7' and $dzien != '6') {
    $a = new Common;
    $polaczenie = $a->MysqliConnect();
    $wnioski = [
        'ewnioski.wniosek' => 'wniosków urlopowych',
        'ewnioski.zwolnienie' => 'wniosków zwolnienia z godzin/ zmiany czasu pracy',
    ];
    foreach ($wnioski as $tabela => $opis) {
        $zapytanie = "SELECT   COUNT(id) as suma, dane_mpk
FROM $tabela w
WHERE id_status='1'
GROUP BY dane_mpk";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $a->BladZapytania($polaczenie, $zapytanie, 'Raport eWnioski');
        while ($wiersz = mysqli_fetch_array($wykonaj)) {
            $suma = $wiersz['suma'];
            $page = $a->TrescMaila("<h2>Masz do zaakceptowania następującą liczbę $opis: <br>$suma </h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . " > eWniosków</a>");
            $a->Wyslij(AdresaciPrzypomnienia($a, $polaczenie, $wiersz['dane_mpk']), 'Przypomnienie o akceptacji', $page);
        }
    }
    mysqli_close($polaczenie);
}
