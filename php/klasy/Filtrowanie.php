<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 11:44
 */

/**
 * Class Filtrowanie Klasa z funkcjami odpowiedzialnymi za sortowanie
 */
require_once("/home/lora/public_html/PolaczenieWise/Polaczenie.php");
class Filtrowanie extends Polaczenie
{
    /**
     * Ustawienei zmienej odnośnie sortowania
     */
    function UstawienieSortowaniaFiltrow()
    {
        $pierwszy=date ("Y-m",mktime(0, 0, 0, date("m")+1, date("d"),   date("Y"))).'-01';
        $pierwszy= date('Y-m-d', strtotime('-1 day', strtotime($pierwszy)));
        if(!isset($_SESSION['ewnioski_sortowanie_kierunek']))$_SESSION['ewnioski_sortowanie_kierunek']='ASC';
        if(!isset($_SESSION['ewnioski_sortowanie_historia']))$_SESSION['ewnioski_sortowanie_historia']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_urlopy']))$_SESSION['ewnioski_sortowanie_urlopy']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_godziny']))$_SESSION['ewnioski_sortowanie_godziny']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_zwolnienia']))$_SESSION['ewnioski_sortowanie_zwolnienia']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_osoby']))$_SESSION['ewnioski_sortowanie_osoby']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_praca_zdalna']))$_SESSION['ewnioski_sortowanie_praca_zdalna']='id';
        if(!isset($_SESSION['ewnioski_sortowanie_wejscia']))$_SESSION['ewnioski_sortowanie_wejscia']='data_odbicia';
        if(!isset($_SESSION['ewnioski_sortowanie_sprawdz_zastepce']))$_SESSION['ewnioski_sortowanie_sprawdz_zastepce']='sg.numer_ewidencyjny';
        if(!isset($_SESSION['ewnioski_sortowanie_odbicia']))$_SESSION['ewnioski_sortowanie_odbicia']='data_odbicia';
        if(!isset($_SESSION['ewnioski_sg_niedopracowania'])) $_SESSION['ewnioski_sg_niedopracowania'] = '';
        if(isset($_POST['ewnioski_sg_niedopracowania'])) $_SESSION['ewnioski_sg_niedopracowania'] = trim($_POST['ewnioski_sg_niedopracowania']);
        $this->Data('ewnioski_data_poczatkowa', 'ewnioski_data_poczatkowa', date('Y-m').'-01');
        $this->Data('ewnioski_data_koncowa', 'ewnioski_data_koncowa',$pierwszy);
        $this->Data('ewnioski_data_poczatkowa_stan_wejsc', 'ewnioski_data_poczatkowa_stan_wejsc',  date("Y-m-d", strtotime(" - 1 days")));
        $this->Data('ewnioski_data_koncowa_stan_wejsc', 'ewnioski_data_koncowa_stan_wejsc', date('Y-m-d'));
        $this->Filter('ewnioski_data_wpisania');
        $this->Filter('ewnioski_imieinazwisko');
        $this->Filter('ewnioski_numer_ewidencyjny');
        $this->Filter('ewnioski_poczatek_wolnego');
        $this->Filter('ewnioski_koniec_wolnego');
        $this->Filter('ewnioski_liczba_dni');
        $this->Filter('ewnioski_imie_dziecka');
        $this->Filter('ewnioski_data_urodzenia_dziecka');
        $this->Filter('ewnioski_status');
        $this->Filter('ewnioski_status_kierownik');
        $this->Filter('ewnioski_status_hr');
        $this->Filter('ewnioski_dane_mpk');
        $this->Filter('ewnioski_kategoria');
        $this->Filter('ewnioski_liczba_dni_eksport');
        $this->Filter('ewnioski_data_odrobienia');
        $this->Filter('ewnioski_osoby_mpk');
        $this->Filter('ewnioski_akceptacja');
        $this->Filter('ewnioski_mail_mpk');
        $this->Filter('ewnioski_inna_osoba');
        $this->Filter('ewnioski_osoby_numer');
        $this->Filter('ewnioski_osoby_imie');
        $this->Filter('ewnioski_osoby_akceptajca');
        $this->Filter('ewnioski_akcpetujacy');
        $this->Filter('ewnioski_data_wpisania_zwolnienie');
        $this->Filter('ewnioski_imieinazwisko_zwolnienie');
        $this->Filter('ewnioski_numer_ewidencyjny_zwolnienie');
        $this->Filter('ewnioski_poczatek_wolnego_zwolnienie');
        $this->Filter('ewnioski_koniec_wolnego_zwolnienie');
        $this->Filter('ewnioski_data_odrobienia_zwolnienie');
        $this->Filter('ewnioski_liczba_dni_zwolnienie');
        $this->Filter('ewnioski_liczba_dni_eksport_zwolnienie');
        $this->Filter('ewnioski_kategoria_zwolnienie');
        $this->Filter('ewnioski_status_zwolnienie');
        $this->Filter('ewnioski_dane_mpk_zwolnienie');
        $this->Filter('ewnioski_status_kierownik_zwolnienie');
        $this->Filter('ewnioski_status_hr_zwolnienie');
        $this->Filter('ewnioski_osoby_lista_imie');
        $this->Filter('ewnioski_osoby_lista_nazwisko');
        $this->Filter('ewnioski_osoby_lista_numer_ewidencyjny');
        $this->Filter('ewnioski_osoby_lista_email');
        $this->Filter('ewnioski_osoby_lista_mpk');
        $this->Filter('ewnioski_osoby_lista_mail_kierownik');
        $this->Filter('ewnioski_osoby_lista_akceptacja');
        $this->Filter('ewnioski_osoby_lista_mail_kieronwik2');
        $this->Filter('ewnioski_osoby_lista_akceptacja2');
        $this->Filter('ewnioski_osoby_lista_mail_kieronwik3');
        $this->Filter('ewnioski_sg_numer_ewidencyjny');
        $this->Filter('ewnioski_sg_nazwisko');
        $this->Filter('ewnioski_sg_imie');
        $this->Filter('ewnioski_sg_mpk');
        $this->Filter('ewnioski_sg_nazwikso_kierownik');
        $this->Filter('ewnioski_sg_stan_urlpou');
        $this->Filter('ewnioski_sg_urlop_zalegly');
        $this->Filter('ewnioski_sg_uz');
        $this->Filter('ewnioski_sg_rcp');
        $this->Filter('ewnioski_sg_ilosc_nadgodzin');
        $this->Filter('ewnioski_sg_ilosc_nadgodzin_plus');
        $this->Filter('ewnioski_sg_stan_godzin');
        $this->Filter('ewnioski_sg_nieodpracowania');
        $this->Filter('ewnioski_sg_data_importu');
        $this->Filter('ewnioski_sg_nazwisko_import');
        $this->Filter('ewnioski_numer_ewidencyjny_wpisujacy');
        $this->Filter('ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie');
        $this->Filter('ewnioski_wejscia_mumer_ewidencyjny');
        $this->Filter('ewnioski_wejscia_imie');
        $this->Filter('ewnioski_wejscia_nazwisko');
        $this->Filter('ewnioski_wejscia_godzina_wejscia');
        $this->Filter('ewnioski_wejscia_godzina_wyjscia');
        $this->Filter('ewnioski_wejscia_grupa');
        $this->Filter('ewnioski_nazwa_pliku');
        $this->Filter('ewnioski_osoby_praca_zdalna');
        $this->Filter('ewnioski_pz_numer_ewidencyjny');
        $this->Filter('ewnioski_pz_data_rozpoczecia');
        $this->Filter('ewnioski_pz_akcja');
        $this->Filter('ewnioski_pz_wpisujacy');
        $this->Filter('ewnioski_pz_data_wpisu');
        $this->Filter('ewnioski_pz_imie');
        $this->Filter('ewnioski_pz_nazwisko');
        $this->Filter('ewnioski_profil');
        $this->Filter('ewnioski_typ_pobytu');
        $this->Filter('ewnioski_miejsce_wejscia');
        $this->Filter('ewnioski_odbicia_mpk');
        $this->Filter('ewnioski_wejscia_godzina_wyjscia');
        $this->Filter('ewnioski_wejscia_godzina_odbicia');
        $this->Filter('');
    }
    /**
     * Ustawienie filtru daty początkowej
     * @param type $data Zmienna pryjmujaca datę
     * @param type $sesja Zmienna przyjmująca nazwę
     * @param type $data_domyslna Zmienna przyjmująca aktualna datę
     */
    function Data($data, $sesja, $data_domyslna) {
        if (isset($_REQUEST[$data])) {
            $_SESSION[$sesja] = $_REQUEST[$data];
        } elseif (!isset($_REQUEST[$data]) AND isset($_SESSION[$sesja])) {
            // Brak akcji
        } else {
            $_SESSION[$sesja] = $data_domyslna;
        }
    }
    /**
     * Ustawienie filtru
     * @param type $nazwa nazwa filtru
     */
    function Filter($nazwa) {
        if (isset($_POST[$nazwa])) {
            $_SESSION[$nazwa] = $_POST[$nazwa];
        } elseif (isset($_SESSION[$nazwa])) {

        } else {
            $_SESSION[$nazwa] = "";
        }
    }
    /**
     * Usnięcie zmiennych sesyjnych programu
     */
    function WyczyscFiltry() {

        unset($_SESSION['ewnioski_sortowanie_kierunek']);
        unset($_SESSION['ewnioski_sortowanie']);
        unset($_SESSION['ewnioski_sortowanie_historia']);
        unset($_SESSION['ewnioski_data_poczatkowa']);
        unset($_SESSION['ewnioski_data_koncowa']);
        unset($_SESSION['ewnioski_data_poczatkowa_stan_wejsc']);
        unset($_SESSION['ewnioski_data_koncowa_stan_wejsc']);
        unset($_SESSION['ewnioski_data_wpisania']);
        unset($_SESSION['ewnioski_imieinazwisko']);
        unset($_SESSION['ewnioski_numer_ewidencyjny']);
        unset($_SESSION['ewnioski_poczatek_wolnego']);
        unset($_SESSION['ewnioski_koniec_wolnego']);
        unset($_SESSION['ewnioski_liczba_dni']);
        unset($_SESSION['ewnioski_imie_dziecka']);
        unset($_SESSION['ewnioski_data_urodzenia_dziecka']);
        unset($_SESSION['ewnioski_status']);
        unset($_SESSION['ewnioski_status_kierownik']);
        unset($_SESSION['ewnioski_status_hr']);
        unset($_SESSION['ewnioski_dane_mpk']);
        unset($_SESSION['ewnioski_kategoria']);
        unset($_SESSION['ewnioski_liczba_dni_eksport']);
        unset($_SESSION['ewnioski_data_odrobienia']);
        unset($_SESSION['ewnioski_osoby_mpk']);
        unset($_SESSION['ewnioski_akceptacja']);
        unset($_SESSION['ewnioski_mail_mpk']);
        unset($_SESSION['ewnioski_inna_osoba']);
        unset($_SESSION['ewnioski_osoby_numer']);
        unset($_SESSION['ewnioski_osoby_imie']);
        unset($_SESSION['ewnioski_osoby_akceptajca']);
        unset($_SESSION['ewnioski_akcpetujacy']);
        unset($_SESSION['ewnioski_data_wpisania_zwolnienie']);
        unset($_SESSION['ewnioski_imieinazwisko_zwolnienie']);
        unset($_SESSION['ewnioski_numer_ewidencyjny_zwolnienie']);
        unset($_SESSION['ewnioski_poczatek_wolnego_zwolnienie']);
        unset($_SESSION['ewnioski_koniec_wolnego_zwolnienie']);
        unset($_SESSION['ewnioski_data_odrobienia_zwolnienie']);
        unset($_SESSION['ewnioski_liczba_dni_zwolnienie']);
        unset($_SESSION['ewnioski_liczba_dni_eksport_zwolnienie']);
        unset($_SESSION['ewnioski_kategoria_zwolnienie']);
        unset($_SESSION['ewnioski_status_zwolnienie']);
        unset($_SESSION['ewnioski_dane_mpk_zwolnienie']);
        unset($_SESSION['ewnioski_status_kierownik_zwolnienie']);
        unset($_SESSION['ewnioski_status_hr_zwolnienie']);
        unset($_SESSION['ewnioski_osoby_lista_imie']);
        unset($_SESSION['ewnioski_osoby_lista_nazwisko']);
        unset($_SESSION['ewnioski_osoby_lista_numer_ewidencyjny']);
        unset($_SESSION['ewnioski_osoby_lista_email']);
        unset($_SESSION['ewnioski_osoby_lista_mpk']);
        unset($_SESSION['ewnioski_osoby_lista_mail_kierownik']);
        unset($_SESSION['ewnioski_osoby_lista_akceptacja']);
        unset($_SESSION['ewnioski_osoby_lista_mail_kieronwik2']);
        unset($_SESSION['ewnioski_osoby_lista_akceptacja2']);
        unset($_SESSION['ewnioski_osoby_lista_mail_kieronwik3']);
        unset($_SESSION['ewnioski_sg_numer_ewidencyjny']);
        unset($_SESSION['ewnioski_sg_nazwisko']);
        unset($_SESSION['ewnioski_sg_imie']);
        unset($_SESSION['ewnioski_sg_mpk']);
        unset($_SESSION['ewnioski_sg_nazwikso_kierownik']);
        unset($_SESSION['ewnioski_sg_stan_urlpou']);
        unset($_SESSION['ewnioski_sg_urlop_zalegly']);
        unset($_SESSION['ewnioski_sg_uz']);
        unset($_SESSION['ewnioski_sg_rcp']);
        unset($_SESSION['ewnioski_sg_ilosc_nadgodzin']);
        unset($_SESSION['ewnioski_sg_ilosc_nadgodzin_plus']);
        unset($_SESSION['ewnioski_sg_stan_godzin']);
        unset($_SESSION['ewnioski_sg_niedopracowania']);
        unset($_SESSION['ewnioski_sg_data_importu']);
        unset($_SESSION['ewnioski_sg_nazwisko_import']);
        unset($_SESSION['ewnioski_sortowanie_urlopy']);
        unset($_SESSION['ewnioski_sortowanie_godziny']);
        unset($_SESSION['ewnioski_sortowanie_zwolnienia']);
        unset($_SESSION['ewnioski_sortowanie_osoby']);
        unset($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy']);
        unset($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie']);
        unset($_SESSION['ewnioski_wejscia_mumer_ewidencyjny']);
        unset($_SESSION['ewnioski_wejscia_imie']);
        unset($_SESSION['ewnioski_wejscia_nazwisko']);
        unset($_SESSION['ewnioski_wejscia_godzina_wejscia']);
        unset($_SESSION['ewnioski_wejscia_godzina_wyjscia']);
        unset($_SESSION['ewnioski_wejscia_grupa']);
        unset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset']);
        unset($_SESSION['ewnioski_nazwa_pliku']);
        unset($_SESSION['ewnioski_osoby_praca_zdalna']);
        unset($_SESSION['ewnioski_pz_numer_ewidencyjny']);
        unset($_SESSION['ewnioski_pz_data_rozpoczecia']);
        unset($_SESSION['ewnioski_pz_akcja']);
        unset($_SESSION['ewnioski_pz_wpisujacy']);
        unset($_SESSION['ewnioski_pz_data_wpisu']);
        unset($_SESSION['ewnioski_sortowanie_praca_zdalna']);
        unset($_SESSION['ewnioski_pz_imie']);
        unset($_SESSION['ewnioski_pz_nazwisko']);
        unset($_SESSION['ewnioski_typ_pobytu']);
        unset($_SESSION['ewnioski_miejsce_wejscia']);
        unset($_SESSION['ewnioski_odbicia_mpk']);
        unset($_SESSION['ewnioski_wejscia_godzina_wyjscia']);
        unset($_SESSION['ewnioski_wejscia_godzina_odbicia']);
        unset($_SESSION['ewnioski_sortowanie_odbicia']);
        unset($_SESSION['ewnioski_sortowanie_wejscia']);
        unset($_SESSION['']);
    }
    /**
     * Usnięcie zmiennych sesyjnych programu utworzonych podczas logowania
     */
    function WyczyscFiltryLogowanie() {

        unset($_SESSION['ewnioski_full_name']);
        unset($_SESSION['ewnioski_login']);
        unset($_SESSION['ewnioski_kierownik']);
        unset($_SESSION['ewnioski_stanowisko']);
        unset($_SESSION['ewnioski_mpk']);
        unset($_SESSION['ewnioski_mail']);
        unset($_SESSION['ewnioski_mail_kierownik']);
        unset($_SESSION['ewnioski_czas']);
        unset($_SESSION['ewnioski_data']);
        unset($_SESSION['ewnioski_admin']);
        unset($_SESSION['ewnioski_limit_stron']);
        unset($_SESSION['inny_pracownik']);
        unset($_SESSION['ewnioski_profil']);
        unset($_SESSION['ewnioski_zmiana_profilu']);


    }

    /**
     * Ustawienei zmiennych odpowiedzialnych za sortowanei bazy danych
     * @param $nazwa_sesji Nazwa zmiennej która ma byc sprawdzona
     * @param $nazwa_pola_tabela Nazwa tabelki z bazy danychh
     * @param $kierunek_sortowania Kierunek sortowania
     */
    function Sortowanie($nazwa_sesji, $nazwa_pola_tabela, $kierunek_sortowania)
    {
        $_SESSION[$nazwa_sesji] = trim($nazwa_pola_tabela);
        if($nazwa_pola_tabela == $_SESSION[$nazwa_sesji] AND $_SESSION[$kierunek_sortowania] == 'ASC') $_SESSION[$kierunek_sortowania] = 'DESC';
        elseif($nazwa_pola_tabela == $_SESSION[$nazwa_sesji] AND $_SESSION[$kierunek_sortowania] == 'DESC') $_SESSION[$kierunek_sortowania] = 'ASC';
    }
}