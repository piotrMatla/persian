<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 11:44
 */

Konfiguracja::WczytajPolaczenieWise();

/**
 * Class Filtrowanie Klasa z funkcjami odpowiedzialnymi za sortowanie
 */
class Filtrowanie extends Polaczenie
{
    /**
     * Filtry raportów zapamiętywane w sesji (wartość z formularza nadpisuje zapamiętaną)
     */
    const FILTRY = [
        'ewnioski_data_wpisania', 'ewnioski_imieinazwisko', 'ewnioski_numer_ewidencyjny', 'ewnioski_poczatek_wolnego',
        'ewnioski_koniec_wolnego', 'ewnioski_liczba_dni', 'ewnioski_imie_dziecka', 'ewnioski_data_urodzenia_dziecka',
        'ewnioski_status', 'ewnioski_status_kierownik', 'ewnioski_status_hr', 'ewnioski_dane_mpk', 'ewnioski_kategoria',
        'ewnioski_liczba_dni_eksport', 'ewnioski_data_odrobienia', 'ewnioski_osoby_mpk', 'ewnioski_akceptacja',
        'ewnioski_mail_mpk', 'ewnioski_inna_osoba', 'ewnioski_osoby_numer', 'ewnioski_osoby_imie',
        'ewnioski_osoby_akceptajca', 'ewnioski_akcpetujacy',
        'ewnioski_data_wpisania_zwolnienie', 'ewnioski_imieinazwisko_zwolnienie', 'ewnioski_numer_ewidencyjny_zwolnienie',
        'ewnioski_poczatek_wolnego_zwolnienie', 'ewnioski_koniec_wolnego_zwolnienie', 'ewnioski_data_odrobienia_zwolnienie',
        'ewnioski_liczba_dni_zwolnienie', 'ewnioski_liczba_dni_eksport_zwolnienie', 'ewnioski_kategoria_zwolnienie',
        'ewnioski_status_zwolnienie', 'ewnioski_dane_mpk_zwolnienie', 'ewnioski_status_kierownik_zwolnienie',
        'ewnioski_status_hr_zwolnienie',
        'ewnioski_osoby_lista_imie', 'ewnioski_osoby_lista_nazwisko', 'ewnioski_osoby_lista_numer_ewidencyjny',
        'ewnioski_osoby_lista_email', 'ewnioski_osoby_lista_mpk', 'ewnioski_osoby_lista_mail_kierownik',
        'ewnioski_osoby_lista_akceptacja', 'ewnioski_osoby_lista_mail_kieronwik2', 'ewnioski_osoby_lista_akceptacja2',
        'ewnioski_osoby_lista_mail_kieronwik3',
        'ewnioski_sg_numer_ewidencyjny', 'ewnioski_sg_nazwisko', 'ewnioski_sg_imie', 'ewnioski_sg_mpk',
        'ewnioski_sg_nazwikso_kierownik', 'ewnioski_sg_stan_urlpou', 'ewnioski_sg_urlop_zalegly', 'ewnioski_sg_uz',
        'ewnioski_sg_rcp', 'ewnioski_sg_ilosc_nadgodzin', 'ewnioski_sg_ilosc_nadgodzin_plus', 'ewnioski_sg_stan_godzin',
        'ewnioski_sg_nieodpracowania', 'ewnioski_sg_godziny_do_odpracowania', 'ewnioski_sg_data_importu', 'ewnioski_sg_nazwisko_import',
        'ewnioski_numer_ewidencyjny_wpisujacy', 'ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie',
        'ewnioski_wejscia_mumer_ewidencyjny', 'ewnioski_wejscia_imie', 'ewnioski_wejscia_nazwisko',
        'ewnioski_wejscia_godzina_wejscia', 'ewnioski_wejscia_godzina_wyjscia', 'ewnioski_wejscia_grupa',
        'ewnioski_nazwa_pliku', 'ewnioski_osoby_praca_zdalna',
        'ewnioski_pz_numer_ewidencyjny', 'ewnioski_pz_data_rozpoczecia', 'ewnioski_pz_akcja', 'ewnioski_pz_wpisujacy',
        'ewnioski_pz_data_wpisu', 'ewnioski_pz_imie', 'ewnioski_pz_nazwisko',
        'ewnioski_profil', 'ewnioski_typ_pobytu', 'ewnioski_miejsce_wejscia', 'ewnioski_odbicia_mpk',
        'ewnioski_wejscia_godzina_odbicia',
    ];

    /**
     * Domyślne pola sortowania raportów
     */
    const SORTOWANIE_DOMYSLNE = [
        'ewnioski_sortowanie' => 'id',
        'ewnioski_sortowanie_historia' => 'id',
        'ewnioski_sortowanie_urlopy' => 'id',
        'ewnioski_sortowanie_godziny' => 'id',
        'ewnioski_sortowanie_zwolnienia' => 'id',
        'ewnioski_sortowanie_osoby' => 'id',
        'ewnioski_sortowanie_praca_zdalna' => 'id',
        'ewnioski_sortowanie_wejscia' => 'data_odbicia',
        'ewnioski_sortowanie_sprawdz_zastepce' => 'sg.numer_ewidencyjny',
        'ewnioski_sortowanie_odbicia' => 'data_odbicia',
    ];

    /**
     * Zmienne sesyjne usuwane razem z filtrami (poza samymi filtrami)
     */
    const CZYSZCZONE_RAZEM_Z_FILTRAMI = [
        'ewnioski_sortowanie_kierunek', 'ewnioski_sortowanie', 'ewnioski_sortowanie_historia',
        'ewnioski_sortowanie_urlopy', 'ewnioski_sortowanie_godziny', 'ewnioski_sortowanie_zwolnienia',
        'ewnioski_sortowanie_osoby', 'ewnioski_sortowanie_praca_zdalna', 'ewnioski_sortowanie_odbicia',
        'ewnioski_sortowanie_wejscia',
        'ewnioski_data_poczatkowa', 'ewnioski_data_koncowa', 'ewnioski_data_poczatkowa_stan_wejsc',
        'ewnioski_data_koncowa_stan_wejsc', 'ewnioski_sg_niedopracowania',
        'ewnioski_produktywnosc_filter_nadgodziny_offset',
    ];

    /**
     * Zmienne sesyjne utworzone podczas logowania
     */
    const ZMIENNE_LOGOWANIA = [
        'ewnioski_full_name', 'ewnioski_login', 'ewnioski_kierownik', 'ewnioski_stanowisko', 'ewnioski_mpk',
        'ewnioski_mail', 'ewnioski_mail_kierownik', 'ewnioski_czas', 'ewnioski_data', 'ewnioski_admin',
        'ewnioski_limit_stron', 'inny_pracownik', 'ewnioski_profil', 'ewnioski_zmiana_profilu',
    ];

    /**
     * Ustawienei zmienej odnośnie sortowania
     */
    function UstawienieSortowaniaFiltrow()
    {
        $pierwszy = date("Y-m", mktime(0, 0, 0, (int)date("m") + 1, (int)date("d"), (int)date("Y"))) . '-01';
        $pierwszy = date('Y-m-d', strtotime('-1 day', strtotime($pierwszy)));
        if (!isset($_SESSION['ewnioski_sortowanie_kierunek'])) $_SESSION['ewnioski_sortowanie_kierunek'] = 'ASC';
        foreach (self::SORTOWANIE_DOMYSLNE as $nazwa => $pole) {
            if (!isset($_SESSION[$nazwa])) $_SESSION[$nazwa] = $pole;
        }
        if (!isset($_SESSION['ewnioski_sg_niedopracowania'])) $_SESSION['ewnioski_sg_niedopracowania'] = '';
        if (isset($_POST['ewnioski_sg_niedopracowania'])) $_SESSION['ewnioski_sg_niedopracowania'] = trim($_POST['ewnioski_sg_niedopracowania']);
        $this->Data('ewnioski_data_poczatkowa', 'ewnioski_data_poczatkowa', date('Y-m') . '-01');
        $this->Data('ewnioski_data_koncowa', 'ewnioski_data_koncowa', $pierwszy);
        $this->Data('ewnioski_data_poczatkowa_stan_wejsc', 'ewnioski_data_poczatkowa_stan_wejsc', date("Y-m-d", strtotime(" - 1 days")));
        $this->Data('ewnioski_data_koncowa_stan_wejsc', 'ewnioski_data_koncowa_stan_wejsc', date('Y-m-d'));
        foreach (self::FILTRY as $filtr) {
            $this->Filter($filtr);
        }
    }

    /**
     * Ustawienie filtru daty początkowej
     * @param string $data Zmienna pryjmujaca datę
     * @param string $sesja Zmienna przyjmująca nazwę
     * @param string $data_domyslna Zmienna przyjmująca aktualna datę
     */
    function Data($data, $sesja, $data_domyslna)
    {
        // daty trafiają do zapytań SQL - przyjmujemy tylko format z pola <input type="date">
        if (isset($_REQUEST[$data]) && is_string($_REQUEST[$data]) && preg_match('/^(\d{4}-\d{2}-\d{2})?$/', $_REQUEST[$data])) {
            $_SESSION[$sesja] = $_REQUEST[$data];
        } elseif (!isset($_SESSION[$sesja])) {
            $_SESSION[$sesja] = $data_domyslna;
        }
    }

    /**
     * Ustawienie filtru
     * @param string $nazwa nazwa filtru
     */
    function Filter($nazwa)
    {
        if (isset($_POST[$nazwa])) {
            $_SESSION[$nazwa] = $_POST[$nazwa];
        } elseif (!isset($_SESSION[$nazwa])) {
            $_SESSION[$nazwa] = "";
        }
    }

    /**
     * Usnięcie zmiennych sesyjnych programu
     */
    function WyczyscFiltry()
    {
        foreach (self::FILTRY as $filtr) {
            // zakład wybrany w nagłówku nie jest filtrem raportu - zostaje do wylogowania
            if ($filtr !== 'ewnioski_profil') unset($_SESSION[$filtr]);
        }
        foreach (self::CZYSZCZONE_RAZEM_Z_FILTRAMI as $nazwa) {
            unset($_SESSION[$nazwa]);
        }
    }

    /**
     * Usnięcie zmiennych sesyjnych programu utworzonych podczas logowania
     */
    function WyczyscFiltryLogowanie()
    {
        foreach (self::ZMIENNE_LOGOWANIA as $nazwa) {
            unset($_SESSION[$nazwa]);
        }
    }

    /**
     * Ustawienei zmiennych odpowiedzialnych za sortowanei bazy danych
     * @param string $nazwa_sesji Nazwa zmiennej która ma byc sprawdzona
     * @param string $nazwa_pola_tabela Nazwa pola z bazy danych (trafia do ORDER BY, więc dopuszczamy tylko nazwy kolumn)
     * @param string $kierunek_sortowania Kierunek sortowania
     */
    function Sortowanie($nazwa_sesji, $nazwa_pola_tabela, $kierunek_sortowania)
    {
        $nazwa_pola_tabela = trim((string)$nazwa_pola_tabela);
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $nazwa_pola_tabela)) {
            return;
        }
        $_SESSION[$nazwa_sesji] = $nazwa_pola_tabela;
        if (($_SESSION[$kierunek_sortowania] ?? '') == 'ASC') $_SESSION[$kierunek_sortowania] = 'DESC';
        elseif (($_SESSION[$kierunek_sortowania] ?? '') == 'DESC') $_SESSION[$kierunek_sortowania] = 'ASC';
    }
}
