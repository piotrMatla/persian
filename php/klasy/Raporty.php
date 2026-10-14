<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 17.12.2018
 * Time: 15:48
 */


/**
 * Class Raporty Klasa z raportami
 */
class Raporty extends Common
{
        /**
     * Mapowanie kolumn formatki Excel ("Stan urlopu i nadgodzin")
     * na kolumny tabeli stan_godzin.
     *
     * Klucz tablicy - nagłówek dokładnie tak, jak występuje w formatce HR.
     *                 Porównanie jest odporne na wielkość liter, spacje na
     *                 końcach i podwójne spacje (patrz NormalizujNaglowek()).
     * kolumna       - nazwa kolumny w tabeli stan_godzin
     *                 ('brygadzista' jest polem specjalnym: 'B' => 1).
     * walidacja     - reguła sprawdzania komórki w wierszu z danymi:
     *                 'numer'      - 5 lub 6 znaków (numery ewidencyjne),
     *                 'tekst'      - więcej niż 2 znaki,
     *                 'niepuste'   - cokolwiek niepustego,
     *                 'opcjonalne' - może być puste.
     * liczba        - true => przecinek dziesiętny zamieniany na kropkę przy zapisie.
     *
     * Żeby dodać kolejną kolumnę: dopisz tu jedną linię, dopisz nazwę kolumny
     * w $ImportStanGodzinKolejnosc i w INSERT w ZaciagnijDane().
     */
    public $ImportStanGodzinMapowanie = array(
        'Nr ewidencyjny'                             => array('kolumna' => 'numer_ewidencyjny',       'walidacja' => 'numer',      'liczba' => false),
        'Nazwisko'                                   => array('kolumna' => 'nazwisko',                'walidacja' => 'tekst',      'liczba' => false),
        'Imię'                                       => array('kolumna' => 'imie',                    'walidacja' => 'tekst',      'liczba' => false),
        'Mpk i grupa'                                => array('kolumna' => 'mpk',                     'walidacja' => 'tekst',      'liczba' => false),
        'Kierownik'                                  => array('kolumna' => 'numer_kierownik',         'walidacja' => 'numer',      'liczba' => false),
        'UW'                                         => array('kolumna' => 'stan_urlopu',             'walidacja' => 'niepuste',   'liczba' => true),
        'UW - godziny'                               => array('kolumna' => 'stan_urlopu_godziny',     'walidacja' => 'niepuste',   'liczba' => true),
        'UW zaległy'                                 => array('kolumna' => 'urlop_zalegly',           'walidacja' => 'niepuste',   'liczba' => true),
        'UŻ'                                         => array('kolumna' => 'uz',                      'walidacja' => 'niepuste',   'liczba' => true),
        'Ilość nadgodzin nadpracowanych (limit 260)' => array('kolumna' => 'rcp',                     'walidacja' => 'niepuste',   'liczba' => true),
        'NADGODZINY'                                 => array('kolumna' => 'ilosc_nadgodzin',         'walidacja' => 'niepuste',   'liczba' => true),
        'Godziny odpracowane (+)'                    => array('kolumna' => 'ilosc_nadgodzin_plus',    'walidacja' => 'niepuste',   'liczba' => true),
        'Godziny do odpracowania (-)'                => array('kolumna' => 'godziny_do_odpracowania', 'walidacja' => 'niepuste',   'liczba' => true),
        'Niedopracowania (bez wniosku)'              => array('kolumna' => 'niedopracowania',         'walidacja' => 'niepuste',   'liczba' => true),
        'Stan na dzień'                              => array('kolumna' => 'stan_na_dzien',           'walidacja' => 'niepuste',   'liczba' => false),
        'Brygadzista'                                => array('kolumna' => 'brygadzista',             'walidacja' => 'opcjonalne', 'liczba' => false),
    );

    /**
     * Kolejność, w jakiej WeryfikacjaExcel() zapisuje wartości do pliku
     * tymczasowego, a ZaciagnijDane() je stamtąd czyta. Jest niezależna
     * od kolejności kolumn w arkuszu Excela.
     * Pod indeksem count($ImportStanGodzinKolejnosc) (czyli 16) jest flaga błędu.
     */
    public $ImportStanGodzinKolejnosc = array(
        'numer_ewidencyjny', 'nazwisko', 'imie', 'mpk', 'numer_kierownik',
        'stan_urlopu', 'stan_urlopu_godziny', 'urlop_zalegly', 'uz', 'rcp',
        'ilosc_nadgodzin', 'ilosc_nadgodzin_plus', 'godziny_do_odpracowania',
        'niedopracowania', 'stan_na_dzien', 'brygadzista'
    );
    /**
     * Kolumny raportów, które użytkownik może ukryć: numer kolumny => nazwa
     */
    const KOLUMNY_URLOPY = [
        1 => 'Lp.', 2 => 'ID', 3 => 'Wybór globalny', 4 => 'Data wpisania wniosku', 5 => 'Imię i nazwisko',
        6 => 'Numer ewidencyjny', 44 => 'Numer wpisującego', 7 => 'Nieobecność od', 8 => 'Nieobecność do',
        9 => 'Data odpracowania', 10 => 'Liczba dni urlopu wnioskowanego', 11 => 'Rodzaj nieobecności ',
        12 => 'Imię dziecka', 13 => 'Data urodzenia dziecka', 14 => 'Osoba akceptująca', 15 => 'Data akceptacji',
        16 => 'Przełożony', 17 => 'Status wniosku', 45 => 'Profil', 18 => 'Pobrane przez HR',
        19 => 'Wygeneruj wniosek PDF', 20 => 'Zaakceptuj wniosek', 21 => 'Anuluj wniosek', 22 => 'Odzrzuć wniosek',
        43 => 'Tylko mój MPK',
    ];
    const KOLUMNY_ZWOLNIENIA = [
        23 => 'Lp.', 24 => 'ID', 25 => 'Wybór globalny', 26 => 'Data wpisania ', 27 => 'Imię i nazwisko',
        28 => 'Numer ewidencyjny', 29 => 'Liczba godzin zwolnienia', 30 => 'Data zwolnienia z godzin',
        31 => 'Data odpracowania', 32 => 'Godzina rozpoczęcia pracy', 33 => 'Przedział czasowy zmiany godzin',
        34 => 'Rodzaj zwolnienia', 35 => 'Status zwolnienia', 36 => 'Dane kierownik', 37 => 'Akceptacja kierownika',
        38 => 'Pobrane przez HR', 39 => 'Wygeneruj wniosek PDF', 40 => 'Zaakceptuj wniosek', 41 => 'Anuluj wniosek',
        42 => 'Odzrzuć wniosek', 43 => 'Tylko mój MPK',
    ];

    /**
     * Menu szukania danych (zakres dat, liczba wyników, widoczność kolumn)
     */
    function PokazMenuSzukaniaDanych()
    {
        $this->MenuSzukania('ewnioski_data_poczatkowa', 'ewnioski_data_koncowa');
    }

    /**
     * Menu szukania danych w raportach wejść i odbić
     */
    function PokazMenuSzukaniaDanychWejscia()
    {
        $this->MenuSzukania('ewnioski_data_poczatkowa_stan_wejsc', 'ewnioski_data_koncowa_stan_wejsc');
    }

    /**
     * Formularz zakresu dat i liczby wyników oraz menu widoczności kolumn
     * @param string $poleOd Nazwa pola (i zmiennej sesyjnej) z datą początkową
     * @param string $poleDo Nazwa pola (i zmiennej sesyjnej) z datą końcową
     */
    private function MenuSzukania(string $poleOd, string $poleDo): void
    {
        ?>
        <div class="toolbar">
            <form method="POST" id="form_1" class="toolbar-form">
                <input type="hidden" name="szukaj"/>
                <label class="field">
                    <span class="field-label">Data początkowa</span>
                    <input type="date" name="<?php echo $poleOd; ?>" value="<?php echo htmlspecialchars((string)$_SESSION[$poleOd]); ?>"/>
                </label>
                <label class="field">
                    <span class="field-label">Data końcowa</span>
                    <input type="date" name="<?php echo $poleDo; ?>" value="<?php echo htmlspecialchars((string)$_SESSION[$poleDo]); ?>"/>
                </label>
                <label class="field">
                    <span class="field-label">Liczba wyników</span>
                    <select name="limit_stron">
                        <?php for ($i = 100; $i <= 10000; $i += 100) { ?>
                            <option <?php if ($i == $_SESSION['ewnioski_limit_stron']) echo " SELECTED"; ?> value="<?php echo $i ?>"><?php echo $i ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Szukaj"/>
            </form>
            <form method="POST" class="toolbar-form">
                <input type="hidden" name="wyczysc_filtry"/>
                <input type="submit" value="Wyczyść filtry"/>
            </form>
            <?php if (isset($_GET['urlop']) or isset($_GET['zwolnienia'])) { ?>
                <button type="button" class="btn" onclick="$('#submenu').slideToggle(300);">Widoczność kolumn</button>
            <?php } ?>
        </div>
        <?php
        if (isset($_GET['urlop'])) $this->MenuWidocznosciKolumn('Nazwa kolumny urlopu', self::KOLUMNY_URLOPY);
        if (isset($_GET['zwolnienia'])) $this->MenuWidocznosciKolumn('Nazwa kolumny zwolnienia', self::KOLUMNY_ZWOLNIENIA);
    }

    /**
     * Tabela z checkboxami ukrywania kolumn raportu (zapis przez ZmianaWidocznosc.php)
     * @param string $tytul Nagłówek pierwszej kolumny
     * @param array $kolumny Numer kolumny => nazwa
     */
    private function MenuWidocznosciKolumn(string $tytul, array $kolumny): void
    {
        $widocznosc = explode(',', (string)$_SESSION['ewnioski_widocznosc']);
        ?>
        <div id="submenu" class="panel column-visibility">
            <table border="2" align="center" class="wysrodkoj">
                <tr>
                    <td><?php echo $tytul; ?></td>
                    <?php foreach ($kolumny as $nazwa) { ?>
                        <td><?php echo $nazwa; ?></td>
                    <?php } ?>
                </tr>
                <tr>
                    <td>Ukryj</td>
                    <?php foreach (array_keys($kolumny) as $numer) { ?>
                        <td><input type="checkbox" id="<?php echo $numer; ?>" value="<?php echo $numer; ?>" <?php
                            if (array_search((string)$numer, $widocznosc) !== false) echo "CHECKED";
                            ?> onchange="Ajax('../php/ZmianaWidocznosc.php', '<?php echo $numer; ?>')"/></td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align="center" colspan="<?php echo count($kolumny) + 1; ?>">
                        <form method="POST">
                            <input type="submit" value="Zastosuj filtry widoczności"/>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
      /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function RaportUrlopow()
    {

        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array(' w.data_wpisania', 'w.imie_nazwisko', 'w.numer_ewidencyjny', 'w.poczatek_wolnego','w.koniec_wolnego','w.liczba_dni','w.liczba_dni_eksport',' k.nazwa','w.imie_dziecka',' w.id_status','os.nazwisko',' w.data_kierownik','w.data_hr','w.login_kierownik','w.numer_ewidencyjny_wpisujacy','w.profil');
        $array2 = array('ewnioski_data_wpisania', 'ewnioski_imieinazwisko', 'ewnioski_numer_ewidencyjny', 'ewnioski_poczatek_wolnego', 'ewnioski_koniec_wolnego','ewnioski_liczba_dni','ewnioski_liczba_dni_eksport','ewnioski_kategoria',
        'ewnioski_imie_dziecka','ewnioski_status','ewnioski_dane_mpk','ewnioski_status_kierownik','ewnioski_status_hr', 'ewnioski_akcpetujacy','ewnioski_numer_ewidencyjny_wpisujacy','ewnioski_profil');
  $widocznosc = array();
  $warunek='';
  $profil_raport=array();
        $widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
         if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)  and array_search('43', $widocznosc) === false) {
            $dodatkowy_warunek=" and w.profil='".$_SESSION['ewnioski_profil']."'";
        }
        else
        {
            $zapytaniespr="SELECT o.numer_ewidencyjny, profil FROM ewidencja_szkolen.osoby o
 WHERE o.numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
           while ($danespr=mysqli_fetch_array($wykonajspr))
                  {
                      $warunek.="or w.dane_mpk='".$danespr[0]."'";
                  }

if($warunek=='')  $dodatkowy_warunek = "and (w.numer_ewidencyjny_wpisujacy='".$_SESSION['ewnioski_login']."' or w.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or w.dane_mpk='".$_SESSION['ewnioski_login']."')";
else
    {
        $dodatkowy_warunek = "and (w.numer_ewidencyjny_wpisujacy='".$_SESSION['ewnioski_login']."' or w.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' $warunek or w.dane_mpk='".$_SESSION['ewnioski_login']."')";
    }
        }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                if($array2[$i]=='ewnioski_profil')
                    {
                        if($_SESSION['ewnioski_zmiana_profilu']!=0)
                            {
                        if($_SESSION[$array2[$i]]!=999) $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                        }
                    }
                else{
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                    }
                $licznik++;
            }
        }
        $zapytanie = "SELECT  w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego,w.koniec_wolnego, w.liczba_dni, w.imie_dziecka, w.data_urodzenia_dziecka, s.nazwa, w.data_kierownik,w.data_hr,w.id as id, k.nazwa as nazwakategorii,
         w.liczba_dni_eksport,w.data_odrobienia, w.login_kierownik,w.numer_ewidencyjny, w.dane_mpk, os.imie ,os.nazwisko,w.numer_ewidencyjny_wpisujacy, w.osoba_wpisujaca,w.przyczyna,w.stopien_pokrewienstwa, w.adres_zamieszkania, w.profil,p.nazwa as nazwa_profilu
        FROM wniosek w
        LEFT JOIN status s on w.id_status=s.id
        LEFT JOIN kategoria k on w.id_kategorii=k.id
        LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on w.profil=p.id    
        WHERE DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_urlopy'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;

        $zapytanieexcel = "SELECT   w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego,w.koniec_wolnego, w.liczba_dni, w.imie_dziecka, w.data_urodzenia_dziecka, s.nazwa, w.data_kierownik,w.data_hr,w.dane_mpk,w.id as id, k.nazwa as nazwakategorii,
        w.data_odrobienia, w.liczba_dni_eksport,w.login_kierownik,k.kod,w.id_status, w.id_kategorii,w.numer_ewidencyjny,os.imie ,os.nazwisko,w.numer_ewidencyjny_wpisujacy, w.osoba_wpisujaca,k.kod_enova, w.profil,p.nazwa as nazwa_profilu, w.liczba_godzin_enova
        ,(SELECT sg_w.stan_urlopu FROM stan_godzin sg_w where w.numer_ewidencyjny=sg_w.numer_ewidencyjny  order by id desc LIMIT 1) as typ_umowy
        FROM wniosek w
        LEFT JOIN status s on w.id_status=s.id
        LEFT JOIN kategoria k on w.id_kategorii=k.id
        LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on w.profil=p.id 
        WHERE DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_urlopy'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "" ;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT   w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego,w.koniec_wolnego, w.liczba_dni, w.imie_dziecka, w.data_urodzenia_dziecka, s.nazwa, w.data_kierownik,w.data_hr,w.dane_mpk,w.id as id, k.nazwa as nazwakategorii,
         w.liczba_dni_eksport,w.data_odrobienia,w.numer_ewidencyjny,os.imie ,os.nazwisko,w.numer_ewidencyjny_wpisujacy,  w.osoba_wpisujaca, w.profil,p.nazwa as nazwa_profilu,w.liczba_godzin_enova
        FROM wniosek w
        LEFT JOIN status s on w.id_status=s.id
        LEFT JOIN kategoria k on w.id_kategorii=k.id
        LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on w.profil=p.id  
        WHERE DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(w.poczatek_wolnego,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_urlopy'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "   " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        $this->SprawdzZastepceStanGodzin($_SESSION['ewnioski_login'],$_SESSION['ewnioski_full_name']);

        $ZapytaniePracaZdalnaExcel = "SELECT
        w.id,
        REPLACE(REPLACE(w.id_kategorii,'17', 'ZDALNA_OK'),'18','ZDALNA_HYB') as SymbolAbsencji,
        CONCAT(w.poczatek_pracy_zdalnej, ' 00:00:00.000') as DataOd,
        CONCAT(w.koniec_pracy_zdalnej, ' 00:00:00.000') as DataDo,
        CONCAT(CURRENT_DATE(),' 00:00:00.000') as DataRozliczenia,
        '' as Dni,
        '' as DniRob,
        '' as GodzRob,
        '0' as NaZadanie,
        w.numer_ewidencyjny as NumerEwidencyjny,
        w.data_wpisania,
        w.imie_nazwisko,
        w.poczatek_pracy_zdalnej,
        w.koniec_pracy_zdalnej,
        w.login_kierownik,
        w.data_kierownik,
        CASE 
            WHEN LENGTH(w.numer_ewidencyjny) = 5 THEN 'Starachowice'
            ELSE 'Tczew'
        END    
        as zaklad,
        w.login_kierownik,
        w.data_hr,
        s.nazwa
        FROM zwolnienie w
        LEFT JOIN `status` s ON(s.id=w.id_status) 
        WHERE id_kategorii IN ('17','18')
        AND w.poczatek_pracy_zdalnej >= DATE('".$_SESSION['ewnioski_data_poczatkowa']."')
        AND w.poczatek_pracy_zdalnej <= DATE('".$_SESSION['ewnioski_data_koncowa']."')
        AND w.profil='".$_SESSION['ewnioski_profil']."'
        
        ";

        $ZapytaniePracaZdalnaExcelAkceptacjaHr = $ZapytaniePracaZdalnaExcel." AND w.id_status='3'";
        $ZapytaniePracaZdalnaExcelWniosekWRCP = $ZapytaniePracaZdalnaExcel." AND w.id_status='9'";
        ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php

            if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false))
                {?>
                     <form method="POST" action="raporty.php?urlop" id="form_excel" >
                     <input type="hidden" name="zapytanie_excel" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="hidden" name="zapytanie_praca_zdalna_excel" value="<?php echo $this->ZapamietajZapytanie($ZapytaniePracaZdalnaExcelAkceptacjaHr); ?>">
                     <input type="hidden" name="data_poczatkowa" value="<?php echo $_SESSION['ewnioski_data_poczatkowa'];?>">
                     <input type="hidden" name="data_koncowa" value="<?php echo $_SESSION['ewnioski_data_koncowa'];?>">
                     <input type="button" value="Eksport do SaturnNET" onclick="document.getElementById('form_excel').submit();" />
                     </form>
                     <br><br>
                      <form method="POST" action="raporty.php?urlop" id="form_excel_widoczne" >
                     <input type="hidden" name="zapytanie_excel_widoczne" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="hidden" name="zapytanie_praca_zdalna_excel" value="<?php echo $this->ZapamietajZapytanie($ZapytaniePracaZdalnaExcelWniosekWRCP); ?>">
                     <input type="hidden" name="data_poczatkowa" value="<?php echo $_SESSION['ewnioski_data_poczatkowa'];?>">
                     <input type="hidden" name="data_koncowa" value="<?php echo $_SESSION['ewnioski_data_koncowa'];?>">
                     <input type="button" value="Eksport do XLSX" onclick="document.getElementById('form_excel_widoczne').submit();" />
                     </form>
                     <br>
                      <form method="POST" action="raporty.php?urlop" id="form_excel2" >
                     <input type="hidden" name="zapytanie_excel2" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="hidden" name="zapytanie_praca_zdalna_excel" value="<?php echo $this->ZapamietajZapytanie($ZapytaniePracaZdalnaExcelWniosekWRCP); ?>">
                     <input type="hidden" name="data_poczatkowa" value="<?php echo $_SESSION['ewnioski_data_poczatkowa'];?>">
                     <input type="hidden" name="data_koncowa" value="<?php echo $_SESSION['ewnioski_data_koncowa'];?>">
                     <input type="button" value="Eksport do SaturnNET ze statusem wniosek w RCP" onclick="document.getElementById('form_excel2').submit();" />
                     </form>
                     <br>
                       <form method="POST" action="raporty.php?urlop" id="form_excel3" >
                     <input type="hidden" name="zapytanie_excel2_enova" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="hidden" name="zapytanie_praca_zdalna_excel" value="<?php echo $this->ZapamietajZapytanie($ZapytaniePracaZdalnaExcelWniosekWRCP); ?>">
                     <input type="hidden" name="data_poczatkowa" value="<?php echo $_SESSION['ewnioski_data_poczatkowa'];?>">
                     <input type="hidden" name="data_koncowa" value="<?php echo $_SESSION['ewnioski_data_koncowa'];?>">
                     <input type="button" value="Eksport do Enovy ze statusem wniosek w RCP tylko pracownicy PKC" onclick="document.getElementById('form_excel3').submit();" />
                     </form>
                     <br>
                     <?php
                }
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszUrlopy($liczba_rekordow,$widocznosc);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneUrlpoy($wykonaj, $liczba_rekordow,$widocznosc);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszUrlopy($liczba,$widocznosc)
    {
         $polaczenie = $this->MysqliConnect();
$colspan1=0;
      foreach ($widocznosc as $wartosc )
          {
              if($wartosc<22 and $wartosc!='') $colspan1++;
          }
          $colspan1=22-$colspan1;
      $colspan2=2;
      foreach ($widocznosc as $wartosc )
          {

              if($wartosc=='22') $colspan2--;
              if( $wartosc=='23'  ) $colspan2--;
              if($wartosc=='45') $colspan2--;
          }
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td colspan="<?php echo $colspan1;?>" align="center" class="padding_5_all bg_11 bottom right">
                    <b class="inline">Modyfikacja globalna następujących pozycji: <span id="modyfikacje"></span></b>
                </td>
                 <?php
                 if (array_search('18', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                <?php if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)!==false) { ?>
                    <form method="POST" id="modyfikacja_globalna_hr">
                        <input type="hidden" name="id_global_hr" id="id_global_hr" value=""/>
                        <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('modyfikacja_globalna_hr').submit();"/>
                    </form>

                    <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość tej akceptacji maja tylko pracownicy działu HR"/> <?php
                        }?>
                </td>
                 <?php
                 } if (array_search('19', $widocznosc) === false) {
                    ?>
                  <td align="center" class="padding_5_all bg_11 bottom right">
                  </td>
                   <?php
                 } if (array_search('20', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                  <?php if($_SESSION['ewnioski_stanowisko']=='kierownicze' ) { ?>
                    <form method="POST" id="modyfikacja_globalna_kierownik">
                        <input type="hidden" name="id_global_kierownik" id="id_global_kierownik" value=""/>
                        <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('modyfikacja_globalna_kierownik').submit();"/>
                    </form>
                      <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość globalnej akceptacji maja tylko osoby ze stanowiskiem kierowniczym"/> <?php
                        }?>
                </td>
                 <?php
                 } if ($colspan2!='0') {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom right" colspan="<?php echo $colspan2?>">
                </td>
                 <?php
                 }
                    ?>
            </tr>
            <tr>
                <?php if (array_search('1', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                 <?php
                 } if (array_search('2', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                   <?php
                 } if (array_search('3', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Wybór globalny.</b>
                </td>
                   <?php
                 } if (array_search('4', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.data_wpisania&raport=urlopy">
                        <b>Data wpisania wniosku</b></a>
                </td>
                   <?php
                 } if (array_search('5', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.imie_nazwisko&raport=urlopy">
                        <b>Imię i nazwisko</b></a>
                </td>
                   <?php
                 } if (array_search('6', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.numer_ewidencyjny&raport=urlopy">
                        <b> Numer ewidencyjny </b></a>
                </td>
                   <?php
                 }
                 if (array_search('44', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.numer_ewidencyjny_wpisujacy&raport=urlopy">
                        <b> Numer wpisujacego </b></a>
                </td>
                   <?php
                 }

                 if (array_search('7', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.poczatek_wolnego&raport=urlopy">
                        <b>Nieobecność od </b></a>
                </td>
                   <?php
                 } if (array_search('8', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.koniec_wolnego&raport=urlopy">
                        <b>Nieobecność do </b></a>
                </td>
                   <?php
                 } if (array_search('9', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.koniec_wolnego&raport=urlopy">
                        <b>Data odpracowania</b></a>
                </td>
                   <?php
                 } if (array_search('10', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.liczba_dni_eksport&raport=urlopy">
                        <b>Liczba dni urlopu wnioskowanego</b></a>
                </td>
                   <?php
                 } if (array_search('11', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=k.nazwa&raport=urlopy">
                        <b>Rodzaj nieobecności </b></a>
                </td>
                   <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.imie_dziecka&raport=urlopy">
                        <b> Imię dziecka/ Imię nazwisko</b></a>
                </td>
                   <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.data_urodzenia_dziecka&raport=urlopy">
                        <b>Data urodzenia dziecka </b></a>
                </td>
                <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.	przyczyna&raport=urlopy">
                        <b> Przyczyna konieczności</b></a>
                </td>
                <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.stopien_pokrewienstwa&raport=urlopy">
                        <b> Stopień pokrewieństwa</b></a>
                </td>
                <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.adres_zamieszkania&raport=urlopy">
                        <b> Adres zamieszkania</b></a>
                </td>

   <?php
                 } if (array_search('14', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.login_kierownik&raport=urlopy">
                        <b> Osoba akceptująca</b></a>
                </td>
                   <?php
                 } if (array_search('15', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.data_kierownik&raport=urlopy">
                        <b>Data akceptacji</b></a>
                </td>
                <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.profil&raport=urlopy">
                        <b>Zakład</b></a>
                </td>
                   <?php
                 } if (array_search('16', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=os.nazwisko&raport=urlopy">
                        <b>Przełożony</b></a>
                </td>
                   <?php
                 } if (array_search('17', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=s.nazwa&raport=urlopy">
                        <b>Status wniosku</b></a>
                </td>
                   <?php
                 } if (array_search('18', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=w.data_hr&raport=urlopy">
                        <b> Pobrane przez HR</b></a>
                </td>
                   <?php
                 } if (array_search('19', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Wygeneruj wniosek PDF </b>
                </td>
                   <?php
                 } if (array_search('20', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Zaakceptuj wniosek  </b>
                </td>
                   <?php
                 } if (array_search('21', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Anuluj wniosek  </b>
                </td>
                   <?php
                 } if (array_search('22', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Odrzuć wniosek  </b>
                </td>
                   <?php }  ?>
            </tr>
            <tr>
               <?php
                 if (array_search('1', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('2', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('3', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input id="checkbox_calosc<?php echo $liczba; ?>" name="<?php echo $liczba; ?>" type='checkbox'
                               onclick="IdCheckbox('wszystko', <?php echo $liczba; ?>);"
                               value='<?php echo $liczba; ?>'/>
                    </td>
                       <?php
                 } if (array_search('4', $widocznosc) === false) {
                    ?>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_data_wpisania" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_data_wpisania'])) echo trim($_SESSION['ewnioski_data_wpisania']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('5', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_imieinazwisko" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_imieinazwisko'])) echo trim($_SESSION['ewnioski_imieinazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('6', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_numer_ewidencyjny" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 }
                  if (array_search('44', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_numer_ewidencyjny_wpisujacy" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy'])) echo trim($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('7', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_poczatek_wolnego" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_poczatek_wolnego'])) echo trim($_SESSION['ewnioski_poczatek_wolnego']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('8', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_koniec_wolnego" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_koniec_wolnego'])) echo trim($_SESSION['ewnioski_koniec_wolnego']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('9', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_data_odrobienia" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_data_odrobienia'])) echo trim($_SESSION['ewnioski_data_odrobienia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('10', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_liczba_dni_eksport" id="filter_id" size="5" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_liczba_dni_eksport'])) echo trim($_SESSION['ewnioski_liczba_dni_eksport']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('11', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_1">
                    <select name="ewnioski_kategoria" onchange="document.forms['form_f_1'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM kategoria  where typ=1 order by nazwa ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_kategoria'] == $wiersz['nazwa']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['nazwa']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                   <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_imie_dziecka" id="filter_id" size="20" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_imie_dziecka'])) echo trim($_SESSION['ewnioski_imie_dziecka']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('14', $widocznosc) === false) {
                    ?>
                <td   align="center" class="padding_5_all bg_11 bottom right">
                  <form method="POST">
                        <input type="text" name="ewnioski_akcpetujacy" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_akcpetujacy'])) echo trim($_SESSION['ewnioski_akcpetujacy']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                    </td>
                       <?php
                 } if (array_search('15', $widocznosc) === false) {
                    ?>
                    <td   align="center" class="padding_5_all bg_11 bottom right">
                  <form method="POST">
                        <input type="date" name="ewnioski_status_kierownik" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_status_kierownik'])) echo trim($_SESSION['ewnioski_status_kierownik']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                    </td><?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                    <?php  if($_SESSION['ewnioski_zmiana_profilu']==1)
                          {
                              ?>
                 <form method="POST" id="form_f_4">
                    <select name="ewnioski_profil" onchange="document.forms['form_f_4'].submit();">
                          <?php
                        $q1 = "SELECT id,nazwa FROM  profil  ORDER BY nazwa ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_profil'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                <?php
                }
                else
                    {
                         ?><img src="../img/remove_filter.png"/><?php
                    }
                ?>
                </td>
                       <?php
                 } if (array_search('16', $widocznosc) === false) {
                    ?>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_3">
                    <select name="ewnioski_dane_mpk" onchange="document.forms['form_f_3'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT DISTINCT (os.numer_ewidencyjny), os.imie, os.nazwisko FROM  wniosek w LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny ORDER BY os.nazwisko ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_dane_mpk'] == $wiersz['nazwisko']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['nazwisko']; ?>"><?php echo $wiersz['nazwisko'].' '.$wiersz['imie']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                   <?php
                 } if (array_search('17', $widocznosc) === false) {
                    ?>
                      <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_2">
                    <select name="ewnioski_status" onchange="document.forms['form_f_2'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM status ORDER BY nazwa ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_status'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                   <?php
                 } if (array_search('18', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                     <form method="POST">
                        <input type="date" name="ewnioski_status_hr" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_status_hr'])) echo trim($_SESSION['ewnioski_status_hr']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                   <?php
                 } if (array_search('19', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('20', $widocznosc) === false) {
                    ?>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('21', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 } if (array_search('22', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <?php
                 }
                    ?>
            </tr>

        <?php
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneUrlpoy($wykonaj, $liczba,$widocznosc)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
        $suma=$suma+$dane['liczba_dni_eksport'];
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
             <?php
                  if (array_search('1', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <?php
                 } if (array_search('2', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['id']; ?>
            </td>
              <?php
                 } if (array_search('3', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
            <?php
            $disabled="disabled";
               $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.dane_zastepcy LIKE '%".$_SESSION['ewnioski_login']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
             if (($dane['nazwa']=='wpisany' and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']) or $danespr!==null) and strtoupper($_SESSION['ewnioski_login'])!=$dane['numer_ewidencyjny'])or $_SESSION['ewnioski_admin']=='1') {
             $disabled="";
             }
 ?>
                        <input id="checkbox_<?php echo $licznik; ?>" name="<?php echo $dane['id'] ?>"
                               type='checkbox' <?php echo $disabled ?> onclick="IdCheckbox('<?php echo $licznik; ?>','<?php echo $liczba; ?>')"
                               value='<?php echo $dane['id']; ?>'/>
            </td>
              <?php
                 } if (array_search('4', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <div title=" <?php echo $dane['osoba_wpisujaca'];?>"> <?php echo $dane['data_wpisania']; ?></div>
            </td>
              <?php
                 } if (array_search('5', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie_nazwisko']; ?>
            </td>
              <?php
                 } if (array_search('6', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
              <?php
                 }     if (array_search('44', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny_wpisujacy']; ?>
            </td>
              <?php
                 }if (array_search('7', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['poczatek_wolnego']; ?>
            </td>
              <?php
                 } if (array_search('8', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['koniec_wolnego']; ?>
            </td>
              <?php
                 } if (array_search('9', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_odrobienia']; ?>
            </td>
              <?php
                 } if (array_search('10', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['liczba_dni_eksport']; ?>
            </td>
              <?php
                 } if (array_search('11', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwakategorii']; ?>
            </td>
              <?php
                 } if (array_search('12', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie_dziecka']; ?>
            </td>
              <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_urodzenia_dziecka']; ?>
            </td>
            <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['przyczyna']; ?>
            </td>
            <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['stopien_pokrewienstwa']; ?>
            </td>
            <?php
                 } if (array_search('13', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['adres_zamieszkania']; ?>
            </td>
              <?php
                 } if (array_search('14', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['login_kierownik']; ?>
            </td>
              <?php
                 } if (array_search('15', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_kierownik']; ?>
            </td>
               <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa_profilu']; ?>
            </td>
              <?php
                 } if (array_search('16', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko'].' '.$dane['imie']; ?>
            </td>
              <?php
                 } if (array_search('17', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>
              <?php
                 } if (array_search('18', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_hr'];?>
            </td>
              <?php
                 } if (array_search('19', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <form method="POST" id="id_wiazki_<?php echo $dane['id']; ?>"/>
                    <input type="hidden" name="wydruk_urlop" value="<?php echo $dane['id']; ?>"/>
                    <input type="hidden" name="wydruk_przyczyna" value="<?php echo $dane['przyczyna']; ?>"/>
                    <input type="hidden" name="wydruk_stopien" value="<?php echo $dane['stopien_pokrewienstwa']; ?>"/>
                    <input type="hidden" name="wydruk_adres" value="<?php echo $dane['adres_zamieszkania']; ?>"/>
                    <input type="button" value="" class="drukowanie_guzik"
                           onclick="document.getElementById('id_wiazki_<?php echo $dane['id']; ?>').submit()"/>
                    </form>
            </td>
              <?php
                 } if (array_search('20', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
            <?php
            $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
               if (($dane['nazwa']=='wpisany' and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']) or $danespr!==null) and strtoupper($_SESSION['ewnioski_login'])!=$dane['numer_ewidencyjny'])or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="akcptuj_<?php echo $licznik; ?>">
                            <input type="hidden" name="akcptuj_id_ewniosek" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('akcptuj_<?php echo $licznik; ?>').submit();"/>
                        </form>
                        <?php }
                        else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość akceptacji maja tylko wnioski ze statusem wpisany"/> <?php
                        }?>
            </td>
              <?php
                 } if (array_search('21', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
             $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.dane_zastepcy LIKE '%".$_SESSION['ewnioski_full_name']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
             if ((($dane['nazwa']=='wpisany' or $dane['nazwa']=='zaakceptowany przez przełożonego')and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']) or $danespr!==null)) or ($dane['nazwa']=='wpisany' and strtoupper($dane['numer_ewidencyjny_wpisujacy'])==strtoupper($_SESSION['ewnioski_login'])) or ($dane['nazwa']=='zaakceptowany przez przełożonego' and strtoupper($dane['login_kierownik'])==strtoupper($_SESSION['ewnioski_full_name']) )or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="delete_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('delete_<?php echo $licznik; ?>', 'Czy napewno chcesz cofnąc urlop z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>
              <?php
                 } if (array_search('22', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
              $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.dane_zastepcy LIKE '%".$_SESSION['ewnioski_full_name']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
             if ((($dane['nazwa']=='wpisany' )and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login'])or $danespr!==null)) or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="cofnij_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik_anuluj" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="zakaz_guzik"
                                   onclick="CzyNaPewno('cofnij_<?php echo $licznik; ?>', 'Czy napewno nie wyrażasz zgody na urlop z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>
              <?php
                 }
                    ?>

</tr>
            <?php
            $licznik++;
        }
        ?>
        <tr>
            <td align="center" valign="middle" class="low_menu_4 up font padding_5_all  right" colspan="10">
            <h3>Suma widocznego urlopu</h3>
            </td>
             <td align="center" valign="middle" class="low_menu_4 up font padding_5_all  right" colspan="1">
              <h3><?php echo $suma;?></h3>
            </td>
            <td align="center" valign="middle" class="low_menu_4 up font padding_5_all  " colspan="16">
            </td>
        </tr>
        <?php
mysqli_close($polaczenie);
    }
       /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function RaportUrlopowDotyk()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];

        $zapytanie = "SELECT  w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego,w.koniec_wolnego, w.liczba_dni, w.imie_dziecka, w.data_urodzenia_dziecka, s.nazwa, w.data_kierownik,w.data_hr,w.id as id, k.nazwa as nazwakategorii,
 w.liczba_dni_eksport,w.data_odrobienia, w.login_kierownik,w.numer_ewidencyjny, w.dane_mpk, os.imie ,os.nazwisko,w.numer_ewidencyjny_wpisujacy, w.osoba_wpisujaca
FROM wniosek w
LEFT JOIN status s on w.id_status=s.id
LEFT JOIN kategoria k on w.id_kategorii=k.id
LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny
WHERE  w.numer_ewidencyjny='".$_SESSION['ewnioski_login']."'
            ORDER BY " . $_SESSION['ewnioski_sortowanie_dotyk_urlop'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek_dotyk_urlop'] . "   LIMIT " . $limit;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT   w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego,w.koniec_wolnego, w.liczba_dni, w.imie_dziecka, w.data_urodzenia_dziecka, s.nazwa, w.data_kierownik,w.data_hr,w.dane_mpk,w.id as id, k.nazwa as nazwakategorii,
 w.liczba_dni_eksport,w.data_odrobienia,w.numer_ewidencyjny,os.imie ,os.nazwisko,w.numer_ewidencyjny_wpisujacy,  w.osoba_wpisujaca
FROM wniosek w
LEFT JOIN status s on w.id_status=s.id
LEFT JOIN kategoria k on w.id_kategorii=k.id
LEFT JOIN ewidencja_szkolen.osoby os on w.dane_mpk=os.numer_ewidencyjny
WHERE w.numer_ewidencyjny='".$_SESSION['ewnioski_login']."'
            ORDER BY data_wpisania " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);

        // Sprawdzanie zastępcy wyłaczone 18.02.2026
        //$this->SprawdzZastepceStanGodzin($_SESSION['ewnioski_login'],$_SESSION['ewnioski_full_name']);

        ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszUrlopyDotyk($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneUrlopyDotyk($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszUrlopyDotyk($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.data_wpisania">
                        <b>Data wpisania wniosku</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.imie_nazwisko">
                        <b>Imię i nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.numer_ewidencyjny">
                        <b> Numer ewidencyjny </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.poczatek_wolnego">
                        <b>Nieobecność od </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.koniec_wolnego">
                        <b>Nieobecność do </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.koniec_wolnego">
                        <b>Data odpracowania</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.liczba_dni_eksport">
                        <b>Liczba dni urlopu wnioskowanego</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=k.nazwa">
                        <b>Rodzaj nieobecności </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.imie_dziecka">
                        <b> Imię dziecka</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.data_urodzenia_dziecka">
                        <b>Data urodzenia dziecka </b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.login_kierownik">
                        <b> Osoba akceptująca</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=w.data_kierownik">
                        <b>Data akceptacji</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=os.nazwisko">
                        <b>Przełożony</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_urlopow&sort=s.nazwa">
                        <b>Status wniosku</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Anuluj wniosek  </b>
                </td>
            </tr>


        <?php
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneUrlopyDotyk($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
        $suma=$suma+$dane['liczba_dni_eksport'];
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <div title=" <?php echo $dane['osoba_wpisujaca'];?>"> <?php echo $dane['data_wpisania']; ?></div>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie_nazwisko']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['poczatek_wolnego']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['koniec_wolnego']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_odrobienia']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['liczba_dni_eksport']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwakategorii']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie_dziecka']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_urodzenia_dziecka']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['login_kierownik']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_kierownik']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko'].' '.$dane['imie']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
             $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.dane_zastepcy LIKE '%".$_SESSION['ewnioski_full_name']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
             if ((($dane['nazwa']=='wpisany' or $dane['nazwa']=='zaakceptowany przez przełożonego')and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']) or $danespr!==null)) or ($dane['nazwa']=='wpisany' and strtoupper($dane['numer_ewidencyjny_wpisujacy'])==strtoupper($_SESSION['ewnioski_login'])) or ($dane['nazwa']=='zaakceptowany przez przełożonego' and strtoupper($dane['login_kierownik'])==strtoupper($_SESSION['ewnioski_full_name']) )or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="delete_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik" value="<?php echo $dane['id']; ?>"/>
                            <input type="hidden" name="dotyk"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('delete_<?php echo $licznik; ?>', 'Czy napewno chcesz cofnąc urlop z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>
              <?php
                    ?>

</tr>
            <?php
            $licznik++;
        }
        ?>

        <?php
mysqli_close($polaczenie);
    }
    /**
* Funkacja odpowiedzialan za zmiane statusu wniosku urlopowego
 */
    function ZaakceptujUrlop()
    {
       $polaczenie = $this->MysqliConnect();
       $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.poczatek_wolnego, w.koniec_wolnego
FROM wniosek w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['akcptuj_id_ewniosek']."'";
       $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
       $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
       $wynik=mysqli_fetch_row($wykonajspr);
                     if($wynik[2]==2)
                         {
                              $status="id_status='8',";
                         }
                         else $status="id_status='2',";

        $zapytanie = "UPDATE wniosek SET $status login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['akcptuj_id_ewniosek'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
        mysqli_close($polaczenie);
 if ($wynik[4] != '') {
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . " - " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek urlopowy w przedziale czasowym  <br> $przedzial.<br>
                Został zaakceptowany przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Akceptacja wniosku urlopowego', $page);
                        }
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Zaakceptowano wniosek urlopowy.</font></h1>';
        $this->Przekieruj('raporty.php?urlop');
    }
/**
* Funkacja odpowiedzialan za zmiane statusu wniosku urlopowego w przypadku wybrania kilku wniosków
 */
    function ZaakceptujUrlopGlobalnie()
    {
        $polaczenie = $this->MysqliConnect();
        $wnioski=explode(',',$_POST['id_global_kierownik']);
       $zmienione='';
       $niezmienione='';
        for ($i = 0; $i < count($wnioski); $i++) {
            $zapytanie="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.poczatek_wolnego, w.koniec_wolnego
FROM wniosek w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='$wnioski[$i]'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
            if($wynik[1]==1)
                {
                     $zmienione=$zmienione.$wynik[0].', ';
                        if($wynik[2]==2)
                         {
                              $status="id_status='8',";
                         }
                         else $status="id_status='2',";

                     $zapytaniezmien = "UPDATE wniosek SET $status login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $wynik[0] . "' ";
                     $wykonaj = mysqli_query($polaczenie, $zapytaniezmien);
                     $this->BladZapytania($polaczenie, $zapytaniezmien, $_SESSION['ewnioski_login']);
                        $this->ZapiszLog('Zapytanie;' . $zapytaniezmien, $_SESSION['ewnioski_login']);
                         if ($wynik[4] != '') {
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . " - " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek urlopowy w przedziale czasowym  <br> $przedzial.<br>
                Został zaakceptowany przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop >eWniosków</a>");
                            $this->Wyslij($adresat, 'Akceptacja wniosku urlopowego', $page);
                        }
                }
                else{
                    $niezmienione=$niezmienione.$wynik[0].', ';
                }
        }
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Następujące wnioski urlopowe zostały zaakceptowane: '.$zmienione.'.</font></h1>';
        if($niezmienione!='')
            {
                $_SESSION['ewnioski_komunikat'] = '<h1><font color="red">Następujące wnioski nie zostały zaakceptowane ze względu na inny status niż wpisany: '.$niezmienione.'.</font></h1><br>'.$_SESSION['ewnioski_komunikat'];
            }
        $this->Przekieruj('raporty.php?urlop');
    }
    /**
* Funkacja odpowiedzialan za zmiane statusu wniosku urlopowegoprzez dział HR w przypadku wybrania kilku wniosków
 */
    function ZaakceptujUrlopGlobalnieHR()
    {
        $polaczenie = $this->MysqliConnect();
        $wnioski=explode(',',$_POST['id_global_hr']);
       $zmienione='';
       $niezmienione='';
        for ($i = 0; $i < count($wnioski); $i++) {
            $zapytanie="SELECT id, id_status, id_kategorii FROM wniosek where id='$wnioski[$i]'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
            if($wynik[1]==2 or $wynik[1]==8)
                {
                     $zmienione=$zmienione.$wynik[0].', ';
                     $zapytaniezmien = "UPDATE wniosek SET  id_status='3',  login_hr='". $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id='" . $wynik[0] . "' ";
                     $wykonaj = mysqli_query($polaczenie, $zapytaniezmien);
                     $this->BladZapytania($polaczenie, $zapytaniezmien, $_SESSION['ewnioski_login']);
                     $this->ZapiszLog('Zapytanie;' . $zapytaniezmien, $_SESSION['ewnioski_login']);
                }
                else{
                    $niezmienione=$niezmienione.$wynik[0].', ';
                }
        }
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Następujące wnioski urlopowe zostały zaakceptowane: '.$zmienione.'.</font></h1>';
        if($niezmienione!='')
            {
                $_SESSION['ewnioski_komunikat'] = '<h1><font color="red">Następujące wnioski nie zostały zaakceptowane ze względu na inny status niż wpisany: '.$niezmienione.'.</font></h1><br>'.$_SESSION['ewnioski_komunikat'];
            }
        $this->Przekieruj('raporty.php?urlop');
    }
       /**
* Funkacja odpowiedzialan za zmiane statusu wniosku urlopowego
 */
    function CofnijUrlop()
    {
         if(isset($_POST['dotyk'])) $strona='dotyk.php?raport_urlopow';
        else $strona='raporty.php?urlop';
       $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE wniosek SET id_status='6', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['id_cofnij_kierownik'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
  $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.poczatek_wolnego, w.koniec_wolnego
FROM wniosek w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['id_cofnij_kierownik']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytaniespr);
            $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Anulowano wniosek urlopowy.</font></h1>';
         if ($wynik[4] != '') {
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . " - " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek urlopowy w przedziale czasowym  <br> $przedzial.<br>
                Został anulowany przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Anulowanie wniosku urlopowego', $page);
                        }
        $this->Przekieruj($strona);
    }
      /**
* Funkacja odpowiedzialan za zmiane statusu wniosku urlopowego
 */
    function AnulujUrlop()
    {
       $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE wniosek SET id_status='4', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['id_cofnij_kierownik_anuluj'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
 $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.poczatek_wolnego, w.koniec_wolnego
FROM wniosek w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['id_cofnij_kierownik_anuluj']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytaniespr);
            $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Odrzucono wniosek urlopowy.</font></h1>';
         if ($wynik[4] != '') {
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . " - " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek urlopowy w przedziale czasowym  <br> $przedzial.<br>
                Został odrzucony przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Odrzucenie wniosku urlopowego', $page);
                        }
        $this->Przekieruj('raporty.php?urlop');
    }
      /**
     * Raport zwolnień wpisanych przez użytkownika
     */
    function RaportZwolnien()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('z.data_wpisania', 'z.imie_nazwisko', 'z.numer_ewidencyjny', 'z.liczba_godzin','z.data_zwolnienia','z.data_odpracowania','z.poczatek_pracy','z.przedzial_czasowy','k.nazwa', 'z.id_status','z.dane_mpk','z.data_kierownik','z.data_hr','z.numer_ewidencyjny_wpisujacy','z.profil');
        $array2 = array('ewnioski_data_wpisania_zwolnienie', 'ewnioski_imieinazwisko_zwolnienie', 'ewnioski_numer_ewidencyjny_zwolnienie', 'ewnioski_poczatek_wolnego_zwolnienie', 'ewnioski_koniec_wolnego_zwolnienie','ewnioski_data_odrobienia_zwolnienie','ewnioski_liczba_dni_zwolnienie',
        'ewnioski_liczba_dni_eksport_zwolnienie','ewnioski_kategoria_zwolnienie', 'ewnioski_status_zwolnienie','ewnioski_dane_mpk_zwolnienie','ewnioski_status_kierownik_zwolnienie','ewnioski_status_hr_zwolnienie','ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie','ewnioski_profil');
        $widocznosc = array();
        $warunek='';
        $widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
               if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false) and array_search('43', $widocznosc) === false) {
         $dodatkowy_warunek=" and z.profil='".$_SESSION['ewnioski_profil']."'";
        }
        else
        {

$zapytaniespr="SELECT o.numer_ewidencyjny FROM ewidencja_szkolen.osoby o
 WHERE o.numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
           while ($danespr=mysqli_fetch_array($wykonajspr))
                  {
                      $warunek.="or z.dane_mpk='".$danespr[0]."'";
                  }

if($warunek=='')  $dodatkowy_warunek = "and (z.numer_ewidencyjny_wpisujacy='".$_SESSION['ewnioski_login']."' or z.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or z.dane_mpk='".$_SESSION['ewnioski_login']."')";
else
    {
        $dodatkowy_warunek = "and (z.numer_ewidencyjny_wpisujacy='".$_SESSION['ewnioski_login']."' or z.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' $warunek or z.dane_mpk='".$_SESSION['ewnioski_login']."')";
    } }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                if($array2[$i]=='ewnioski_profil')
                    {
                       if($_SESSION['ewnioski_zmiana_profilu']!=0)
                            {
                            if($_SESSION[$array2[$i]]!=999) $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                        }
                    }
                else $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $zapytanie = "SELECT z.id as id, z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.poczatek_pracy, k.nazwa, s.nazwa as status_wniosku,z.dane_mpk, z.data_kierownik, z.data_hr, z.przedzial_czasowy, os.imie ,os.nazwisko,z.numer_ewidencyjny_wpisujacy, z.login_kierownik, z.osoba_wpisujaca,p.nazwa as nazwa_profilu
        FROM zwolnienie z 
        LEFT JOIN status s on z.id_status=s.id 
        LEFT JOIN kategoria k on z.id_kategorii=k.id 
        LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on z.profil=p.id    
        WHERE DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_zwolnienia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;

        $zapytanieexcel_z_praca_zdalna = "SELECT   z.id as id, z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.poczatek_pracy, k.nazwa, s.nazwa as status_wniosku,z.dane_mpk, z.data_kierownik, z.data_hr, z.przedzial_czasowy, os.imie ,os.nazwisko,z.numer_ewidencyjny_wpisujacy, z.login_kierownik,p.nazwa as nazwa_profilu
        FROM zwolnienie z 
        LEFT JOIN status s on z.id_status=s.id 
        LEFT JOIN kategoria k on z.id_kategorii=k.id 
        LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on z.profil=p.id    
        WHERE DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' 
        and DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."' ".$dodatkowy_warunek;

        $zapytanieexcel = $zapytanieexcel_z_praca_zdalna." AND id_kategorii!='17' AND id_kategorii!='18' ";
        $zapytanieexcel_z_praca_zdalna .= " ORDER BY ".$_SESSION['ewnioski_sortowanie_zwolnienia'].' '. $_SESSION['ewnioski_sortowanie_kierunek'];
        $zapytanieexcel .= " ORDER BY " . $_SESSION['ewnioski_sortowanie_zwolnienia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'];

        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);

        $zapytanieliczba = "SELECT   z.id as id, z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.poczatek_pracy, k.nazwa, s.nazwa as status_wniosku,z.dane_mpk, z.data_kierownik, z.data_hr, z.przedzial_czasowy, os.imie ,os.nazwisko,z.numer_ewidencyjny_wpisujacy, z.login_kierownik,p.nazwa as nazwa_profilu
        FROM zwolnienie z 
        LEFT JOIN status s on z.id_status=s.id 
        LEFT JOIN kategoria k on z.id_kategorii=k.id 
        LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny
        LEFT JOIN  profil p on z.profil=p.id    
        WHERE DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(z.data_wpisania,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_zwolnienia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "   " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false))
                {?>
                <br>
                     <form method="POST" action="raporty.php?zwolnienia" id="form_excel" >
                     <input type="hidden" name="zapytanie_excel_zwolnienia" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="button" value="Eksport do RCP widocznych danych" onclick="document.getElementById('form_excel').submit();" />
                     </form>
                     <br>
                      <form method="POST" action="raporty.php?zwolnienia" id="form_excel1" >
                     <input type="hidden" name="zapytanie_excel_widok" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel_z_praca_zdalna); ?>">
                     <input type="button" value="Eksport do Excela widocznych danych" onclick="document.getElementById('form_excel1').submit();" />
                     </form>
                     <br>
                     <?php
                }

        ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszZwolnienia($liczba_rekordow,$widocznosc);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneZwolnienia($wykonaj, $liczba_rekordow,$widocznosc);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
      /**
     * Raport zwolnień dla monitora dotykowego wpisanych przez użytkownika
     */
    function RaportZwolnienDotyk()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];

        $zapytanie = "SELECT z.id as id, z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.poczatek_pracy, k.nazwa, s.nazwa as status_wniosku,z.dane_mpk, z.data_kierownik, z.data_hr, z.przedzial_czasowy, os.imie ,os.nazwisko,z.numer_ewidencyjny_wpisujacy, z.login_kierownik, z.osoba_wpisujaca
FROM zwolnienie z 
LEFT JOIN status s on z.id_status=s.id 
LEFT JOIN kategoria k on z.id_kategorii=k.id 
LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny
WHERE z.numer_ewidencyjny='". $_SESSION['ewnioski_login']."'
ORDER BY " . $_SESSION['ewnioski_sortowanie_dotyk_zwolnienia'] .  " DESC LIMIT " . $limit;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT   z.id as id, z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.poczatek_pracy, k.nazwa, s.nazwa as status_wniosku,z.dane_mpk, z.data_kierownik, z.data_hr, z.przedzial_czasowy, os.imie ,os.nazwisko,z.numer_ewidencyjny_wpisujacy, z.login_kierownik
FROM zwolnienie z 
LEFT JOIN status s on z.id_status=s.id 
LEFT JOIN kategoria k on z.id_kategorii=k.id 
LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny
WHERE z.numer_ewidencyjny='". $_SESSION['ewnioski_login']."'
ORDER BY data_wpisania  " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        ?>
             <table width="100%" cellspacing="0" class="border_top">
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszZwolnieniaDotyk($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneZwolnieniaDotyk($wykonaj, $liczba_rekordow);
        } else {
            ?>

            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
         ?>
            </table><br/> <?php
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszZwolnienia($liczba,$widocznosc)
    {
         $polaczenie = $this->MysqliConnect();
         $colspan1=0;
      foreach ($widocznosc as $wartosc )
          {
              if((int)$wartosc>23 and (int)$wartosc<40 ) $colspan1++;
          }
          $colspan1=18-$colspan1;

         $colspan2=2;
      foreach ($widocznosc as $wartosc )
          {
              if($wartosc=='42') $colspan2--;
              if( $wartosc=='43') $colspan2--;
              if($wartosc=='46') $colspan2--;
          }
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td colspan="<?php echo $colspan1;?>"align="center" class="padding_5_all bg_11 bottom right">
                    <b class="inline">Modyfikacja globalna następujących pozycji: <span id="modyfikacje"></span></b>
                </td>
                   <?php if (array_search('38', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                <?php if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)){ ?>
                    <form method="POST" id="modyfikacja_globalna_hr">
                        <input type="hidden" name="id_global_hr_zwolnienie" id="id_global_hr" value=""/>
                        <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('modyfikacja_globalna_hr').submit();"/>
                    </form>
                    <input type="hidden" name="id_global_kierownik" id="id_global_kierownik" value=""/>
                    <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość tej akceptacji maja tylko pracownicy działu HR"/> <?php
                        }?>
                </td>
                  <?php } if (array_search('39', $widocznosc) === false) {
                    ?>
                  <td align="center" class="padding_5_all bg_11 bottom right">
                  </td>
                    <?php } if (array_search('40', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                  <?php if($_SESSION['ewnioski_stanowisko']=='kierownicze') { ?>
                    <form method="POST" id="modyfikacja_globalna_kierownik">
                        <input type="hidden" name="id_global_kierownik_zwolnienie" id="id_global_kierownik" value=""/>
                        <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('modyfikacja_globalna_kierownik').submit();"/>
                    </form>
                      <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość globalnej akceptacji maja tylko osoby ze stanowiskiem kierowniczym"/> <?php
                        }?>
                </td>
                  <?php } if ($colspan2!='0') {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom right" colspan="<?php echo $colspan2;?>">
                </td>
                <?php } ?>
            </tr>
            <tr>
             <?php
                 if (array_search('23', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                 <?php
                 } if (array_search('24', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                <?php
                 } if (array_search('25', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Wybór globalny.</b>
                </td>
                <?php
                 } if (array_search('26', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.data_wpisania&raport=zwolnienia">
                        <b> Data wpisania</b></a>
                </td>
                <?php
                 } if (array_search('27', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.imie_nazwisko&raport=zwolnienia">
                        <b>Imię i nazwisko</b></a>
                </td>
                <?php
                 } if (array_search('28', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.numer_ewidencyjny&raport=zwolnienia">
                        <b> Numer ewidencyjny </b></a>
                </td>
                <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.numer_ewidencyjny_wpisujacy&raport=zwolnienia">
                        <b> Numer wpisującego </b></a>
                </td>
                <?php
                 } if (array_search('29', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.liczba_godzin&raport=zwolnienia">
                        <b>Liczba godzin zwolnienia </b></a>
                </td>
                <?php
                 } if (array_search('30', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.data_zwolnienia&raport=zwolnienia">
                        <b>Data zwolnienia z godzin </b></a>
                </td>
                <?php
                 } if (array_search('31', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.data_odpracowania&raport=zwolnienia">
                        <b>Data odpracowania</b></a>
                </td>
                <?php
                 } if (array_search('32', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.poczatek_pracy&raport=zwolnienia">
                        <b>Godzina rozpoczęcia pracy / Powód</b></a>
                </td>
                <?php
                 } if (array_search('33', $widocznosc) === false) {
                    ?>
                  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.przedzial_czasowy&raport=zwolnienia">
                        <b>Przedział czasowy zmiany godzin </b></a>
                </td>
                <?php
                 } if (array_search('34', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=k.nazwa&raport=zwolnienia">
                        <b>Rodzaj zwolnienia </b></a>
                </td>
                <?php
                 } if (array_search('35', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.id_status&raport=zwolnienia">
                        <b>Status zwolnienia </b></a>
                </td>
                <?php
                 } if (array_search('36', $widocznosc) === false) {
                    ?>
                    <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?urlop&sort=z.profil&raport=urlopy">
                        <b>Zakład</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.dane_mpk&raport=zwolnienia">
                        <b> Dane kierownika</b></a>
                </td>
                <?php
                 } if (array_search('37', $widocznosc) === false) {
                    ?>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.data_kierownik&raport=zwolnienia">
                        <b> Akceptacja kierownika</b></a>
                </td>
                <?php
                 } if (array_search('38', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?zwolnienia&sort=z.data_hr&raport=zwolnienia">
                        <b> Pobrane przez HR</b></a>
                </td>
                <?php
                 } if (array_search('39', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Wygeneruj wniosek PDF </b>
                </td>
                <?php
                 } if (array_search('40', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Zaakceptuj wniosek  </b>
                </td>
                <?php
                 } if (array_search('41', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Anuluj wniosek  </b>
                </td>
                <?php
                 } if (array_search('42', $widocznosc) === false) {
                    ?>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Odrzuć wniosek  </b>
                </td>
                <?php                 }
                    ?>
            </tr>
            <tr>
            <?php
                  if (array_search('23', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('24', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('25', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input id="checkbox_calosc<?php echo $liczba; ?>" name="<?php echo $liczba; ?>" type='checkbox'
                               onclick="IdCheckbox('wszystko', <?php echo $liczba; ?>);"
                               value='<?php echo $liczba; ?>'/>
                    </td>
                    <?php
                 } if (array_search('26', $widocznosc) === false) {
                    ?>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_data_wpisania_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_data_wpisania_zwolnienie'])) echo trim($_SESSION['ewnioski_data_wpisania_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('27', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_imieinazwisko_zwolnienie" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_imieinazwisko_zwolnienie'])) echo trim($_SESSION['ewnioski_imieinazwisko_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('28', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_numer_ewidencyjny_zwolnienie" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_numer_ewidencyjny_zwolnienie'])) echo trim($_SESSION['ewnioski_numer_ewidencyjny_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie'])) echo trim($_SESSION['ewnioski_numer_ewidencyjny_wpisujacy_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('29', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="number" name="ewnioski_poczatek_wolnego_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_poczatek_wolnego_zwolnienie'])) echo trim($_SESSION['ewnioski_poczatek_wolnego_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('30', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_koniec_wolnego_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_koniec_wolnego_zwolnienie'])) echo trim($_SESSION['ewnioski_koniec_wolnego_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('31', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_data_odrobienia_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_data_odrobienia_zwolnienie'])) echo trim($_SESSION['ewnioski_data_odrobienia_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('32', $widocznosc) === false) {
                    ?>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_liczba_dni_zwolnienie" id="filter_id" size="5" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_liczba_dni_zwolnienie'])) echo trim($_SESSION['ewnioski_liczba_dni_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('33', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_liczba_dni_eksport_zwolnienie" id="filter_id" size="5" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_liczba_dni_eksport_zwolnienie'])) echo trim($_SESSION['ewnioski_liczba_dni_eksport_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('34', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_1">
                    <select name="ewnioski_kategoria_zwolnienie" onchange="document.forms['form_f_1'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM kategoria  order by nazwa";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_kategoria_zwolnienie'] == $wiersz['nazwa']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['nazwa']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                <?php
                 } if (array_search('35', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_2">
                    <select name="ewnioski_status_zwolnienie" onchange="document.forms['form_f_2'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM status order by nazwa";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_status_zwolnienie'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                </td><?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                    <?php  if($_SESSION['ewnioski_zmiana_profilu']==1)
                          {
                              ?>
                 <form method="POST" id="form_f_4">
                    <select name="ewnioski_profil" onchange="document.forms['form_f_4'].submit();">
                          <?php
                        $q1 = "SELECT id,nazwa FROM  profil  ORDER BY nazwa ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_profil'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                <?php
                }
                else
                    {
                         ?><img src="../img/remove_filter.png"/><?php
                    }
                ?>
                </td>
                <?php
                 } if (array_search('36', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                 <form method="POST" id="form_f_3">
                    <select name="ewnioski_dane_mpk_zwolnienie" onchange="document.forms['form_f_3'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                           $q1 = "SELECT DISTINCT (os.numer_ewidencyjny), os.imie, os.nazwisko FROM  zwolnienie z LEFT JOIN ewidencja_szkolen.osoby os on z.dane_mpk=os.numer_ewidencyjny order by os.nazwisko";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_dane_mpk_zwolnienie'] == $wiersz['nazwisko']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['nazwisko']; ?>"><?php echo $wiersz['nazwisko'].' '.$wiersz['imie']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                <?php
                 } if (array_search('37', $widocznosc) === false) {
                    ?>
                <td   align="center" class="padding_5_all bg_11 bottom right">
                  <form method="POST">
                        <input type="date" name="ewnioski_status_kierownik_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_status_kierownik_zwolnienie'])) echo trim($_SESSION['ewnioski_status_kierownik_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                    </td>
                    <?php
                 } if (array_search('38', $widocznosc) === false) {
                    ?>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                     <form method="POST">
                        <input type="date" name="ewnioski_status_hr_zwolnienie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_status_hr_zwolnienie'])) echo trim($_SESSION['ewnioski_status_hr_zwolnienie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <?php
                 } if (array_search('39', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('40', $widocznosc) === false) {
                    ?>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('41', $widocznosc) === false) {
                    ?>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php
                 } if (array_search('42', $widocznosc) === false) {
                    ?>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <?php }  ?>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
     /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszZwolnieniaDotyk($liczba)
    {
         $polaczenie = $this->MysqliConnect();
         $colspan1=0;

        ?>

            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.data_wpisania">
                        <b> Data wpisania</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.imie_nazwisko">
                        <b>Imię i nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.numer_ewidencyjny">
                        <b> Numer ewidencyjny </b></a>
                </td>
                   <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.numer_ewidencyjny_wpisujacego">
                        <b> Numer wpisującego </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.liczba_godzin">
                        <b>Liczba godzin zwolnienia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.data_zwolnienia">
                        <b>Data zwolnienia z godzin </b></a>
                </td>

                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.data_odpracowania">
                        <b>Data odpracowania</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.poczatek_pracy">
                        <b>Godzina rozpoczęcia pracy </b></a>
                </td>
                  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.przedzial_czasowy">
                        <b>Przedział czasowy zmiany godzin </b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=k.nazwa">
                        <b>Rodzaj zwolnienia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.id_status">
                        <b>Status zwolnienia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.dane_mpk">
                        <b> Przełożony</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?raport_zwolnien&sort=z.data_kierownik">
                        <b> Akceptacja kierownika</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Anuluj wniosek  </b>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneZwolnienia($wykonaj, $liczba,$widocznosc)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
            <?php
                  if (array_search('23', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
            <?php
                 } if (array_search('24', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['id']; ?>
            </td>
            <?php
                 } if (array_search('25', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <input id="checkbox_<?php echo $licznik; ?>" name="<?php echo $dane['id'] ?>"
                               type='checkbox' onclick="IdCheckbox('<?php echo $licznik; ?>','<?php echo $liczba; ?>')"
                               value='<?php echo $dane['id']; ?>'/>
            </td>
            <?php
                 } if (array_search('26', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                     <p title="<?php echo $dane['osoba_wpisujaca']; ?>"><?php echo $dane['data_wpisania']; ?></p>
            </td>
            <?php
                 } if (array_search('27', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <?php echo $dane['imie_nazwisko']; ?>
            </td>
            <?php
                 } if (array_search('28', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <?php
                 }
                   if (array_search('45', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny_wpisujacy']; ?>
            </td>
            <?php
                 }if (array_search('29', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['liczba_godzin']; ?>
            </td>
            <?php
                 } if (array_search('30', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_zwolnienia']; ?>
            </td>
            <?php
                 } if (array_search('31', $widocznosc) === false) {
                ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo str_replace(',', '<br>', $dane['data_odpracowania']); ?>
            </td>
            <?php
                 } if (array_search('32', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <?php
                      if($dane['poczatek_pracy']=='00:00:00')$dane['poczatek_pracy']='';
                    echo $dane['poczatek_pracy']; ?>
            </td>
            <?php
                 } if (array_search('33', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['przedzial_czasowy']; ?>
            </td>
            <?php
                 } if (array_search('34', $widocznosc) === false) {
                    ?>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>
            <?php
                 } if (array_search('35', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['status_wniosku']; ?>
            </td>
            <?php
                 } if (array_search('45', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa_profilu']; ?>
            </td>
            <?php
                 } if (array_search('36', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                      <?php echo $dane['nazwisko'].' '.$dane['imie']; ?>
            </td>
            <?php
                 } if (array_search('37', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                    if($dane['data_kierownik']=='0000-00-00 00:00:00')$dane['data_kierownik']='brak';
                    echo $dane['data_kierownik']; ?>
            </td>
            <?php
                 } if (array_search('38', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                      if($dane['data_hr']=='0000-00-00 00:00:00')$dane['data_hr']='brak';
                    echo $dane['data_hr']; ?>
            </td>
            <?php
                 } if (array_search('39', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <form method="POST" id="id_wiazki_<?php echo $dane['id']; ?>"/>
                    <input type="hidden" name="wydruk_zwolnienie" value="<?php echo $dane['id']; ?>"/>
                    <input type="button" value="" class="drukowanie_guzik"
                           onclick="document.getElementById('id_wiazki_<?php echo $dane['id']; ?>').submit()"/>
                    </form>
            </td>
            <?php
                 } if (array_search('40', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">

            <?php

            $zapytaniespr="SELECT m.kierownik FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on m.id=o.id_mpk
 WHERE o.numer_zastepcy_stan_godzin  LIKE '%".$_SESSION['ewnioski_login']."%'";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $danespr=mysqli_fetch_row($wykonajspr);
            if (($dane['status_wniosku']=='wpisany' and (strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']) or $danespr!=null) and strtoupper($_SESSION['ewnioski_login'])!=$dane['numer_ewidencyjny'])or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="akcptuj_<?php echo $licznik; ?>">
                            <input type="hidden" name="akcptuj_id_zwolnienie" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="zatwierdz_guzik"
                                   onclick="document.getElementById('akcptuj_<?php echo $licznik; ?>').submit();"/>
                        </form>
                        <?php }
                        else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość akceptacji maja tylko wnioski ze statusem wpisany"/> <?php
                        }?>
            </td>
            <?php
                 } if (array_search('41', $widocznosc) === false) {
                    ?>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
             if ((($dane['status_wniosku']=='wpisany' or $dane['status_wniosku']=='zaakceptowany przez przełożonego')and strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']))or ($dane['status_wniosku']=='wpisany' and strtoupper($dane['numer_ewidencyjny_wpisujacy'])==strtoupper($_SESSION['ewnioski_login'])) or($dane['status_wniosku']=='zaakceptowany przez przełożonego' and strtoupper($dane['login_kierownik'])==strtoupper($_SESSION['ewnioski_full_name']) )or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="delete_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik_zwolnienie" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('delete_<?php echo $licznik; ?>', 'Czy napewno chcesz cofnąc wniosek z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>
            <?php
                 } if (array_search('42', $widocznosc) === false) {
                    ?>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
             if ((($dane['status_wniosku']=='wpisany' or $dane['status_wniosku']=='zaakceptowany przez przełożonego')and strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']))or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="anuluj_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik_zwolnienie_anuluj" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="zakaz_guzik"
                                   onclick="CzyNaPewno('anuluj_<?php echo $licznik; ?>', 'Czy napewno odrzucić wniosek z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>
            <?php
                 }                     ?>

</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
      /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneZwolnieniaDotyk($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <?php echo $dane['data_wpisania']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <?php echo $dane['imie_nazwisko']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny_wpisujacy']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['liczba_godzin']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_zwolnienia']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo str_replace(',', '<br>', $dane['data_odpracowania']); ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <?php
                      if($dane['poczatek_pracy']=='00:00:00')$dane['poczatek_pracy']='';
                    echo $dane['poczatek_pracy']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['przedzial_czasowy']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['status_wniosku']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                      <?php echo $dane['nazwisko'].' '.$dane['imie']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                    if($dane['data_kierownik']=='0000-00-00 00:00:00')$dane['data_kierownik']='brak';
                    echo $dane['data_kierownik']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right"><?php
             if ((($dane['status_wniosku']=='wpisany' or $dane['status_wniosku']=='zaakceptowany przez przełożonego')and strtoupper($dane['dane_mpk'])==strtoupper($_SESSION['ewnioski_login']))or ($dane['status_wniosku']=='wpisany' and strtoupper($dane['numer_ewidencyjny_wpisujacy'])==strtoupper($_SESSION['ewnioski_login'])) or($dane['status_wniosku']=='zaakceptowany przez przełożonego' and strtoupper($dane['login_kierownik'])==strtoupper($_SESSION['ewnioski_full_name']) )or $_SESSION['ewnioski_admin']=='1') {?>
                        <form method="POST" id="delete_<?php echo $licznik; ?>">
                            <input type="hidden" name="id_cofnij_kierownik_zwolnienie" value="<?php echo $dane['id']; ?>"/>
                            <input type="hidden" name="dotyk"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('delete_<?php echo $licznik; ?>', 'Czy napewno chcesz cofnąc wniosek z numerem id (<?php echo $dane['id']; ?>) ');"/>
                        </form>
                        <?php
                        }
                          else {?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość anulowania mają tylko wnioski ze statusem wpisany lub zaakceptowany przez przełożonego "/> <?php
                        }?>
            </td>

</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
  /**
* Funkacja odpowiedzialan za zmiane statusu wniosku o zmiane godzin czy zwolnienei z pracy
 */
    function ZaakceptujZwolnienie()
    {

       $polaczenie = $this->MysqliConnect();
       $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email
FROM zwolnienie w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['akcptuj_id_zwolnienie']."'";
       $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
       $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
       $wynik=mysqli_fetch_row($wykonajspr);
       $zapytanie = "UPDATE zwolnienie SET id_status='2', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['akcptuj_id_zwolnienie'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
        mysqli_close($polaczenie);
        if ($wynik[4] != '') {
                            $adresat =$wynik[4];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek zwolnienia z godzin/ zmiany czasu pracy.<br>
                Został zaakceptowany przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Akceptacja wniosku urlopowego', $page);
                        }


        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Zaakceptowano wniosek o zwolnienie z godzin/ zmiane godzin.</font></h1>';
        $this->Przekieruj('raporty.php?zwolnienia');
    }
      /**
* Funkacja odpowiedzialan za zmiane statusu zwolnienia
 */
    function CofnijZwolnienie()
    {
         if(isset($_POST['dotyk'])) $strona='dotyk.php?raport_zwolnien';
        else $strona='raporty.php?zwolnienia';
       $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE zwolnienie SET id_status='6', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['id_cofnij_kierownik_zwolnienie'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
  $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.data_zwolnienia , w.poczatek_pracy
FROM zwolnienie w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['id_cofnij_kierownik_zwolnienie']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytaniespr);
            $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Anulowano wniosek o zwolnienie z godzin/ zmiane godzin.</font></h1>';
        if ($wynik[4] != '') {
            if($wynik[5]=='0000-00-00')$wynik[5]='';
            if($wynik[6]=='00:00:00')$wynik[6]='';
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . "  " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek zwolnienia z godzin/ zmiany czasu pracy w przedziale czasowym  <br> $przedzial.<br>
                Został anulowany przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Anulowanie wniosku zwolnienia z godzin/ zmiany czasu pracy', $page);
                        }

        $this->Przekieruj($strona);
    }
    /**
* Funkacja odpowiedzialan za zmiane statusu zwolnienia
 */
    function AnulujZwolnienie()
    {
       $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE zwolnienie SET id_status='4', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $_POST['id_cofnij_kierownik_zwolnienie_anuluj'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
 $zapytaniespr="SELECT w.id, w.id_status, w.id_kategorii, w.numer_ewidencyjny , o.email, w.data_zwolnienia , w.poczatek_pracy
FROM zwolnienie w
LEFT JOIN ewidencja_szkolen.osoby o on w.numer_ewidencyjny=o.numer_ewidencyjny
 where w.id='".$_POST['id_cofnij_kierownik_zwolnienie_anuluj']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytaniespr);
            $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Odrzucono wniosek o zwolnienie z godzin/ zmiane godzin.</font></h1>';
         if ($wynik[4] != '') {
            if($wynik[5]=='0000-00-00')$wynik[5]='';
            if($wynik[6]=='00:00:00')$wynik[6]='';
                            $adresat =$wynik[4];
                            $przedzial = $wynik[5] . "  " . $wynik[6];
                            $akceptujacy=$_SESSION['ewnioski_full_name'];
                            $page = $this->TrescMaila("<h2>Twój wniosek zwolnienia z godzin/ zmiany czasu pracy w przedziale czasowym  <br> $przedzial.<br>
                Został odzrzucony przez $akceptujacy. </h2><br>
                Aby przejść do programu naciśnij następujący link do <a href=" . Konfiguracja::Pobierz('adres_programu') . "raporty.php?urlop > eWniosków</a>");
                            $this->Wyslij($adresat, 'Odrzycenie wniosku zwolnienia z godzin/ zmiany czasu pracy', $page);
                        }
        $this->Przekieruj('raporty.php?zwolnienia');
    }
    /**
* Funkacja odpowiedzialan za zmiane statusu zwolnienia w przypadku wybrania kilku wniosków
 */
    function ZaakceptujZwolnienieGlobalnie()
    {
        $polaczenie = $this->MysqliConnect();
        $wnioski=explode(',',$_POST['id_global_kierownik_zwolnienie']);
       $zmienione='';
       $niezmienione='';
        for ($i = 0; $i < count($wnioski); $i++) {
            $zapytanie="SELECT id, id_status, id_kategorii FROM zwolnienie where id='$wnioski[$i]'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
            if($wynik[1]==1)
                {
                     $zmienione=$zmienione.$wynik[0].', ';
                     $zapytaniezmien = "UPDATE zwolnienie SET id_status='2', login_kierownik='". $_SESSION['ewnioski_full_name'] . "', data_kierownik=now()
                      WHERE id='" . $wynik[0] . "' ";
                     $wykonaj = mysqli_query($polaczenie, $zapytaniezmien);
                     $this->BladZapytania($polaczenie, $zapytaniezmien, $_SESSION['ewnioski_login']);
                        $this->ZapiszLog('Zapytanie;' . $zapytaniezmien, $_SESSION['ewnioski_login']);
                }
                else{
                    $niezmienione=$niezmienione.$wynik[0].', ';
                }
        }
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Następujące wnioski zwolnienia/zmiany godzin zostały zaakceptowane: '.$zmienione.'.</font></h1>';
        if($niezmienione!='')
            {
                $_SESSION['ewnioski_komunikat'] = '<h1><font color="red">Następujące wnioski nie zostały zaakceptowane ze względu na inny status niż wpisany: '.$niezmienione.'.</font></h1><br>'.$_SESSION['ewnioski_komunikat'];
            }
        $this->Przekieruj('raporty.php?zwolnienia');
    }
    /**
* Funkacja odpowiedzialan za zmiane statusu zwolnienia dział HR w przypadku wybrania kilku wniosków
 */
    function ZaakceptujZwolnienieGlobalnieHR()
    {
        $polaczenie = $this->MysqliConnect();
        $wnioski=explode(',',$_POST['id_global_hr_zwolnienie']);
       $zmienione='';
       $niezmienione='';
        for ($i = 0; $i < count($wnioski); $i++) {
            $zapytanie="SELECT id, id_status, id_kategorii FROM zwolnienie where id='$wnioski[$i]'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            $wynik=mysqli_fetch_row($wykonaj);
            if($wynik[1]==2 )
                {
                    if($wynik[2]==9 )
                        {
                     $zmienione=$zmienione.$wynik[0].', ';
                     $zapytaniezmien = "UPDATE zwolnienie SET  id_status='3',  login_hr='". $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id='" . $wynik[0] . "' ";
                     $wykonaj = mysqli_query($polaczenie, $zapytaniezmien);
                     $this->BladZapytania($polaczenie, $zapytaniezmien, $_SESSION['ewnioski_login']);
                     $this->ZapiszLog('Zapytanie;' . $zapytaniezmien, $_SESSION['ewnioski_login']);

                     }
                     else
                         {
                               $zmienione=$zmienione.$wynik[0].', ';
                     $zapytaniezmien = "UPDATE zwolnienie SET  id_status='3',  login_hr='". $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id='" . $wynik[0] . "' ";
                     $wykonaj = mysqli_query($polaczenie, $zapytaniezmien);
                     $this->BladZapytania($polaczenie, $zapytaniezmien, $_SESSION['ewnioski_login']);
                     $this->ZapiszLog('Zapytanie;' . $zapytaniezmien, $_SESSION['ewnioski_login']);
                         }
                }
                else{
                    $niezmienione=$niezmienione.$wynik[0].', ';
                }
        }
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Następujące zwolnienia/zmiany czasu zostały zaakceptowane: '.$zmienione.'.</font></h1>';
        if($niezmienione!='')
            {
                $_SESSION['ewnioski_komunikat'] = '<h1><font color="red">Następujące wnioski nie zostały zaakceptowane ze względu na inny status niż wpisany: '.$niezmienione.'.</font></h1><br>'.$_SESSION['ewnioski_komunikat'];
            }
        $this->Przekieruj('raporty.php?zwolnienia');
    }
    /**
     * Raport wyjątków związanych z MPK-ami
     */
    function RaportMPK()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('mpk', 'akceptacja', 'mail', 'inna_osoba',);
        $array2 = array('ewnioski_osoby_mpk', 'ewnioski_akceptacja', 'ewnioski_mail_mpk', 'ewnioski_inna_osoba');
$dodatkowy_warunek="id!=0 ";
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $zapytanie = "SELECT *
FROM mpk_wyjatki
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
           $zapytanieliczba = "SELECT *
FROM mpk_wyjatki
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.
            <table>
                <tr>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numer MPK z systemu RCP</b>
                    </td>
                     <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numer ewidencyjny osoby akceptujacej</b>
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Osoba akceptujaca wnioski z tego MPK-u</b>
                    </td>
                     <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Email</b>
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Pisanie wniosków dla innych</b>
                    </td>
                </tr>
                <tr>
                <form method="POST">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="mpk_wpisz"  size="30" class="border" />
                    </td>
                      <td  align="center" class="padding_5_all bg_11 bottom right">
                         <input type="text" name="numer_kierownika"  id="numer_kierownika" size="30" class="border" onkeyup="PobierzDane('numer_kierownika','akceptujacy')"/>
                    </td>
                    <td nowap align="center" class="padding_5_all bg_11 bottom right">
                         <input type="text" name="akceptujacy1" id="akceptujacy1" size="30" class="border" disabled/>
                          <input type="hidden" name="akceptujacy" id="akceptujacy" size="30" class="border" />
                    </td>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="email" name="email"  size="30" class="border" />
                    </td>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="checkbox" name="ewnioski_raport_mpk" id="filter_id" size="30" class="border" />
                    </td>

                </tr>
                <tr>
                    <td colspan="5" align="center"><input type="submit" value="Dodaj MPK" /></td>
                     </form>
                </tr>
            </table>

        <?php }
        $this->PierwszyWierszMPK($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneMPK($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do wyjatków z MPK-ami
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszMPK($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                   <a href="?mpk&sort=mpk"> <b>MPK</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=akceptacja">
                        <b>Dane osoby która akceptuje</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=akceptacja">
                        <b>Numer ewidencyjny osoby która akceptuje</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=mail">
                        <b>Mail osoby akceptującej</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=inna_osoba">
                        <b> Pisanie wniosków dla innych osób</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Usuń  </b>
                </td>
            </tr>
            <tr>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_raport_mpk" id="filter_id" size="30" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_raport_mpk'])) echo trim($_SESSION['ewnioski_raport_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_akceptacja" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_akceptacja'])) echo trim($_SESSION['ewnioski_akceptacja']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_mail_mpk" id="filter_id" size="20" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_mail_mpk'])) echo trim($_SESSION['ewnioski_mail_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneMPK($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['id']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['mpk']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['akceptacja']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['mail']; ?>
            </td>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <?php
                      if($dane['inna_osoba']=='0')echo 'Brak';
                      else echo 'Jest'; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <form method="POST" id="usun_mpk_<?php echo $licznik; ?>">
                            <input type="hidden" name="usun_mpk" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('usun_mpk_<?php echo $licznik; ?>', 'Czy napewno chcesz usunać wyjatek MPK-u z następującym nuemrem ID(<?php echo $dane['id']; ?>) ');"/>
                        </form>
            </td>
</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
      /**
     * Raport wyjątków związanych z osobami
     */
    function RaportOsoby()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('numer_ewidencyjny', 'imie_nazwisko', 'osoba_akceptujaca');
        $array2 = array('ewnioski_osoby_mpk', 'ewnioski_osoby_imie', 'ewnioski_osoby_akceptajca');
$dodatkowy_warunek="id!=0 ";
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $zapytanie = "SELECT *
FROM osoby_wyjatki
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_osoby'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
           $zapytanieliczba = "SELECT *
FROM osoby_wyjatki
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_osoby'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
       ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>
 <table>
                <tr>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numer ewidencyjny osoby</b>
                    </td>

                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Imię i nazwisko osoby której dotyczy wyjątek</b>
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numer ewidencyjny przełożonego</b>
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Imię i nazwisko przełożonego</b>
                    </td>
<td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Email przełożonego</b>
                    </td>
                     <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=inna_osoba">
                        <b> Pisanie wniosków dla innych osób</b></a>
                </td>
                </tr>
                <tr>
                <form method="POST">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="numer_wpisz"  id="numer_wpisz" size="30" class="border" onkeyup="PobierzDane('numer_wpisz','akceptujacy')"/>
                    </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                   <input type="text" name="akceptujacy1" id="akceptujacy1" size="30" class="border" disabled/>
                        <input type="hidden" name="akceptujacy" id="akceptujacy" size="30" class="border" />
                    </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="numer_ewidencyjny" id="numer_ewidencyjny" size="30" class="border" onkeyup="PobierzDaneEmail('numer_ewidencyjny','mail_nowy','imie_nazwisko')"/>
                    </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                         <input type="text" name="imie_nazwisko1" id="imie_nazwisko1" size="30" class="border" disabled/>
                         <input type="hidden" name="imie_nazwisko" id="imie_nazwisko" size="30" class="border" />
                    </td>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="email" name="mail_nowy"  id="mail_nowy" size="40" class="border" />
                    </td>
                      <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="checkbox" name="ewnioski_raport_mpk" id="ewnioski_raport_mpk" size="30" class="border" />
                        <input type="hidden" name="id_edytuj" id="id_edytuj" value="" size="30" class="border" />
                    </td>


                </tr>
                <tr>
                    <td colspan="6" align="center"><input type="submit" value="Dodaj kolejną osobę" /></td>
                     </form>
                </tr>
            </table>
        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszOsoby($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneOsoby($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do wyjatków z osobami
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszOsoby($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?osoby&sort=numer_ewidencyjny">
                        <b>Numer ewidencyjny osoby</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?osoby&sort=imie_nazwisko">
                        <b>Imię i nazwisko osoby której dotyczy wyjatek</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?osoby&sort=numer_ewidencyjny_kierownik">
                        <b>Numer ewidencyjny osoby akceptującej</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?osoby&sort=osoba_akceptujaca">
                        <b>Imię i nazwisko osoby akceptującej</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?osoby&sort=mail">
                        <b>Email osoby akceptującej</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?mpk&sort=inna_osoba">
                        <b> Pisanie wniosków dla innych osób</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Edytuj  </b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b> Usuń  </b>
                </td>
            </tr>
            <tr>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                  <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_mpk" id="filter_id" size="30" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_mpk'])) echo trim($_SESSION['ewnioski_osoby_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>

                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_imie" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_imie'])) echo trim($_SESSION['ewnioski_osoby_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_akceptajca" id="filter_id" size="20" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_akceptajca'])) echo trim($_SESSION['ewnioski_osoby_akceptajca']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneOsoby($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                            <?php echo $licznik; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['id']; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['numer_ewidencyjny']; ?>
                </td>
                  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['imie_nazwisko']; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['numer_ewidencyjny_kierownik']; ?>
                </td>
                  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['osoba_akceptujaca']; ?>
                </td>
                 </td>
                  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['mail']; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <?php
                      if($dane['inna_osoba']=='0')echo 'Brak';
                      else echo 'Jest'; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                           <form method="POST" id="usun_osobe_<?php echo $licznik; ?>">
                                <input type="hidden" name="usun_osoba" value="<?php echo $dane['id']; ?>"/>
                                <input type="button" value="" class="edytowanie_guzik"
                                       onclick="Edytuj('<?php echo $dane['id'] ?>','<?php echo $dane['numer_ewidencyjny'] ?>','<?php echo $dane['imie_nazwisko'] ?>','<?php echo $dane['numer_ewidencyjny_kierownik'] ?>','<?php echo $dane['osoba_akceptujaca'] ?>','<?php echo $dane['mail'] ?>','<?php echo $dane['inna_osoba'] ?>');"/>
                            </form>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                           <form method="POST" id="usun_osobe_<?php echo $licznik; ?>">
                                <input type="hidden" name="usun_osoba" value="<?php echo $dane['id']; ?>"/>
                                <input type="button" value="" class="kasowanie_guzik"
                                       onclick="CzyNaPewno('usun_osobe_<?php echo $licznik; ?>', 'Czy napewno usunąć osobe z następującym numerem ID (<?php echo $dane['id']; ?>) ');"/>
                            </form>
                </td>
            </tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
    /**
* Wpisanie wyjątku z numerme MPK
 */
    function WpiszMPK()
    {
          $polaczenie = $this->MysqliConnect();
          $akceptajca=0;
          if(isset($_POST['ewnioski_raport_mpk']))
              {
                  $akceptajca=1;
              }
            $zapytanie = "INSERT INTO mpk_wyjatki (mpk, akceptacja, mail, inna_osoba, numer_ewidencyjny)
           values ('" . trim($_POST['mpk_wpisz']) . "','" . trim($_POST['akceptujacy']) . "','" . trim($_POST['email']) . "','" . trim($akceptajca) . "','" . trim($_POST['numer_kierownika']) . "' )";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wyjątek dla MPK-u  został wpisany do systemu</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?mpk');
    }
    /**
* Usuwanie nuemr MPk z tabeli wyjątków
 */
    function UsunMPK()
    {
          $polaczenie = $this->MysqliConnect();

            $zapytanie = "DELETE FROM mpk_wyjatki WHERE id ='".trim($_POST['usun_mpk'])."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Numer MPk został usuniety</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?mpk');
    }
     /**
* Wpisanie wyjątku z numerme Osoby
 */
    function WpiszOsoba()
    {
          $polaczenie = $this->MysqliConnect();
          $akceptajca=0;
          if(isset($_POST['ewnioski_raport_mpk']))
              {
                  $akceptajca=1;
              }
            $zapytanie = "INSERT INTO osoby_wyjatki (numer_ewidencyjny, imie_nazwisko, osoba_akceptujaca, numer_ewidencyjny_kierownik, mail, inna_osoba)
           values ('" . trim($_POST['numer_wpisz']) . "','" . trim($_POST['akceptujacy']) . "','" . trim($_POST['imie_nazwisko']) . "','" . trim($_POST['numer_ewidencyjny']) . "','" . trim($_POST['mail_nowy']) . "','" . trim($akceptajca) . "')";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wyjątek dla osoby  został wpisany do systemu</h3></font>';
            }
        mysqli_close($polaczenie);

        $this->Przekieruj('raporty.php?osoby');
    }
     /**
* Wpisanie wyjątku z numerme Osoby
 */
    function EdytujOsoba()
    {
          $polaczenie = $this->MysqliConnect();
          $akceptajca=0;
          if(isset($_POST['ewnioski_raport_mpk']))
              {
                  $akceptajca=1;
              }
            $zapytanie = "UPDATE osoby_wyjatki SET numer_ewidencyjny='" . trim($_POST['numer_wpisz']) . "', imie_nazwisko='" . trim($_POST['akceptujacy']) . "', osoba_akceptujaca='" . trim($_POST['imie_nazwisko']) . "',
             numer_ewidencyjny_kierownik='" . trim($_POST['numer_ewidencyjny']) . "', mail='" . trim($_POST['mail_nowy']) . "', inna_osoba='" . trim($akceptajca) . "'
            WHERE id='".$_POST['id_edytuj']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wyjątek dla osoby  został zmodyfikowany</h3></font>';
            }
        mysqli_close($polaczenie);

        $this->Przekieruj('raporty.php?osoby');
    }
    /**
* Usuwanie nuemr osoby z tabeli wyjątków
 */
    function UsunOsoba()
    {
          $polaczenie = $this->MysqliConnect();

            $zapytanie = "DELETE FROM osoby_wyjatki WHERE id ='".trim($_POST['usun_osoba'])."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Osoba została usunieta</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?osoby');
    }
/**
 * Sprawdzenia czy dana osoba ma zastępce nie używane po zmianei wersji
* @param $numerewidencyjny Numer ewidencyjny kierownika
* @param $kierownik Imię i nazwisko kierownika
 */
   /* function SprawdzZastepce($numerewidencyjny,$kierownik)
    {
         $polaczenie = $this->MysqliConnect();
         $zapytaniempk = "SELECT dane_mpk
FROM wniosek
WHERE  dane_mpk='$numerewidencyjny'";
        $wykonajmpk = mysqli_query($polaczenie, $zapytaniempk);
        $this->BladZapytania($polaczenie, $zapytaniempk, $_SESSION['ewnioski_login']);
         $danempk=mysqli_fetch_row($wykonajmpk);
        if($danempk!==null)
            {
           $zapytanie = "SELECT dane_zastepcy, id, mail_zastepcy
FROM ewidencja_szkolen.osoby
WHERE  numer_ewidencyjny='$numerewidencyjny'";
         $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $danezastepstwo=mysqli_fetch_row($wykonaj);
         if($danezastepstwo[0]=='' or $danezastepstwo[0]==',' or $danezastepstwo[0]==';,;')
             {

                 ?>
                 <h2>Nie masz jeszcze wpisanej osoby która może akceptować urlopy jako twój zastępca.<br> Aby dodać zastępstwo wciśnij poniższy przycisk.</h2>
                 <form method="POST" action="raporty.php?zastepstwo">
                    <input type="submit" value="Dodaj zastępce">
                    <input type="hidden" value="<?php echo $danezastepstwo[1]?>" name="nowy">
                    </form>
            <?php
             }
             else{
                 $dane=explode(',',$danezastepstwo[0]);
                 $dane2=explode(',',$danezastepstwo[2]);
                 $danetym=explode(';',$dane[0]);
                 $danetym2=explode(';',$dane[1]);
                 $zastepca2='';
                  $imie2='';
               if(isset($danetym2[1]))$zastepca2=$danetym2[1];
               if(isset($danetym2[2]))$imie2=$danetym2[1];

                  ?>
                 <h4>Twoi zastępcy to <?php echo $danetym[1].', '.$zastepca2?>.</h4>
                 <form method="POST" action="raporty.php?zastepstwo">
                    <input type="submit" value="Zmień zastępce">
                    <input type="hidden" value="<?php echo $danezastepstwo[1]?>" name="nowy">
                    <input type="hidden" value="<?php echo $zastepca2?>" name="imie1">
                    <input type="hidden" value="<?php echo $imie2?>" name="imie2">
                    <input type="hidden" value="<?php echo $danetym[0]?>" name="zastepca_1">
                    <input type="hidden" value="<?php echo $danetym2[0]?>" name="zastepca_2">
                     <input type="hidden" value="<?php echo $dane2[0]?>" name="zastepca_1_email">
                    <input type="hidden" value="<?php echo $dane2[1]?>" name="zastepca_2_email">
                    </form>
            <?php
             }
        }
        mysqli_close($polaczenie);
    }*/
    /**
* Formularz zmiany zastępcy kierownika
 */
    function FormularzZastepcy()
    {
        ?><br><br>
            <table>
                <tr>
                  <td align="center" class="padding_5_all bg_11 bottom right">
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Zastępca pierwszy</b>
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Zastępca drugi</b>
                    </td>

                </tr>
                 <tr>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numer ewidencyjny</b>
                    </td>
                <form method="POST">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="zastepca_1_wpsiz"  id="zastepca_1_wpsiz" size="30" autocomplete="off" class="border" value="<?php if (isset($_POST['zastepca_1'])) echo $_POST['zastepca_1']?>" onkeyup="PobierzDane('zastepca_1_wpsiz','imie')"/>
                    </td>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                         <input type="text" name="zastepca_2"  id="zastepca_2" size="30" class="border"  autocomplete="off" value="<?php if (isset($_POST['zastepca_2'])) echo $_POST['zastepca_2']?>" onkeyup="PobierzDane('zastepca_2','imie2')"/>
                    </td>
                </tr>
                <tr>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Imię i nazwisko</b>
                    </td>
                <form method="POST">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="imie"  id="imie" size="30" class="border" disabled="disabled" value="<?php if (isset($_POST['imie1'])) echo $_POST['imie1']?>"/>
                        <input type="hidden" name="imie1" id="imie1" size="30" class="border"  value="<?php if (isset($_POST['imie1'])) echo $_POST['imie1']?>"/>
                    </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="imie2"  id="imie2" size="30" class="border" disabled="disabled" value="<?php if (isset($_POST['imie2'])) echo $_POST['imie2']?>"/>
                        <input type="hidden" name="imie21" id="imie21" size="30" class="border" value="<?php if (isset($_POST['imie2'])) echo $_POST['imie2']?>"/>
                    </td>
                </tr>
                 <tr>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Email</b>
                    </td>
                <form method="POST">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="zastepca_1_wpsiz_email"  size="30" class="border" value="<?php if (isset($_POST['zastepca_1_email'])) echo $_POST['zastepca_1_email']?>"/>
                    </td>
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                         <input type="text" name="zastepca_2_email"  size="30" class="border"  value="<?php if (isset($_POST['zastepca_2_email'])) echo $_POST['zastepca_2_email']?>"/>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="padding_5_all bg_11 bottom right" colspan="3">
                    <input type="hidden" valu="<?php  ?>" name="nowy">
                        <input type="submit" value="Zaaktualizuj zastępce"  size="30" class="border" />
                        <input type="hidden" value="<?php echo htmlspecialchars((string)($_POST['nowy'] ?? '')) ?>" name="id">
                    </td>
                </form>
                </tr>
            </table>
            <br><br>
                    <?php
    }
    /**
* Aktualizacja bazy danych pola zastępca kierownika
 */
    function AktualizujZaastepstwo()
    {

        $polaczenie = $this->MysqliConnect();

        if ($_POST['zastepca_1_wpsiz'] == ''){
            $_POST['imie1'] = '';
        $_POST['zastepca_1_wpsiz_email']='';
    }
if($_POST['zastepca_2']=='')
    {
      $_POST['imie21']='';
       $_POST['zastepca_2_email']='';
    }
        $zapytanie = "UPDATE ewidencja_szkolen.osoby SET  dane_zastepcy='". $_POST['zastepca_1_wpsiz'] . ";". $_POST['imie1'] . ",". $_POST['zastepca_2'] . ";". $_POST['imie21'] . "',mail_zastepcy='". $_POST['zastepca_1_wpsiz_email'] . ",". $_POST['zastepca_2_email'] . "'
                      WHERE id='" . $_POST['id'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
           if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Zastępstwo zostało zaktualizowane.</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?urlop');
    }
     /**
     * Raport wszystkich osób dostępnych w ewnioskach
     */
    function RaportOsobyWszystkie()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('o.imie', 'o.nazwisko', 'o.numer_ewidencyjny',' o.email',' m.mpk',' m.mail','mw.akceptacja','mw.mail','ow.osoba_akceptujaca','ow.mail','o.praca_zdalna','o.profil');
        $array2 = array('ewnioski_osoby_lista_imie', 'ewnioski_osoby_lista_nazwisko', 'ewnioski_osoby_lista_numer_ewidencyjny','ewnioski_osoby_lista_email','ewnioski_osoby_lista_mpk','ewnioski_osoby_lista_mail_kierownik','ewnioski_osoby_lista_akceptacja','ewnioski_osoby_lista_mail_kieronwik2',
        'ewnioski_osoby_lista_akceptacja2','ewnioski_osoby_lista_mail_kieronwik3', 'ewnioski_osoby_praca_zdalna','ewnioski_profil');
$dodatkowy_warunek="o.numer_ewidencyjny!='' ";
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                if($array2[$i]=='ewnioski_profil')
                    {
                        if($_SESSION[$array2[$i]]!=999) $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                    }
                    else
                        {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                    }
                $licznik++;
            }
        }
        $zapytanie = "SELECT o.id, o.imie, o.nazwisko, o.numer_ewidencyjny, o.email, m.mpk, m.mail, o.numer_zastepcy_stan_godzin, o.wnioski_dla_innych, o.praca_zdalna,o.profil, p.nazwa as nazwa_profilu  FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id 
LEFT JOIN  profil p on o.profil=p.id                               
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_osoby'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
           $zapytanieliczba = "SELECT o.id,  o.imie, o.nazwisko, o.numer_ewidencyjny, o.email, m.mpk, m.mail, o.numer_zastepcy_stan_godzin, o.wnioski_dla_innych, o.praca_zdalna,o.profil, p.nazwa as nazwa_profilu    FROM ewidencja_szkolen.osoby o
LEFT JOIN ewidencja_szkolen.mpk m on o.id_mpk=m.id 
LEFT JOIN  profil p on o.profil=p.id    
WHERE  $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_osoby'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "   " ;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
       ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>
        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wierszy
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszOsobyWszystkie($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
           $this->DaneOsobyWszystkie($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów wszystkich osob w ewnioskach
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszOsobyWszystkie($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <a href="?wszystkie&sort=o.imie&raport=osoby">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.nazwisko&raport=osoby">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.numer_ewidencyjny&raport=osoby">
                        <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.profil&raport=osoby">
                        <b>Zakład</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.email&raport=osoby">
                        <b>Mail</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.wnioski_dla_innych&raport=osoby">
                        <b>Pisanie dla innych</b></a>
                </td>
                  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.praca_zdalna&raport=osoby">
                        <b>Praca zdalna</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Mail przełożonego</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b> Przełożony </b>
                </td>
  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wszystkie&sort=o.numer_zastepcy_stan_godzin&raport=osoby">
                        <b> Zastępcy</b></a>
                </td>
            </tr>
            <tr>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_lista_imie" id="filter_id" size="30" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_lista_imie'])) echo trim($_SESSION['ewnioski_osoby_lista_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_lista_nazwisko" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_lista_nazwisko'])) echo trim($_SESSION['ewnioski_osoby_lista_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_lista_numer_ewidencyjny" id="filter_id" size="20" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_lista_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_osoby_lista_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                  <?php  if($_SESSION['ewnioski_zmiana_profilu']==1)
                          {
                              ?>
                 <form method="POST" id="form_f_4">
                    <select name="ewnioski_profil" onchange="document.forms['form_f_4'].submit();">

                          <?php
                        $q1 = "SELECT id,nazwa FROM  profil  ORDER BY nazwa ";
                        $e1 = mysqli_query($this->MysqliConnect(), $q1);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_profil'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                <?php
                }
                else
                    {
                         ?><img src="../img/remove_filter.png"/><?php
                    }
                ?>
                </td>
               <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_osoby_lista_email" id="filter_id" size="20" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_osoby_lista_email'])) echo trim($_SESSION['ewnioski_osoby_lista_email']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                     <form method="POST" id="form_f_1">
                    <select name="ewnioski_osoby_praca_zdalna" onchange="document.forms['form_f_1'].submit();">
                        <option value="">Wszystko</option>
                        <option <?php if ($_SESSION['ewnioski_osoby_praca_zdalna'] =='0') echo "SELECTED"; ?> value="0">Nie</option>
                        <option <?php if ($_SESSION['ewnioski_osoby_praca_zdalna'] =='1') echo "SELECTED"; ?> value="1">Tak</option>
                    </select>
                </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>

            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneOsobyWszystkie($wykonaj, $liczba)
    {
        $polaczenie=$this->MysqliConnect();
        $licznik = 1;
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {

             $color = $this->KolorWiersza($licznik);
             $zapytanieprzelozony="SELECT o.imie, o.nazwisko, o.mail
                                    FROM stan_godzin sg
                                    LEFT JOIN ewidencja_szkolen.osoby o on o.numer_ewidencyjny=sg.numer_kierownik
                                    WHERE sg.numer_ewidencyjny = '".$dane['numer_ewidencyjny']."'
                                    ORDER by sg.id desc limit 1";
               $wykonajprzelozony = mysqli_query($polaczenie, $zapytanieprzelozony);
        $this->BladZapytania($polaczenie, $zapytanieprzelozony, $_SESSION['ewnioski_login']);
        $daneprzelozony=mysqli_fetch_row($wykonajprzelozony);
            ?>
            <tr>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                            <?php echo $licznik; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['imie']; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['nazwisko']; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['numer_ewidencyjny']; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['nazwa_profilu']; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['email']; ?>
                </td>
                  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php
                         if ($_SESSION['ewnioski_admin'] == '1')
                         {
                             ?>
                              <form method="POST" id="pisanie_dla_inncyh<?php echo $licznik; ?>" >
                                    <input type="hidden" name="numer_id_pisanie_dla_inncyh" value="<?php echo $dane['id']; ?>"/>
                                    <input type="hidden" name="numer_ewidencyjny_pisanie_dla_inncyh" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                                    <input type="hidden" name="aktualna_wartosc" value="<?php echo$dane['wnioski_dla_innych']; ?>"/>
                                    <input type="hidden" name="strona" value="wszystkie"/>
                                    <input type="button" value="" class="edytowanie_guzik"
                                           onclick="document.forms['pisanie_dla_inncyh<?php echo $licznik; ?>'].submit();"/><?php
                                            if($dane['wnioski_dla_innych']=='1') echo " tak";
                        else echo " nie";?>
                                </form>
                                <?php
                         }
                         else{
                              if($dane['wnioski_dla_innych']=='1') echo "tak";
                        else echo "nie";
                         }

                        ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php

                         if ($_SESSION['ewnioski_admin'] == '1')
                         {
                             ?>
                              <form method="POST" id="praca_zdalna<?php echo $licznik; ?>" >
                                    <input type="hidden" name="numer_id_praca_zdalna" value="<?php echo $dane['id']; ?>"/>
                                    <input type="hidden" name="numer_ewidencyjny_praca_zdalna" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                                    <input type="hidden" name="aktualna_wartosc" value="<?php echo$dane['praca_zdalna']; ?>"/>
                                    <input type="hidden" name="strona" value="wszystkie"/>
                                    <input type="button" value="" class="edytowanie_guzik"
                                           onclick="document.forms['praca_zdalna<?php echo $licznik; ?>'].submit();"/><?php
                                            if($dane['praca_zdalna']=='1') echo " tak";
                        else echo " nie";?>
                                </form>
                                <?php
                         }
                         else{
                              if($dane['praca_zdalna']=='1') echo "tak";
                        else echo "nie";
                         }

                        ?>
                </td>
                  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $daneprzelozony[2] ?? ''; ?>
                </td>
                <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo ($daneprzelozony[0] ?? '').' '.($daneprzelozony[1] ?? ''); ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $dane['numer_zastepcy_stan_godzin']; ?>
                </td>

            </tr>
            <?php
            $licznik++;
        } ?>   </table> <?php

    }
    /**
* Formularz wczytania liczby dniu urlopu
 */
    function FormularzWczytaniaUrlopu()
    {
?>
 <h4>Usuń dane z dnia którego one zostały importowane.</h4>
<form method="POST">
            <input type="date" name="usun_data" id="usun_data" />
            <input type="submit" value="Usuń dane z tego dnia." class="border"/>
        </form>
 <br/><br/><br/><br/><br/>
        <form method="POST">
            <input type="hidden" name="przyklad" id="przyklad" value="urlopy.xlsx"/>
            <input type="submit" value="Przykładowy arkusz do zaciągania liczbi dni urlopu." class="border"/>
        </form>
        <br/>
        <form method="POST">
        <h4>Proszę wkleić do okienka całą zawartość pliku excelowego zgodnego ze wzorem z powyższego pliku.</h4>
        <textarea name="opisexcel" class="border" rows="20" cols="180"></textarea>
        <br/>
        <br/>
        <input type="submit" value="Wstępna weryfikacja"/>
        <br/>
        <br/>
        </form><?php
    }

        /**
     * Sprowadza nagłówek z arkusza do postaci porównywalnej:
     * usuwa twarde spacje, zwija białe znaki, przycina i zamienia na małe litery.
     */
    function NormalizujNaglowek($tekst)
    {
        $tekst = str_replace(array("\r", "\n", "\t", "\xc2\xa0"), ' ', $tekst);
        $tekst = preg_replace('/\s+/u', ' ', $tekst);
        $tekst = trim($tekst);
        if (function_exists('mb_strtolower')) $tekst = mb_strtolower($tekst, 'UTF-8');
        else $tekst = strtolower($tekst);
        return $tekst;
    }

    /**
     * Przygotowuje wartość liczbową do zapytania SQL.
     * Pusta komórka albo "bd" => NULL (a nie 0).
     */
    function WartoscLiczbowaSQL($wartosc)
    {
        $wartosc = trim(str_replace(',', '.', $wartosc));
        if ($wartosc === '' || strtolower($wartosc) == 'bd') return 'NULL';
        return "'" . addslashes($wartosc) . "'";
    }
    /**
     *  Wstępna weryfikacja danych do opisu faktury z pliku excel
     */
    function WeryfikacjaExcel()
    {
        ?>
        <table align="center" border="0" name="opis" id="opis" class="padding_5_all bottom right left top">
            <form method="POST" action="raporty.php?wczytaj_urlop" id="blad">
            </form>
            <form method="POST" action="raporty.php?wpisz_urlop" id="forma" name="forma">
                <?php
                $confW = $this->ParseIniE("../conf/confe.ini");
                $sciezka = $confW['FolderTMP'];
                $plik = @fopen($sciezka . 'ImportUrlopu.txt', "w");
                if ($plik === false) {
                    ?></form></table><?php
                    $this->Alert('Nie można zapisać pliku tymczasowego', htmlspecialchars($sciezka . 'ImportUrlopu.txt') . ' - sprawdź FolderTMP w conf/confe.ini.', 'error');
                    return;
                }
                $linijka = explode("\n", $_POST['opisexcel']);
                $a = 1;
                $tablicabryg = '';
                $tabimie = '';

                // Mapa: znormalizowany nagłówek => opis kolumny.
                // Klucze budowane tym samym normalizatorem co nagłówki z arkusza,
                // więc wielkość liter i spacje nie mają znaczenia.
                $mapa = array();
                foreach ($this->ImportStanGodzinMapowanie as $naglowek => $opis) {
                    $opis['naglowek'] = $naglowek;
                    $mapa[$this->NormalizujNaglowek($naglowek)] = $opis;
                }

                // Pozycja kolumny w arkuszu => opis kolumny. Wypełniane z wiersza nagłówków.
                $pozycje = array();

                foreach ($linijka as $element) {
                    $i = 1;
                    if ($element != '') {
                        ?>
                        <tr nowrap align="center" class="padding_5_all bottom right left top">
                            <?php
                            $linijka2 = explode("\t", $element);
                            $blad = 0;
                            $wartosci = array();
                            $numer = '';
                            $imie = '';
                            $nazwisko = '';

                            foreach ($linijka2 as $element2) {
                                $element2 = str_replace("\r", "", $element2);

                                if ($a == 1) {
                                    /* ---------- WIERSZ NAGŁÓWKÓW ---------- */
                                    $klucz = $this->NormalizujNaglowek($element2);

                                    if ($klucz === '') {
                                        $i++;
                                        continue; // pusta kolumna na końcu wklejenia - pomijamy
                                    }
                                    if (!isset($mapa[$klucz])) {
                                        ?> <h1><a
                                                onclick="document.forms['blad'].submit()">Nieznana kolumna w arkuszu: "<?php echo htmlspecialchars($element2); ?>" (pozycja <?php echo $i; ?>). Proszę skorzystać z aktualnej formatki załączonej na stronie, która otworzy się po kliknięciu.</a>
                                        </h1><?php
                                        exit;
                                    }
                                    foreach ($pozycje as $juzMa) {
                                        if ($juzMa['kolumna'] == $mapa[$klucz]['kolumna']) {
                                            ?> <h1><a
                                                    onclick="document.forms['blad'].submit()">Kolumna "<?php echo htmlspecialchars($element2); ?>" występuje w arkuszu dwa razy. Proszę skorzystać z aktualnej formatki załączonej na stronie, która otworzy się po kliknięciu.</a>
                                            </h1><?php
                                            exit;
                                        }
                                    }
                                    $pozycje[$i] = $mapa[$klucz];
                                    ?>
                                    <td nowrap align="center" class="padding_5_all bottom right left top">
                                        <b><?php echo $element2; ?></b></td>
                                    <?php
                                } else {
                                    /* ---------- WIERSZ Z DANYMI ---------- */
                                    if (!isset($pozycje[$i])) {
                                        $i++;
                                        continue; // kolumna spoza formatki - ignorujemy
                                    }
                                    $opis = $pozycje[$i];
                                    $wartosci[$opis['kolumna']] = $element2;

                                    switch ($opis['walidacja']) {
                                        case 'numer':
                                            $ok = (strlen($element2) == 5 or strlen($element2) == 6);
                                            break;
                                        case 'tekst':
                                            $ok = (strlen(trim($element2)) > 2);
                                            break;
                                        case 'niepuste':
                                            $ok = (strlen(trim($element2)) > 0);
                                            break;
                                        default:
                                            $ok = true;
                                    }
                                    if ($ok) {
                                        $bgcolor = "style='background-color:green'";
                                    } else {
                                        $blad = 1;
                                        $bgcolor = "style='background-color:red'";
                                    }

                                    if ($opis['kolumna'] == 'numer_ewidencyjny') $numer = $element2;
                                    if ($opis['kolumna'] == 'nazwisko') $nazwisko = $element2;
                                    if ($opis['kolumna'] == 'imie') $imie = $element2;
                                    ?>
                                    <td nowrap align="center" class="padding_5_all bottom right left top"
                                        id="opis_<?php echo $a . '_' . $i; ?>" <?php echo $bgcolor ?>>
                                        <input type="text" value="<?php echo $element2; ?>"
                                               name="opis_<?php echo $a . '_' . $i; ?>" readonly="readonly"/>
                                    </td>
                                    <?php
                                }
                                $i++;
                            }

                            if ($a == 1) {
                                // Po przejściu nagłówków: sprawdzamy, czy niczego nie brakuje
                                $znalezione = array();
                                foreach ($pozycje as $opis) $znalezione[] = $opis['kolumna'];
                                $brakujace = array();
                                foreach ($this->ImportStanGodzinMapowanie as $naglowek => $opis) {
                                    if (!in_array($opis['kolumna'], $znalezione)) $brakujace[] = $naglowek;
                                }
                                if (count($brakujace) > 0) {
                                    ?> <h1><a
                                            onclick="document.forms['blad'].submit()">W arkuszu brakuje kolumn: "<?php echo htmlspecialchars(implode('", "', $brakujace)); ?>". Proszę skorzystać z aktualnej formatki załączonej na stronie, która otworzy się po kliknięciu.</a>
                                    </h1><?php
                                    exit;
                                }
                            } else {
                                // Zapis wiersza do pliku tymczasowego w kolejności kanonicznej
                                foreach ($this->ImportStanGodzinKolejnosc as $kolumna) {
                                    $wartosc = isset($wartosci[$kolumna]) ? $wartosci[$kolumna] : '';
                                    // średnik jest separatorem pliku tymczasowego - nie może trafić do wartości
                                    $wartosc = str_replace(';', ',', $wartosc);
                                    fputs($plik, $wartosc . ';');
                                }
                                fputs($plik, $blad . "\r\n");

                                if (isset($wartosci['brygadzista']) && $wartosci['brygadzista'] == 'B') {
                                    $tablicabryg .= $numer . ';';
                                    $tabimie .= $imie . ' ' . $nazwisko . ';';
                                }
                            }
                            ?> </tr> <?php
                        $a++;
                    }
                }
                fclose($plik);
                ?>
            </form>
            <?php

            if (substr($tablicabryg, -1) == ';') $tablicabryg = substr($tablicabryg, 0, -1);
            if (substr($tabimie, -1) == ';') $tabimie = substr($tabimie, 0, -1);
            $polaczenie = $this->MysqliConnect();
            $zapytanie = "SELECT imie, nazwisko, numer_ewidencyjny, brygadzista FROM stan_godzin where brygadzista=1 and stan_na_dzien=(SELECT MAX(stan_na_dzien) FROM stan_godzin)  
                and profil ='" . $_SESSION['ewnioski_profil'] . "' ORDER BY numer_ewidencyjny ";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            $tablicabrygadzista = array();
            $tablicabrygadzistanazwisko = array();
            while ($danezastepstwo = mysqli_fetch_array($wykonaj)) {
                array_push($tablicabrygadzista, 'zabrano-' . $danezastepstwo[2]);
                array_push($tablicabrygadzistanazwisko, $danezastepstwo[1] . ' ' . $danezastepstwo[0]);
            }
            $tablicabryg = explode(';', $tablicabryg);
            $tabimie = explode(';', $tabimie);
            foreach ($tablicabryg as $key => $value) {
                if (array_search('zabrano-' . $value, $tablicabrygadzista) !== false) {
                    $indeks = array_search('zabrano-' . $value, $tablicabrygadzista);
                    unset($tablicabrygadzista[$indeks]);
                    unset($tablicabrygadzistanazwisko[$indeks]);
                } else {
                    array_push($tablicabrygadzista, 'dodano-' . $value);
                    array_push($tablicabrygadzistanazwisko, $tabimie[$key]);
                }
            }

            $tabwyswietl = array();
            foreach ($tablicabrygadzista as $key => $value) {
                array_push($tabwyswietl, $tablicabrygadzista[$key] . '-' . $tablicabrygadzistanazwisko[$key]);
            }
            sort($tabwyswietl);
            ?>

        </table>
        </br></br>
        <table>
            <tr>
                <td align="center" class="padding_5_all bottom right left top" colspan="3">
                    <h2>W poniższych wierszach zmieniły się uprawnienia dla brygadzistów. Sprawdź je w raporcie "Sprawdź zastępce" </h2>
                </td>
            </tr>
            <tr>
                <td align="center" class="padding_5_all bottom right left top">
                    Dano/zabrano uprawnienia
                </td>
                <td align="center" class="padding_5_all bottom right left top">
                    Numer Ewidencyjny
                </td>
                <td align="center" class="padding_5_all bottom right left top">
                    Dano/zabrano uprawnienia
                </td>
            </tr>
            <?php
            foreach ($tabwyswietl as $value) {
                $dane = explode('-', $value);
                ?>
                <tr>
                    <td align="center" class="padding_5_all bottom right left top">
                        <?php echo $dane[0] ?>
                    </td>
                    <td align="center" class="padding_5_all bottom right left top">
                        <?php echo $dane[1] ?>
                    </td>
                    <td align="center" class="padding_5_all bottom right left top">
                        <?php echo $dane[2] ?>
                    </td>
                </tr><?php
            }
            ?>
            <tr>
                <td align="center" colspan="3">
                    <input type="button" value="Wpisz stan urlopu i nadgodzin"
                           onclick="document.forms['forma'].submit()"/>
                </td>
            </tr>

        </table>
        <?php
    }
    function ZaciagnijDane()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");
        $sciezka = $confW['FolderTMP'] . 'ImportUrlopu.txt';
        $dane = (string)@file_get_contents($sciezka);
        $tablica = explode("\n", $dane);
        $polaczenie = $this->MysqliConnect();
        $i = 0;
        $i2 = 0;
        $brygadzista = '';

        $kolejnosc = $this->ImportStanGodzinKolejnosc;
        $indeksFlagi = count($kolejnosc); // flaga błędu stoi za ostatnią kolumną

        $zapytaniekasuj = "DELETE FROM stan_godzin WHERE DATE_SUB(NOW(), INTERVAL 355 DAY)> data_importu 
                          and profil='" . trim($_SESSION['ewnioski_profil']) . "'";
        $wykonaj = mysqli_query($polaczenie, $zapytaniekasuj);
        $liczbaskasowanych = mysqli_affected_rows($polaczenie);
        $komunikatskasuj = '.';
        if ($liczbaskasowanych > 0) $komunikatskasuj = "</br>Skasowano nastepujacą liczbę przestarzałych wierszy z bazy danych:" . $liczbaskasowanych;

        foreach ($tablica as $klucz => $wartosc) {
            if (trim($wartosc) == '') continue;
            $danewiersz = explode(';', $wartosc);

            // wiersz wchodzi tylko wtedy, gdy walidacja w WeryfikacjaExcel() go przepuściła
            if (!isset($danewiersz[$indeksFlagi]) || trim($danewiersz[$indeksFlagi]) != '0') {
                $i2++;
                continue;
            }

            // pozycja w pliku tymczasowym => nazwa kolumny w bazie
            $w = array();
            foreach ($kolejnosc as $poz => $kolumna) {
                $w[$kolumna] = isset($danewiersz[$poz]) ? trim($danewiersz[$poz]) : '';
            }

            $brygadzistawpisz = ($w['brygadzista'] == 'B') ? 1 : 0;
            if ($brygadzistawpisz == 1) $brygadzista = $brygadzista . ';' . $w['numer_ewidencyjny'];

            $stanNaDzien = ($w['stan_na_dzien'] === '') ? 'NULL' : "'" . addslashes($w['stan_na_dzien']) . "'";

            $zapytanie = "INSERT INTO stan_godzin
            (numer_ewidencyjny, nazwisko, imie, mpk, numer_kierownik,
             stan_urlopu, stan_urlopu_godziny, urlop_zalegly, uz, rcp,
             ilosc_nadgodzin, ilosc_nadgodzin_plus, godziny_do_odpracowania, niedopracowania,
             stan_na_dzien, brygadzista,
             data_importu, login_uzytkownika, profil)
            VALUES (
             '" . addslashes($w['numer_ewidencyjny']) . "',
             '" . addslashes($w['nazwisko']) . "',
             '" . addslashes($w['imie']) . "',
             '" . addslashes($w['mpk']) . "',
             '" . addslashes($w['numer_kierownik']) . "',
             " . $this->WartoscLiczbowaSQL($w['stan_urlopu']) . ",
             " . $this->WartoscLiczbowaSQL($w['stan_urlopu_godziny']) . ",
             " . $this->WartoscLiczbowaSQL($w['urlop_zalegly']) . ",
             " . $this->WartoscLiczbowaSQL($w['uz']) . ",
             " . $this->WartoscLiczbowaSQL($w['rcp']) . ",
             " . $this->WartoscLiczbowaSQL($w['ilosc_nadgodzin']) . ",
             " . $this->WartoscLiczbowaSQL($w['ilosc_nadgodzin_plus']) . ",
             " . $this->WartoscLiczbowaSQL($w['godziny_do_odpracowania']) . ",
             " . $this->WartoscLiczbowaSQL($w['niedopracowania']) . ",
             " . $stanNaDzien . ",
             '" . $brygadzistawpisz . "',
             now(),
             '" . trim($_SESSION['ewnioski_login']) . "',
             '" . trim($_SESSION['ewnioski_profil']) . "')";

            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLogImport('Zapytanie import;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            $i++;
        }
        mysqli_close($polaczenie);
        $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Stan urlopów został wpisany do sytemu. Wpisano ' . $i . ' pozycji do systemu<br>Liczba pozycji z błędami które nie zostały wpisane ' . $i2 . $komunikatskasuj . '</h3></font>';
        $this->Przekieruj('index.php?');
}
       /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function RaportStanGodzin()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        
      $array1 = array('sg.numer_ewidencyjny', 'sg.nazwisko', 'sg.imie', 'sg.mpk','u.nazwisko','sg.stan_urlopu','sg.urlop_zalegly','sg.uz','sg.rcp','sg.ilosc_nadgodzin','sg.ilosc_nadgodzin_plus','sg.godziny_do_odpracowania','sg.niedopracowania','sg.stan_godzin','sg.data_importu','u1.nazwisko');
       $array2 = array('ewnioski_sg_numer_ewidencyjny', 'ewnioski_sg_nazwisko', 'ewnioski_sg_imie', 'ewnioski_sg_mpk', 'ewnioski_sg_nazwikso_kierownik','ewnioski_sg_stan_urlpou','ewnioski_sg_urlop_zalegly','ewnioski_sg_uz',
        'ewnioski_sg_rcp','ewnioski_sg_ilosc_nadgodzin','ewnioski_sg_ilosc_nadgodzin_plus','ewnioski_sg_godziny_do_odpracowania','ewnioski_sg_niedopracowania','ewnioski_sg_stan_godzin','ewnioski_sg_data_importu', 'ewnioski_sg_nazwisko_import');
  $widocznosc = array();
  $tym=0;
  $warunek='';

        $widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
        $dodatkowy_warunek="and  (sg.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or sg.numer_kierownik='".$_SESSION['ewnioski_login']."'or sg.numer_kierownik='".$_SESSION['ewnioski_login']."')";
       if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)) {
            $dodatkowy_warunek=" and sg.profil='".$_SESSION['ewnioski_profil']."'";;
        }
        else
        {
             $zapytaniezastepstwo="SELECT numer_ewidencyjny FROM ewidencja_szkolen.osoby WHERE numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%'";
        $wykonajzastepstwo = mysqli_query($polaczenie, $zapytaniezastepstwo);
        $this->BladZapytania($polaczenie, $zapytaniezastepstwo, $_SESSION['ewnioski_login']);
        while ($dane = mysqli_fetch_array($wykonajzastepstwo)) {
            if($tym==0) $warunek.="sg.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or sg.numer_kierownik='".$dane[0]."' or sg.numer_kierownik='".$_SESSION['ewnioski_login']."'";
            else $warunek.=" or sg.numer_kierownik='".$dane[0]."'";
           $tym++;
        }
        if($warunek!='')        $dodatkowy_warunek='and ('.$warunek.')';
        }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $zapytanie = "SELECT sg.id, sg.numer_ewidencyjny, sg.nazwisko, sg.imie, sg.mpk,u.nazwisko as nazwisko_kierownik,u.imie as imie_kierownik,sg.stan_urlopu,sg.urlop_zalegly, sg.uz, sg.rcp, sg.ilosc_nadgodzin,sg.ilosc_nadgodzin_plus,sg.godziny_do_odpracowania,sg.niedopracowania,sg.stan_godzin, sg.data_importu, u1.nazwisko as nazwisko_import, u1.imie as imie_import, sg.stan_na_dzien, sg.stan_urlopu_godziny, sg.profil
FROM stan_godzin sg
LEFT JOIN ewidencja_szkolen.osoby u on sg.numer_kierownik=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on sg.login_uzytkownika=u1.numer_ewidencyjny
WHERE DATE_FORMAT(sg.data_importu,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(sg.data_importu,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
           $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_godziny'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;

        $zapytanieexcel = "SELECT sg.id, sg.numer_ewidencyjny, sg.nazwisko, sg.imie, sg.mpk,u.nazwisko as nazwisko_kierownik,u.imie as imie_kierownik,sg.stan_urlopu,sg.urlop_zalegly, sg.uz, sg.rcp, sg.ilosc_nadgodzin,sg.ilosc_nadgodzin_plus,sg.godziny_do_odpracowania,sg.niedopracowania,sg.stan_godzin, sg.data_importu, u1.nazwisko as nazwisko_import, u1.imie as imie_import, sg.stan_na_dzien, sg.stan_urlopu_godziny, sg.profil
FROM stan_godzin sg
LEFT JOIN ewidencja_szkolen.osoby u on sg.numer_kierownik=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on sg.login_uzytkownika=u1.numer_ewidencyjny
WHERE DATE_FORMAT(sg.data_importu,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(sg.data_importu,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_godziny'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "" ;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT  sg.id,sg.numer_ewidencyjny, sg.nazwisko, sg.imie, sg.mpk,u.nazwisko as nazwisko_kierownik,u.imie as imie_kierownik,sg.stan_urlopu,sg.urlop_zalegly, sg.uz, sg.rcp, sg.ilosc_nadgodzin,sg.ilosc_nadgodzin_plus,sg.godziny_do_odpracowania,sg.niedopracowania,sg.stan_godzin, sg.data_importu, u1.nazwisko as nazwisko_import, u1.imie as imie_import, sg.stan_na_dzien, sg.stan_urlopu_godziny, sg.profil
FROM stan_godzin sg
LEFT JOIN ewidencja_szkolen.osoby u on sg.numer_kierownik=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on sg.login_uzytkownika=u1.numer_ewidencyjny
WHERE DATE_FORMAT(sg.data_importu,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(sg.data_importu,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
            $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_godziny'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "   " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        $this->SprawdzZastepceStanGodzin($_SESSION['ewnioski_login'],$_SESSION['ewnioski_full_name']);


         ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

                     <form method="POST" action="raporty.php?stan_urlopu" id="form_excel" >
                     <input type="hidden" name="zapytanie_excel_stan_godzin" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="button" value="Eksport do Excela" onclick="document.getElementById('form_excel').submit();" />
                     </form>
                     <br><br>
                     <?php
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszStanGodzin($liczba_rekordow,$widocznosc);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneStanGodzin($wykonaj, $liczba_rekordow,$widocznosc);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszStanGodzin($liczba,$widocznosc)
    {
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.numer_ewidencyjny&raport=godziny">
                        <b>Nr ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.nazwisko&raport=godziny">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.imie&raport=godziny">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.mpk&raport=godziny">
                        <b>Mpk i grupa</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=u.nazwisko&raport=godziny">
                        <b>Kierownik</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b>Zmień kierownika</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_urlopu&raport=godziny">
                        <b>UW</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_urlopu_godziny&raport=godziny">
                        <b>UW - godziny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.urlop_zalegly&raport=godziny">
                        <b>UW zaległy</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.uz&raport=godziny">
                        <b>UŻ</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.rcp&raport=godziny">
                        <b>Ilość nadgodzin nadpracowanych (limit 260)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.ilosc_nadgodzin&raport=godziny">
                        <b>NADGODZINY</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.ilosc_nadgodzin_plus&raport=godziny">
                        <b>Godziny odpracowane (+)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.godziny_do_odpracowania&raport=godziny">
                        <b>Godziny do odpracowania (-)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.niedopracowania&raport=godziny">
                        <b>Niedopracowania (bez wniosku)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_godzin&raport=godziny">
                        <b>Stan godzin<br/>(archiwalne)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_na_dzien&raport=godziny">
                        <b>Stan na dzień</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.data_importu&raport=godziny">
                        <b>Data importu</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=u1.nazwisko&raport=godziny">
                        <b>Osoba importująca</b></a>
                </td>
            </tr>
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_numer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_sg_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwisko" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwisko'])) echo trim($_SESSION['ewnioski_sg_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_imie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_imie'])) echo trim($_SESSION['ewnioski_sg_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_mpk" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_mpk'])) echo trim($_SESSION['ewnioski_sg_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwikso_kierownik" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwikso_kierownik'])) echo trim($_SESSION['ewnioski_sg_nazwikso_kierownik']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_stan_urlpou" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_stan_urlpou'])) echo trim($_SESSION['ewnioski_sg_stan_urlpou']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_urlop_zalegly" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_urlop_zalegly'])) echo trim($_SESSION['ewnioski_sg_urlop_zalegly']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_uz" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_uz'])) echo trim($_SESSION['ewnioski_sg_uz']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_rcp" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_rcp'])) echo trim($_SESSION['ewnioski_sg_rcp']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_ilosc_nadgodzin" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_ilosc_nadgodzin'])) echo trim($_SESSION['ewnioski_sg_ilosc_nadgodzin']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_ilosc_nadgodzin_plus" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_ilosc_nadgodzin_plus'])) echo trim($_SESSION['ewnioski_sg_ilosc_nadgodzin_plus']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_godziny_do_odpracowania" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_godziny_do_odpracowania'])) echo trim($_SESSION['ewnioski_sg_godziny_do_odpracowania']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_niedopracowania" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_niedopracowania'])) echo trim($_SESSION['ewnioski_sg_niedopracowania']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_stan_godzin" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_stan_godzin'])) echo trim($_SESSION['ewnioski_sg_stan_godzin']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_sg_data_importu" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_data_importu'])) echo trim($_SESSION['ewnioski_sg_data_importu']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwisko_import" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwisko_import'])) echo trim($_SESSION['ewnioski_sg_nazwisko_import']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
            </tr>
        <?php
    }    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneStanGodzin($wykonaj, $liczba,$widocznosc)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        while ($dane = mysqli_fetch_array($wykonaj)) {
            $color = $this->KolorWiersza($licznik);
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $licznik; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['id']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['nazwisko']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['imie']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['mpk']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['nazwisko_kierownik'] . ' ' . $dane['imie_kierownik']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <form method="POST" id="wyslij_mail_zmiana_przelozonego_<?php echo $licznik ?>">
                    <input type="hidden" name="id_mail" id="id_mail" value=""/>
                    <input type="hidden" name="mail_osoba" id="mail_osoba" value="<?php echo $dane['nazwisko'] . ' ' . $dane['imie']; ?>"/>
                    <input type="hidden" name="mail_numer" id="mail_numer" value="  <?php echo $dane['numer_ewidencyjny']; ?>"/>
                    <input type="hidden" name="mail_kierownik" id="mail_kierownik" value=" <?php echo $dane['nazwisko_kierownik'] . ' ' . $dane['imie_kierownik']; ?>"/>
                    <input type="button" value="" class="edycja_guzik"
                           onclick="document.getElementById('wyslij_mail_zmiana_przelozonego_<?php echo $licznik ?>').submit();"/>
                </form>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php
                if ($dane['stan_urlopu'] === NULL) $dane['stan_urlopu'] = 'Pracownik agencyjny';
                echo $dane['stan_urlopu']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['stan_urlopu_godziny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['urlop_zalegly']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['uz']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['rcp']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['ilosc_nadgodzin']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['ilosc_nadgodzin_plus']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php
                if ($dane['godziny_do_odpracowania'] === null) echo '';
                else echo number_format((float)$dane['godziny_do_odpracowania'], 2, ',', '');
                ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php
                if ($dane['niedopracowania'] === null) echo '';
                else echo number_format((float)$dane['niedopracowania'], 2, ',', '');
                ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['stan_godzin']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php
                if ($dane['stan_na_dzien'] == '0000-00-00') $dane['stan_na_dzien'] = '';
                echo $dane['stan_na_dzien']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['data_importu']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                <?php echo $dane['nazwisko_import'] . ' ' . $dane['imie_import']; ?>
            </td>
            </tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);
    }
      /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function RaportStanGodzinDotyk()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
  $tym=0;
  $warunek='';

        $zapytanie = "SELECT sg.id, sg.numer_ewidencyjny, sg.nazwisko, sg.imie, sg.mpk,u.nazwisko as nazwisko_kierownik,u.imie as imie_kierownik,sg.stan_urlopu,sg.urlop_zalegly, sg.uz, sg.rcp, sg.ilosc_nadgodzin,sg.ilosc_nadgodzin_plus,sg.stan_godzin, sg.data_importu, u1.nazwisko as nazwisko_import, u1.imie as imie_import
FROM stan_godzin sg
LEFT JOIN ewidencja_szkolen.osoby u on sg.numer_kierownik=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on sg.login_uzytkownika=u1.numer_ewidencyjny
WHERE sg.numer_ewidencyjny ='".$_SESSION['ewnioski_login']."'
           
            ORDER BY " . $_SESSION['ewnioski_sortowanie_dotyk_stan'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek_dotyk_stan'] . "  LIMIT " . $limit;

        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT  sg.id,sg.numer_ewidencyjny, sg.nazwisko, sg.imie, sg.mpk,u.nazwisko as nazwisko_kierownik,u.imie as imie_kierownik,sg.stan_urlopu,sg.urlop_zalegly, sg.uz, sg.rcp, sg.ilosc_nadgodzin,sg.ilosc_nadgodzin_plus,sg.stan_godzin, sg.data_importu, u1.nazwisko as nazwisko_import, u1.imie as imie_import
FROM stan_godzin sg
LEFT JOIN ewidencja_szkolen.osoby u on sg.numer_kierownik=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on sg.login_uzytkownika=u1.numer_ewidencyjny
WHERE sg.numer_ewidencyjny ='".$_SESSION['ewnioski_login']."'
            ORDER BY " . $_SESSION['ewnioski_sortowanie_dotyk_stan'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek_dotyk_stan'] . "   " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
         ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

                     <br><br>
                     <?php
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszStanGodzinDotyk($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneStanGodzinDotyk($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszStanGodzinDotyk($liczba)
    {
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>ID.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.numer_ewidencyjny&raport=godziny">
                        <b>Nr ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.nazwisko&raport=godziny">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.imie&raport=godziny">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.mpk&raport=godziny">
                        <b>Mpk i grupa</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=u.nazwisko&raport=godziny">
                        <b>Kierownik</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <b>Zmień kierownika</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_urlopu&raport=godziny">
                        <b>UW</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_urlopu_godziny&raport=godziny">
                        <b>UW - godziny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.urlop_zalegly&raport=godziny">
                        <b>UW zaległy</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.uz&raport=godziny">
                        <b>UŻ</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.rcp&raport=godziny">
                        <b>Ilość nadgodzin nadpracowanych (limit 260)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.ilosc_nadgodzin&raport=godziny">
                        <b>NADGODZINY</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.ilosc_nadgodzin_plus&raport=godziny">
                        <b>Godziny odpracowane (+)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.godziny_do_odpracowania&raport=godziny">
                        <b>Godziny do odpracowania (-)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.niedopracowania&raport=godziny">
                        <b>Niedopracowania (bez wniosku)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_godzin&raport=godziny">
                        <b>Stan godzin<br/>(archiwalne)</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.stan_na_dzien&raport=godziny">
                        <b>Stan na dzień</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.data_importu&raport=godziny">
                        <b>Data importu</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=u1.nazwisko&raport=godziny">
                        <b>Osoba importująca</b></a>
                </td>
            </tr>
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_numer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_sg_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwisko" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwisko'])) echo trim($_SESSION['ewnioski_sg_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_imie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_imie'])) echo trim($_SESSION['ewnioski_sg_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_mpk" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_mpk'])) echo trim($_SESSION['ewnioski_sg_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwikso_kierownik" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwikso_kierownik'])) echo trim($_SESSION['ewnioski_sg_nazwikso_kierownik']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_stan_urlpou" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_stan_urlpou'])) echo trim($_SESSION['ewnioski_sg_stan_urlpou']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_urlop_zalegly" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_urlop_zalegly'])) echo trim($_SESSION['ewnioski_sg_urlop_zalegly']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_uz" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_uz'])) echo trim($_SESSION['ewnioski_sg_uz']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_rcp" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_rcp'])) echo trim($_SESSION['ewnioski_sg_rcp']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_ilosc_nadgodzin" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_ilosc_nadgodzin'])) echo trim($_SESSION['ewnioski_sg_ilosc_nadgodzin']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_ilosc_nadgodzin_plus" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_ilosc_nadgodzin_plus'])) echo trim($_SESSION['ewnioski_sg_ilosc_nadgodzin_plus']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_godziny_do_odpracowania" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_godziny_do_odpracowania'])) echo trim($_SESSION['ewnioski_sg_godziny_do_odpracowania']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_niedopracowania" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_niedopracowania'])) echo trim($_SESSION['ewnioski_sg_niedopracowania']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_stan_godzin" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_stan_godzin'])) echo trim($_SESSION['ewnioski_sg_stan_godzin']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="date" name="ewnioski_sg_data_importu" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_data_importu'])) echo trim($_SESSION['ewnioski_sg_data_importu']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_sg_nazwisko_import" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_sg_nazwisko_import'])) echo trim($_SESSION['ewnioski_sg_nazwisko_import']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
            </tr>
        <?php
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneStanGodzinDotyk($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                  <?php echo $dane['nazwisko']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['mpk']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko_kierownik'].' '.$dane['imie_kierownik']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                     if($dane['stan_urlopu']===NULL)$dane['stan_urlopu']='Pracownik agencyjny';
                     echo $dane['stan_urlopu']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['urlop_zalegly']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['uz']; ?>
            </td>
         <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['rcp']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['stan_godzin']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_importu']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko_import'].' '.$dane['imie_import']; ?>
            </td>
              <?php
              $licznik++;
        }
                    ?>

</tr> <?php
mysqli_close($polaczenie);


    }
    /**
*
 */
     function UsunDaneZDnia()
    {
          $polaczenie = $this->MysqliConnect();

            $zapytanie = "DELETE FROM stan_godzin WHERE data_importu LIKE '%".trim($_POST['usun_data'])."%' and profil='".$_SESSION['ewnioski_profil']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            $liczbaskasowanych=mysqli_affected_rows($polaczenie);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Dane z dnia '.$_POST['usun_data'].' zostały usunięte.Liczba usuniętych rekordów to '.$liczbaskasowanych.'</h3></font>';
            }
           else {
                $_SESSION['ewnioski_komunikat'] = '<font color="red"><h3>Nie usunieta żadnych danych z następujacego dnia '.$_POST['usun_data'].' </h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?wczytaj_urlop');
    }
    /**
 * Sprawdzenia czy dana osoba ma zastępce
* @param $numerewidencyjny Numer ewidencyjny kierownika
* @param $kierownik Imię i nazwisko kierownika
 */
    function SprawdzZastepceStanGodzin($numerewidencyjny,$kierownik)
    {
         $polaczenie = $this->MysqliConnect();
         $zapytanieosoba = "SELECT numer_zastepcy_stan_godzin 
                            FROM ewidencja_szkolen.osoby 
                            WHERE  numer_ewidencyjny='".$_SESSION['ewnioski_login']."'";
         $wykonajosoba = mysqli_query($polaczenie, $zapytanieosoba);
         $this->BladZapytania($polaczenie, $zapytanieosoba, $_SESSION['ewnioski_login']);
         $danempk=mysqli_fetch_row($wykonajosoba);
         $numer=explode(',',$danempk[0]);
         $osoby='';
         if($danempk[0]!=''){
           foreach ($numer as $danespr)
                  {
                      $zapytanieosoba = "SELECT numer_ewidencyjny, imie, nazwisko
                                          FROM ewidencja_szkolen.osoby 
                                          WHERE  numer_ewidencyjny='".$danespr."'";
                      $wykonajosoba = mysqli_query($polaczenie, $zapytanieosoba);
                      $daneosoba=mysqli_fetch_row($wykonajosoba);
                      $this->BladZapytania($polaczenie, $zapytanieosoba, $_SESSION['ewnioski_login']);
                      if ($daneosoba) $osoby.=$daneosoba[0].'- '.$daneosoba[1].' '.$daneosoba[2].', ';
                  }
                $osoby=substr($osoby,0,-2);
           }
        if($danempk===null)
            {          ?>
                 <h2>Nie masz jeszcze wpisanej osoby która widzi stan urlopu na twoich MPK-ach.</h2>
                 <form method="POST" action="raporty.php?nowa_osoba">
                    <input type="submit" value="Dodaj osobę">
                    <input type="hidden" name="numer_ewidencyjny" value="<?php echo $_SESSION['ewnioski_login']?>" >
                    </form>
            <?php
}
             else{
                  ?>
                 <h4>Numery osób które widzą urlopy i mogą je akceptować przypisane na twój MPK: <?php echo $osoby;?></h4>

                 <form method="POST" action="raporty.php?nowa_osoba">
                 <input type="hidden" name="numer_ewidencyjny" value="<?php echo $_SESSION['ewnioski_login']?>" >
                 <input type="hidden" name="numer_osoby" value="<?php echo $danempk[0];?>" >
                    <input type="submit" value="Zmień zastępce">
                    </form>
            <?php
             }
        mysqli_close($polaczenie);
    }
     /**
* Formularz zmiany zastępcy kierownika
 */
    function FormularzZastepcyStanGodzin()
    {
        ?><br><br>
            <table>
                <tr>
                  <td align="center" class="padding_5_all bg_11 bottom right">
                    </td>
                    <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numery osób które widza stan urlopów na twoim MPK-u</b>
                    </td>
                </tr>
                 <tr>
                 <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Numery ewidencyjne <br>oddzielone przecinkiem</b>
                    </td>
                <form method="POST" action="raporty.php?stan_urlopu" id="zastepca">
                    <td  align="center" class="padding_5_all bg_11 bottom right">
                        <input type="text" name="osoba_wpisz"  id="osoba_wpisz" size="50" autocomplete="off" class="border" value="<?php if (isset($_POST['numer_osoby'])) echo $_POST['numer_osoby']?>"/>
                    </td>

                </tr>
                <tr>
                    <td align="center" class="padding_5_all bg_11 bottom right" colspan="3">
                    <input type="hidden" valu="<?php  ?>" name="nowy">
                        <input type="button" value="Zaaktualizuj listę osób"  size="30" class="border" onclick="SprawdzZastepstwo()"/>
                        <input type="hidden" value="<?php echo htmlspecialchars((string)($_POST['numer_ewidencyjny'] ?? '')) ?>" name="id">
                    </td>
                </form>
                </tr>
            </table>
            <br><br>
                    <?php
    }
    /**
* Aktualizacja bazy danych pola zastępca kierownika
 */
    function AktualizujZaastepstwoStanGodzin()
    {

        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE ewidencja_szkolen.osoby SET  numer_zastepcy_stan_godzin='". $_POST['osoba_wpisz']."'
                      WHERE numer_ewidencyjny='" . $_POST['id'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);
           if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Zastępstwo zostało zaktualizowane.</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?stan_urlopu');
    }
    function ZmianaKierownikaMail($osoba, $numer,$kierownik)
    {


        $adresat=$_SESSION['ewnioski_mail'].','.$_SESSION['ewnioski_profil_mail_rcp'];
        $zglaszajacy= $_SESSION['ewnioski_full_name'];

           $page = $this->TrescMaila("<h2>Nastepująca osoba prawdopodbnie ma źle przypisanego przełożonego. <br>
                Imię i nazwisko: $osoba <br>
                Numer ewidencyjny: $numer <br>
                Dotychczasowy kierownik: $kierownik <br>
                Imię i nazwisko osoby zgłaszającej: $zglaszajacy <br>");
                            $this->Wyslij($adresat, 'Zle przypisany przelozony', $page);

        $_SESSION['ewnioski_komunikat'] = '<h1><font color="#00aa00">Wysłano maila do działu RCP o zmianę kierownika.</font></h1>';
        $this->Przekieruj('raporty.php?stan_urlopu');
    }
    function ModyfikujPisanieDlaInncyh ()
    {
        $strona=$_POST['strona'];
        if($_POST['aktualna_wartosc']=='1')
            {
                $wpisz='0';
                $tym='ma zabrane uprawnienia pisania wniosków dla inncyh';
            }
            else
                {
                    $wpisz='1';
                    $tym='dostała możliwość pisania wniosków dla inncyh';
                }
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE ewidencja_szkolen.osoby SET  wnioski_dla_innych='". $wpisz."'
                      WHERE id='" . $_POST['numer_id_pisanie_dla_inncyh'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);

           if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Zmiana została wpisana.</h3></font>';
            }
           $zapytaniekierownik="SELECT o.email from stan_godzin s
LEFT JOIN ewidencja_szkolen.osoby o ON s.numer_kierownik=o.numer_ewidencyjny
 WHERE s.numer_ewidencyjny='" . $_POST['numer_ewidencyjny_pisanie_dla_inncyh'] . "'
order by s.id desc
LIMIT 1 ";
             $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
$this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
$danekierownik=mysqli_fetch_row($wykonajkierownik);
$mailkierownik=$danekierownik[0];
              $zapytanie="SELECT imie, nazwisko, numer_ewidencyjny,email FROM ewidencja_szkolen.osoby where id='" . $_POST['numer_id_pisanie_dla_inncyh'] . "' ";
                 $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
$dane=mysqli_fetch_row($wykonaj);
$osoba=$dane[0].' '.$dane[1];
$numer=$dane[2];
$adresat='STAR.RCP@motherson.com,'.$dane[3].','.$mailkierownik;
$page = $this->TrescMaila("<h2>Nastepująca osoba $tym. <br>
                Imię i nazwisko: $osoba <br>
                Numer ewidencyjny: $numer <br>");
                            $this->Wyslij($adresat, 'Zmiana uprawnien w eWnioskach', $page);
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?' . $strona);
    }
    function ModyfikujPracaZdalna ()
    {
        $strona=$_POST['strona'];
        if($_POST['aktualna_wartosc']=='1')
            {
                $wpisz='0';
                $tym='ma zabrane uprawnienia do pisania wniosków o prace zdalną';
            }
            else
                {
                    $wpisz='1';
                    $tym='dostała możliwość pisania wniosków o prace zdalną';
                }
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE ewidencja_szkolen.osoby SET  praca_zdalna='". $wpisz."'
                      WHERE id='" . $_POST['numer_id_praca_zdalna'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);

           if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Zmiana wartości pracy zdalnej została wpisana.</h3></font>';
            }
           $zapytaniekierownik="SELECT o.email from stan_godzin s
LEFT JOIN ewidencja_szkolen.osoby o ON s.numer_kierownik=o.numer_ewidencyjny
 WHERE s.numer_ewidencyjny='" . $_POST['numer_ewidencyjny_praca_zdalna'] . "'
order by s.id desc
LIMIT 1 ";
             $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
$this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
$danekierownik=mysqli_fetch_row($wykonajkierownik);
$mailkierownik=$danekierownik[0];
              $zapytanie="SELECT imie, nazwisko, numer_ewidencyjny,email FROM ewidencja_szkolen.osoby where id='" . $_POST['numer_id_praca_zdalna'] . "' ";
                 $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
$dane=mysqli_fetch_row($wykonaj);
$osoba=$dane[0].' '.$dane[1];
$numer=$dane[2];
$adresat='kamil.kolanowski@motherson.com,tymoteusz.surdy@motherson.com,lukasz.chorab@motherson.com,STAR.RCP@motherson.com,'.$dane[3].','.$mailkierownik;
$page = $this->TrescMaila("<h2>Nastepująca osoba $tym. <br>
                Imię i nazwisko: $osoba <br>
                Numer ewidencyjny: $numer <br>");
                            $this->Wyslij($adresat, 'Zmiana uprawnien w eWnioskach', $page);
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?' . $strona);
    }
/**
     * Raport sprawdzajacy czy zmienił sie jakiś brygadzista
     */
    function SprawdzZastepceHR()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $zapytaniedata="Select MAX(stan_na_dzien) from stan_godzin";
        $wykonajdata = mysqli_query($polaczenie, $zapytaniedata);
        $this->BladZapytania($polaczenie, $zapytaniedata, $_SESSION['ewnioski_login']);
        $wynikdata=mysqli_fetch_row($wykonajdata);
        $zapytanie = "SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, o1.wnioski_dla_innych, o1.id 
FROM `stan_godzin` sg
LEFT JOIN ewidencja_szkolen.osoby o on sg.numer_kierownik=o.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o1 on sg.numer_ewidencyjny=o1.numer_ewidencyjny 
WHERE  sg.stan_na_dzien='".$wynikdata[0]."' and sg.brygadzista='1' and sg.profil='".$_SESSION['ewnioski_profil']."'
            ORDER BY " . $_SESSION['ewnioski_sortowanie_sprawdz_zastepce'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
           $zapytanieliczba = "SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, o1.wnioski_dla_innych 
FROM `stan_godzin` sg
LEFT JOIN ewidencja_szkolen.osoby o on sg.numer_kierownik=o.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o1 on sg.numer_ewidencyjny=o1.numer_ewidencyjny 
WHERE  sg.stan_na_dzien='".$wynikdata[0]."' and sg.brygadzista='1' and sg.profil='".$_SESSION['ewnioski_profil']."'
            ORDER BY " . $_SESSION['ewnioski_sortowanie_sprawdz_zastepce'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);

        ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.

        <?php }
        $this->PierwszyWierszZastepceHR($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneZastepceHR($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do wyjatków z MPK-ami
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszZastepceHR($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>

                 <td align="center" class="padding_5_all bg_11 bottom right">
                   <a href="?sprawdz_zastepce&sort=sg.numer_ewidencyjny&raport=sprawdz_zastepce"> <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.imie&raport=sprawdz_zastepce">
                        <b>Imię</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.nazwisko&raport=sprawdz_zastepce">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.imie&raport=sprawdz_zastepce">
                        <b>Imię kierownika</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.nazwisko&raport=sprawdz_zastepce">
                        <b> Nazwisko kierownika</b></a>
                </td>
               <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o1.wnioski_dla_innych&raport=sprawdz_zastepce">
                        <b> Pisanie wniosków dla innych</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b> Czyim zastępcą jest</b>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneZastepceHR($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {

            $zapytaniekierownik="Select imie,nazwisko, numer_zastepcy_stan_godzin FROM ewidencja_szkolen.osoby where numer_zastepcy_stan_godzin LIKE '%".$dane['numer_ewidencyjny']."%'";
            $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
            $this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
            $color = $this->KolorWiersza($licznik);
            $kierownik='';
              while ($danekierownik = mysqli_fetch_array($wykonajkierownik)) {
                  $kierownik.=$danekierownik['imie'].' '.$danekierownik['nazwisko'].'-';
              }
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imiekierownik']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwiskokierownik']; ?>
            </td>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <?php
                         if ($_SESSION['ewnioski_admin'] == '1')
                         {
                             ?>
                              <form method="POST" id="pisanie_dla_inncyh_<?php echo $licznik; ?>" >
                                    <input type="hidden" name="numer_id_pisanie_dla_inncyh" value="<?php echo $dane['id']; ?>"/>
                                    <input type="hidden" name="numer_ewidencyjny_pisanie_dla_inncyh" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                                    <input type="hidden" name="aktualna_wartosc" value="<?php echo$dane['wnioski_dla_innych']; ?>"/>
                                    <input type="hidden" name="strona" value="sprawdz_zastepce"/>
                                    <input type="button" value="" class="edytowanie_guzik"
                                           onclick="document.forms['pisanie_dla_inncyh_<?php echo $licznik; ?>'].submit();"/><?php
                                            if($dane['wnioski_dla_innych']=='1') echo " tak";
                        else echo " nie";?>
                                </form>
                                <?php
                         }
                         else{
                              if($dane['wnioski_dla_innych']=='1') echo "tak";
                        else echo "nie";
                         }
                        ?>
            </td>
  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $kierownik; ?>
            </td>

</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
    /**
     * Raport sprawdzajacy czy zmienił sie jakiś brygadzista
     */
    function PisanieWnioskowDlaInnych()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
               $zapytaniedata="Select MAX(stan_na_dzien) from stan_godzin";
        $wykonajdata = mysqli_query($polaczenie, $zapytaniedata);
        $this->BladZapytania($polaczenie, $zapytaniedata, $_SESSION['ewnioski_login']);
        $wynikdata=mysqli_fetch_row($wykonajdata);
        $zapytanie = "SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, o.wnioski_dla_innych 
FROM `stan_godzin` sg
LEFT JOIN ewidencja_szkolen.osoby o on sg.numer_kierownik=o.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o1 on sg.numer_ewidencyjny=o1.numer_ewidencyjny 
WHERE  sg.stan_na_dzien='".$wynikdata[0]."' and sg.brygadzista='1' " ;
         $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $numer='';
         while ($dane = mysqli_fetch_array($wykonaj)) {
             $numer.=$dane['numer_ewidencyjny'].",";
         }
          $zapytanie = "SELECT DISTINCT (numer_kierownik)
FROM `stan_godzin` 
WHERE  stan_na_dzien='".$wynikdata[0]."' ";
         $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $kierownik='';
         while ($dane = mysqli_fetch_array($wykonaj)) {
             $kierownik.=$dane['numer_kierownik'].",";
         }
         $numer=$numer.$kierownik;
         $numer=substr($numer,0,-1);
         if ($numer === '') $numer = "''";
         if($_SESSION['ewnioski_sortowanie_sprawdz_zastepce']=='o1.wnioski_dla_innych') $sortowanie='sg.wnioski_dla_innych';
         else $sortowanie=$_SESSION['ewnioski_sortowanie_sprawdz_zastepce'];

         $zapytanie="SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, sg.wnioski_dla_innych, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, sg.id 
FROM ewidencja_szkolen.osoby sg
LEFT JOIN stan_godzin s on sg.numer_ewidencyjny=s.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o ON s.numer_kierownik=o.numer_ewidencyjny
WHERE sg.wnioski_dla_innych ='1' and s.stan_na_dzien='".$wynikdata[0]."' and s.profil='".$_SESSION['ewnioski_profil']."'
and sg.numer_ewidencyjny NOT IN($numer) 
ORDER BY  $sortowanie ". $_SESSION['ewnioski_sortowanie_kierunek'] ."  ";
           $zapytanieliczba = "SELECT o.numer_ewidencyjny, o.nazwisko,o.imie, o.wnioski_dla_innych, ok.imie as imiekierownik, ok.nazwisko as nazwiskokierownik 
FROM ewidencja_szkolen.osoby o
LEFT JOIN stan_godzin s on o.numer_ewidencyjny=s.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby ok ON s.numer_kierownik=ok.numer_ewidencyjny
WHERE o.wnioski_dla_innych ='1' and s.stan_na_dzien='".$wynikdata[0]."' and s.profil='".$_SESSION['ewnioski_profil']."'
and o.numer_ewidencyjny NOT IN($numer) 
ORDER BY o.wnioski_dla_innych DESC LIMIT $limit";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);

        ?>
        <br/>
            <table width="100%" cellspacing="0" class="border_top">
            <h2>Lista osób które mogą pisać wnioski urlopowe dla innych osób a nie są brygadzistami i nie zarządzają pracownikami.</h2>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.

        <?php }
        $this->PierwszyWierszPisanieWnioskowDlaInnych($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DanePisanieWnioskowDlaInnych($wykonaj, $liczba_rekordow);
        } else {
            ?>
          <br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do wyjatków z MPK-ami
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszPisanieWnioskowDlaInnych($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>

            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>

                 <td align="center" class="padding_5_all bg_11 bottom right">
                   <a href="?sprawdz_zastepce&sort=sg.numer_ewidencyjny&raport=sprawdz_zastepce"> <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.imie&raport=sprawdz_zastepce">
                        <b>Imię</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.nazwisko&raport=sprawdz_zastepce">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.imie&raport=sprawdz_zastepce">
                        <b>Imię kierownika</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.nazwisko&raport=sprawdz_zastepce">
                        <b> Nazwisko kierownika</b></a>
                </td>
               <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o1.wnioski_dla_innych&raport=sprawdz_zastepce">
                        <b> Pisanie wniosków dla innych</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b> Czyim zastępcą jest</b>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DanePisanieWnioskowDlaInnych($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {

            $zapytaniekierownik="Select imie,nazwisko, numer_zastepcy_stan_godzin FROM ewidencja_szkolen.osoby where numer_zastepcy_stan_godzin LIKE '%".$dane['numer_ewidencyjny']."%'";
            $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
            $this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
            $color = $this->KolorWiersza($licznik);
            $kierownik='';
              while ($danekierownik = mysqli_fetch_array($wykonajkierownik)) {
                  $kierownik.=$danekierownik['imie'].' '.$danekierownik['nazwisko'].'-';
              }
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imiekierownik']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwiskokierownik']; ?>
            </td>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                     <?php
                         if ($_SESSION['ewnioski_admin'] == '1')
                         {
                             ?>
                              <form method="POST" id="pisanie_dla_inncyh_1_<?php echo $licznik; ?>" >
                                    <input type="hidden" name="numer_id_pisanie_dla_inncyh" value="<?php echo $dane['id']; ?>"/>
                                    <input type="hidden" name="numer_ewidencyjny_pisanie_dla_inncyh" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                                    <input type="hidden" name="aktualna_wartosc" value="<?php echo$dane['wnioski_dla_innych']; ?>"/>
                                    <input type="hidden" name="strona" value="sprawdz_zastepce"/>
                                    <input type="button" value="" class="edytowanie_guzik"
                                           onclick="document.forms['pisanie_dla_inncyh_1_<?php echo $licznik; ?>'].submit();"/><?php
                                            if($dane['wnioski_dla_innych']=='1') echo " tak";
                        else echo " nie";?>
                                </form>
                                <?php
                         }
                         else{
                              if($dane['wnioski_dla_innych']=='1') echo "tak";
                        else echo "nie";
                         }

                        ?>
            </td>
  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $kierownik; ?>
            </td>

</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);

    }
  /**
     * Raport sprawdzajacy czy zmienił sie jakiś brygadzista
     */
    function ZastepcaHR()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $zapytaniezastepca="SELECT numer_zastepcy_stan_godzin FROM ewidencja_szkolen.osoby o WHERE numer_zastepcy_stan_godzin!=''";
        $wykonajzastepca = mysqli_query($polaczenie, $zapytaniezastepca);
          $this->BladZapytania($polaczenie, $zapytaniezastepca, $_SESSION['ewnioski_login']);
        $numerzastepca=array();
         while ($dane = mysqli_fetch_array($wykonajzastepca)) {
             $tablicanumer=explode(',',$dane[0]);
             foreach ($tablicanumer as $value) {
                if(array_search($value, $numerzastepca)===false)
                    {
                        array_push($numerzastepca,$value);
                    }
            }
         }
         $numerzastepca=implode(',',$numerzastepca);
         if ($numerzastepca === '') $numerzastepca = "''"; // pusta lista w IN () to błąd składni SQL

        $zapytaniedata="Select MAX(stan_na_dzien) from stan_godzin";
        $wykonajdata = mysqli_query($polaczenie, $zapytaniedata);
        $this->BladZapytania($polaczenie, $zapytaniedata, $_SESSION['ewnioski_login']);
        $wynikdata=mysqli_fetch_row($wykonajdata);
        $zapytanie = "SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, o1.wnioski_dla_innych 
FROM `stan_godzin` sg
LEFT JOIN ewidencja_szkolen.osoby o on sg.numer_kierownik=o.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o1 on sg.numer_ewidencyjny=o1.numer_ewidencyjny 
WHERE  sg.stan_na_dzien='".$wynikdata[0]."' and sg.brygadzista='1'";
         $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $numer='';
         while ($dane = mysqli_fetch_array($wykonaj)) {
             $numer.=$dane['numer_ewidencyjny'].",";
         }
          $zapytanie = "SELECT DISTINCT (numer_kierownik)
FROM `stan_godzin` 
WHERE  stan_na_dzien='".$wynikdata[0]."' ";
         $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $kierownik='';
         while ($dane = mysqli_fetch_array($wykonaj)) {
             $kierownik.=$dane['numer_kierownik'].",";
         }
         $numer=$numer.$kierownik;
         $numer=substr($numer,0,-1);
         if ($numer === '') $numer = "''";
         if($_SESSION['ewnioski_sortowanie_sprawdz_zastepce']=='o1.wnioski_dla_innych') $sortowanie='sg.wnioski_dla_innych';
         else $sortowanie=$_SESSION['ewnioski_sortowanie_sprawdz_zastepce'];

         $zapytanie="SELECT sg.numer_ewidencyjny, sg.nazwisko,sg.imie, sg.wnioski_dla_innych, o.imie as imiekierownik, o.nazwisko as nazwiskokierownik, sg.id 
FROM ewidencja_szkolen.osoby sg
LEFT JOIN stan_godzin s on sg.numer_ewidencyjny=s.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby o ON s.numer_kierownik=o.numer_ewidencyjny
WHERE  s.stan_na_dzien='".$wynikdata[0]."' and s.profil='".$_SESSION['ewnioski_profil']."' and sg.numer_ewidencyjny  IN ($numerzastepca) 
and sg.numer_ewidencyjny NOT IN($numer) 
 ORDER BY $sortowanie ". $_SESSION['ewnioski_sortowanie_kierunek'] ." LIMIT $limit";
          $zapytanieliczba = "SELECT o.numer_ewidencyjny, o.nazwisko,o.imie, o.wnioski_dla_innych, ok.imie as imiekierownik, ok.nazwisko as nazwiskokierownik, o.id 
FROM ewidencja_szkolen.osoby o
LEFT JOIN stan_godzin s on o.numer_ewidencyjny=s.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby ok ON s.numer_kierownik=ok.numer_ewidencyjny
WHERE  s.stan_na_dzien='".$wynikdata[0]."' and s.profil='".$_SESSION['ewnioski_profil']."' and o.numer_ewidencyjny IN ($numerzastepca) 
and o.numer_ewidencyjny NOT IN($numer) 
ORDER BY o.wnioski_dla_innych DESC LIMIT $limit";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);

        ?>
        <br/>
            <table width="100%" cellspacing="0" class="border_top">
            <h2>Lista osób które są zastępcami i nie zarządzają pracownikami.</h2>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

        <?php
        if ($liczba_rekordow > $limit) {
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {
            ?>
            Wszystkie wyniki są wyświetlone na stronie.

        <?php }
        $this->PierwszyWierszZastepcaHR($liczba_rekordow);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DaneZastepcaHR($wykonaj, $liczba_rekordow);
        } else {
            ?>
          <br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do wyjatków z MPK-ami
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszZastepcaHR($liczba)
    {
         $polaczenie = $this->MysqliConnect();
        ?>

            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>

                 <td align="center" class="padding_5_all bg_11 bottom right">
                   <a href="?sprawdz_zastepce&sort=sg.numer_ewidencyjny&raport=sprawdz_zastepce"> <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.imie&raport=sprawdz_zastepce">
                        <b>Imię</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=sg.nazwisko&raport=sprawdz_zastepce">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.imie&raport=sprawdz_zastepce">
                        <b>Imię kierownika</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o.nazwisko&raport=sprawdz_zastepce">
                        <b> Nazwisko kierownika</b></a>
                </td>
               <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o1.wnioski_dla_innych&raport=sprawdz_zastepce">
                        <b> Pisanie wniosków dla innych</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?sprawdz_zastepce&sort=o1.wnioski_dla_innych&raport=sprawdz_zastepce">
                        <b> Czyim zastępcą jest</b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Zabierz uprawnienia brygadzisty</b>
                </td>
            </tr>

        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneZastepcaHR($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {

            $zapytaniekierownik="Select imie,nazwisko, numer_zastepcy_stan_godzin FROM ewidencja_szkolen.osoby where numer_zastepcy_stan_godzin LIKE '%".$dane['numer_ewidencyjny']."%'";
            $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
            $this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
            $color = $this->KolorWiersza($licznik);
            $kierownik='';
              while ($danekierownik = mysqli_fetch_array($wykonajkierownik)) {
                  $kierownik.=$danekierownik['imie'].' '.$danekierownik['nazwisko'].'-';
              }
            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imiekierownik']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwiskokierownik']; ?>
            </td>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                     <?php
                         if ($_SESSION['ewnioski_admin'] == '1')
                         {
                             ?>
                              <form method="POST" id="pisanie_dla_inncyh_2_<?php echo $licznik; ?>" >
                                    <input type="hidden" name="numer_id_pisanie_dla_inncyh" value="<?php echo $dane['id']; ?>"/>
                                    <input type="hidden" name="numer_ewidencyjny_pisanie_dla_inncyh" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                                    <input type="hidden" name="aktualna_wartosc" value="<?php echo$dane['wnioski_dla_innych']; ?>"/>
                                    <input type="hidden" name="strona" value="sprawdz_zastepce"/>
                                    <input type="button" value="" class="edytowanie_guzik"
                                           onclick="document.forms['pisanie_dla_inncyh_2_<?php echo $licznik; ?>'].submit();"/><?php
                                            if($dane['wnioski_dla_innych']=='1') echo " tak";
                        else echo " nie";?>
                                </form>
                                <?php
                         }
                         else{
                              if($dane['wnioski_dla_innych']=='1') echo "tak";
                        else echo "nie";
                         }

                        ?>
            </td>
  <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $kierownik; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                       <form method="POST" id="usun_zastepstwo_<?php echo $licznik; ?>">
                            <input type="hidden" name="usun_zastepstwo_numer" value="<?php echo $dane['numer_ewidencyjny']; ?>"/>
                            <input type="hidden" name="usun_zastepstwo_id" value="<?php echo $dane['id']; ?>"/>
                            <input type="hidden" name="strona" value="sprawdz_zastepce"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('usun_zastepstwo_<?php echo $licznik; ?>', 'Czy napewno chcesz zabarać uprawnienia następującej osobie(<?php echo  $dane['imie'].' '. $dane['nazwisko']; ?>) ');"/>
                        </form>
            </td>

</tr>
            <?php
            $licznik++;
        }
        mysqli_close($polaczenie);
    }
    function UsunZastepstwo()
    {
        $polaczenie = $this->MysqliConnect();
        $strona=$_POST['strona'];
        $zapytaniezastepstwo="SELECT id, numer_zastepcy_stan_godzin
        FROM ewidencja_szkolen.osoby WHERE  numer_zastepcy_stan_godzin LIKE '%" . $_POST['usun_zastepstwo_numer'] . "%'";
        $wykonajzastepstwo = mysqli_query($polaczenie, $zapytaniezastepstwo);
        $this->BladZapytania($polaczenie, $zapytaniezastepstwo, $_SESSION['ewnioski_login']);
         while ($danezastepstwo = mysqli_fetch_array($wykonajzastepstwo)) {
              $danezastepstwo[1]=str_replace($_POST['usun_zastepstwo_numer'].',','', $danezastepstwo[1]);
             $danezastepstwo[1]=str_replace($_POST['usun_zastepstwo_numer'],'', $danezastepstwo[1]);
             $zapytanieaktualizuj = "UPDATE ewidencja_szkolen.osoby SET  numer_zastepcy_stan_godzin='". $danezastepstwo[1]."'
                      WHERE id='" . $danezastepstwo[0] . "' ";
             $wykonajaktualizuj = mysqli_query($polaczenie, $zapytanieaktualizuj);
             $this->BladZapytania($polaczenie, $zapytanieaktualizuj, $_SESSION['ewnioski_login']);
             $this->ZapiszLog('Zapytanie;' . $zapytanieaktualizuj, $_SESSION['ewnioski_login']);
              }
        $zapytanie = "UPDATE ewidencja_szkolen.osoby SET  wnioski_dla_innych='0'
                      WHERE id='" . $_POST['usun_zastepstwo_id'] . "' ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
 $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_login']);

           if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Zmiana została wpisana.</h3></font>';
            }
           $zapytaniekierownik="SELECT o.email from stan_godzin s
LEFT JOIN ewidencja_szkolen.osoby o ON s.numer_kierownik=o.numer_ewidencyjny
 WHERE s.numer_ewidencyjny='" . $_POST['usun_zastepstwo_numer'] . "'
order by s.id desc
LIMIT 1 ";
             $wykonajkierownik = mysqli_query($polaczenie, $zapytaniekierownik);
$this->BladZapytania($polaczenie, $zapytaniekierownik, $_SESSION['ewnioski_login']);
$danekierownik=mysqli_fetch_row($wykonajkierownik);
$mailkierownik=$danekierownik[0];
              $zapytanie="SELECT imie, nazwisko, numer_ewidencyjny,email FROM ewidencja_szkolen.osoby where id='" . $_POST['usun_zastepstwo_id'] . "' ";
                 $wykonaj = mysqli_query($polaczenie, $zapytanie);
$this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
$dane=mysqli_fetch_row($wykonaj);
$osoba=$dane[0].' '.$dane[1];
$numer=$dane[2];
$adresat='STAR.RCP@motherson.com,'.$dane[3].','.$mailkierownik;
$page = $this->TrescMaila("<h2>Następująca osoba straciła uprawnienia do podgladu stanu urlopu i pisania wniosku dla innych. <br>
                Imię i nazwisko: $osoba <br>
                Numer ewidencyjny: $numer <br>");
                            $this->Wyslij($adresat, 'Zmiana uprawnien w eWnioskach', $page);
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?' . $strona);

    }
      /**
     * Raport wejść z systemu RCP
     */
    function RaportWejscLokalnie()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('numer_ewidencyjny', 'imie', 'nazwisko', 'data_wejscia','typ','nazwa','mpk');
        $array2 = array('ewnioski_wejscia_mumer_ewidencyjny', 'ewnioski_wejscia_imie', 'ewnioski_wejscia_nazwisko', 'ewnioski_wejscia_godzina_wejscia','ewnioski_typ_pobytu','ewnioski_miejsce_wejscia','ewnioski_odbicia_mpk');
        $warunekosoby="numer_ewidencyjny IN ";
         $osoby=$_SESSION['ewnioski_login'].',';
         $dodatkowy_warunek='';
         $warunek_having='';
         $widocznosc = array();
        $widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
      if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)and array_search('43', $widocznosc) === false) {
          $warunekosoby='';
$warunek_having=" having profil='".$_SESSION['ewnioski_profil']."'";
        }
        else
        {
            $warunek="numer_kierownik=".$_SESSION['ewnioski_login'];
            $miesieacwstecz=date ("Y-m",mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"))).'-01';
    $zapytaniespr="SELECT o.numer_ewidencyjny FROM ewidencja_szkolen.osoby o
 WHERE o.numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%' order by id desc";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
           while ($danespr=mysqli_fetch_array($wykonajspr))
                  {
                      $warunek.=" or numer_kierownik='".$danespr[0]."'";
                  }
                    $zapytaniesprosoby="SELECT DISTINCT numer_ewidencyjny FROM stan_godzin
                    WHERE $warunek  and stan_na_dzien> '".$miesieacwstecz."'";

                      $wykonajsprosoby = mysqli_query($polaczenie, $zapytaniesprosoby);
        $this->BladZapytania($polaczenie, $zapytaniesprosoby, $_SESSION['ewnioski_login']);
                       while ($danesprosoby=mysqli_fetch_array($wykonajsprosoby))
                  {
                      $osoby.=$danesprosoby[0].',';
                  }
                  $osoby=substr($osoby,0,-1);
                  $warunekosoby=" and numer_ewidencyjny IN (".$osoby.")";
        }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                if($array1[$i]=='numer_ewidencyjny')  $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                else{
                      if($array1[$i]=='typ')
                        {

                            if($_SESSION[$array2[$i]]=='0')
                                {

                                }
                                else
                                    {
                            $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                            }
                        }
                    else if($array1[$i]=='mpk' or $array1[$i]=='imie' or $array1[$i]=='nazwisko' )
                        {
                            if($_SESSION[$array2[$i]]=='0')
                                {

                                }
                                else
                                    {
                                        if($warunek_having=='')  $warunek_having = " having  " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                                        else{
                                        $warunek_having .= " and  " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                                        }
                            }
                        }
                     else  $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                }
                $licznik++;
            }
        }

        $zapytanie="SELECT * FROM (
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'RCP' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =1  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
UNION ALL
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'Kołowrotek' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =2  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
) t
  ORDER BY t." . $_SESSION['ewnioski_sortowanie_wejscia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . " LIMIT $limit  
";

         $zapytanieexcel="SELECT * FROM (
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'RCP' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =1  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
UNION ALL
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'Kołowrotek' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =2  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
) t
  ORDER BY t." . $_SESSION['ewnioski_sortowanie_wejscia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  
";
         $zapytanieliczba="SELECT * FROM (
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'RCP' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =1  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
UNION ALL
SELECT data_odbicia,DATE_FORMAT(data_odbicia,'%Y-%m-%d') as dzien, data_wejscia, 
CASE 
WHEN DATE_FORMAT(od.data_wejscia,'%H:%i')>'17:00'
THEN
		(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(DATE_ADD(od.data_odbicia, INTERVAL 1 DAY ),'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) 
	ELSE
(SELECT od_w.data_wyjscia 
FROM odbicia_rcp.odbicia od_w 
LEFT JOIN odbicia_rcp.kolowrotek ko_w on od_w.id_kolowrotka=ko_w.id 
WHERE DATE_FORMAT(od_w.data_odbicia,'%Y-%m-%d')=DATE_FORMAT(od.data_odbicia,'%Y-%m-%d') and  numer_ewidencyjny=od.numer_ewidencyjny and typ=ko.typ and data_wejscia='0000-00-00 00:00:00' LIMIT 1) END  as data_wyjscia
, od.numer_ewidencyjny, (SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil,
ko.nazwa,'Kołowrotek' as typ_odbicia
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id
WHERE typ =2  and data_wejscia!='0000-00-00 00:00:00'
and data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']." 23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
) t
  ORDER BY t." . $_SESSION['ewnioski_sortowanie_wejscia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  
";

        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        ?>
       <br>
        <form method="POST">
            <input type="hidden" name="excel_export_wejscia" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>"/>
            <input type="submit" value="Export widocznych danych do excela"/>
        </form>

<br><br>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font> <br>
           Nie wszystkie wyniki mogą być wyświetlone na stronie.</h3>
    <?php
        $this->PierwszyWierszWejsciaLokalnie($liczba_rekordow);
        if ($liczba_rekordow > 0) {
            $this->DaneWejsciaLokalnie($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);
    }
    /**
 * Wyświetlenie nagłówków i filtrów do raportu wejść
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszWejsciaLokalnie($liczba)
    {

        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=numer_ewidencyjny&raport=wejscia">
                        <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=imie&raport=wejscia">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=Nazwisko&raport=wejscia">
                        <b> Nazwisko </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=data_wejscia&raport=wejscia">
                        <b>Godzina wejścia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=data_wyjscia&raport=wejscia">
                        <b>Godzina wyjścia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                   Pobyt
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                   Zakład
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                 <a href="?wejscia&sort=typ_odbicia&raport=wejscia">
                        <b>Typ pobytu </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                <a href="?wejscia&sort=nazwa&raport=wejscia">
                        <b> Miejsce wejścia </b></a>

                </td>
                  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?wejscia&sort=mpk&raport=wejscia">
                        <b>Grupa </b></a>
                </td>
            </tr>
            <tr>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="number"  name="ewnioski_wejscia_mumer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_mumer_ewidencyjny'])) echo trim($_SESSION['ewnioski_wejscia_mumer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_imie" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_imie'])) echo trim($_SESSION['ewnioski_wejscia_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_nazwisko" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_nazwisko'])) echo trim($_SESSION['ewnioski_wejscia_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_godzina_wejscia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_godzina_wejscia'])) echo trim($_SESSION['ewnioski_wejscia_godzina_wejscia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                     <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST" id="form_f_1">
                    <select name="ewnioski_typ_pobytu" onchange="document.forms['form_f_1'].submit();">
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==0) echo " SELECTED"; ?> value="0">Wszystko</option>
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==1) echo " SELECTED"; ?> value="1">RCP</option>
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==2) echo " SELECTED"; ?> value="2">Kołowrotek</option>
                    </select>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST">
                        <input type="text" name="ewnioski_miejsce_wejscia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_miejsce_wejscia'])) echo trim($_SESSION['ewnioski_miejsce_wejscia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST">
                        <input type="text" name="ewnioski_odbicia_mpk" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_odbicia_mpk'])) echo trim($_SESSION['ewnioski_odbicia_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                </td>
            </tr>

        <?php
    }
     /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneWejsciaLokalnie($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;

        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
             $licz=0;
             $czaspracy='';
             if($dane['data_wejscia']==null)
                 {
                     $dane['data_wejscia']='';
                     $licz=1;
                 }
             if($dane['data_wyjscia']==null)
                 {
                     $dane['data_wyjscia']='';
                      $licz=1;
                 }
              $dane['data_wejscia'] = datetime::createfromformat('Y-m-d H:i:s',  $dane['data_wejscia']);
              $dane['data_wyjscia'] = datetime::createfromformat('Y-m-d H:i:s',  $dane['data_wyjscia']);
             if($licz==0 )                 {

                     $diff = $dane['data_wejscia']->diff($dane['data_wyjscia']);
                     $czas=(int)$diff->format('%H');
                     if($czas<8)
                         {
                             $color = $this->KolorWierszaCzerwony($licznik);
                             $licz=1;
                         }
                     $czaspracy=$dane['data_wejscia']->diff($dane['data_wyjscia']);
                     $czaspracy=$czaspracy->format('%H:%i');

                 }
             else
                 {
                     $color = $this->KolorWierszaCzerwony($licznik);
                 }
                     if($dane['data_wyjscia']==null) $wyjscie= 'Brak odbicia';
                     else $wyjscie= date_format($dane['data_wyjscia'], 'Y-m-d H:i:s');
                      if($dane['data_wejscia']==null) $wejscie= 'Brak odbicia';
                     else $wejscie= date_format($dane['data_wejscia'], 'Y-m-d H:i:s');

            ?>
            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwisko']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                    echo $wejscie;
                     ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
              <?php
                     echo $wyjscie;
                     ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['profil_nazwa']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo  $czaspracy; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo  $dane['typ_odbicia']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo  $dane['nazwa']; ?>
            </td>

             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['mpk']; ?>
            </td>
</tr>
            <?php
            $wiersz=$licznik+1;

            $licznik++;
        }

mysqli_close($polaczenie);
    }
     /**
     * Raport wejść z systemu RCP
     */
    function RaportOdbic()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('numer_ewidencyjny', 'imie', 'nazwisko', 'data_wejscia','typ','nazwa','mpk','data_wyjscia','data_odbicia');
        $array2 = array('ewnioski_wejscia_mumer_ewidencyjny', 'ewnioski_wejscia_imie', 'ewnioski_wejscia_nazwisko', 'ewnioski_wejscia_godzina_wejscia','ewnioski_typ_pobytu',
        'ewnioski_miejsce_wejscia','ewnioski_odbicia_mpk', 'ewnioski_wejscia_godzina_wyjscia', 'ewnioski_wejscia_godzina_odbicia');
        $warunekosoby="numer_ewidencyjny IN ";
         $osoby=$_SESSION['ewnioski_login'].',';
         $dodatkowy_warunek='';
         $warunek_having='';
         $widocznosc = array();
        $widocznosc = explode(',', $_SESSION['ewnioski_widocznosc']);
      if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)and array_search('43', $widocznosc) === false) {
          $warunekosoby='';
$warunek_having=" having profil='".$_SESSION['ewnioski_profil']."'";
        }
        else
        {
            $warunek="numer_kierownik=".$_SESSION['ewnioski_login'];
            $miesieacwstecz=date ("Y-m",mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"))).'-01';
    $zapytaniespr="SELECT o.numer_ewidencyjny FROM ewidencja_szkolen.osoby o
 WHERE o.numer_zastepcy_stan_godzin LIKE '%".$_SESSION['ewnioski_login']."%' order by id desc";
                $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
           while ($danespr=mysqli_fetch_array($wykonajspr))
                  {
                      $warunek.=" or numer_kierownik='".$danespr[0]."'";
                  }
                    $zapytaniesprosoby="SELECT DISTINCT numer_ewidencyjny FROM stan_godzin
                    WHERE $warunek  and stan_na_dzien> '".$miesieacwstecz."'";
                    var_dump($zapytaniesprosoby);
                      $wykonajsprosoby = mysqli_query($polaczenie, $zapytaniesprosoby);
        $this->BladZapytania($polaczenie, $zapytaniesprosoby, $_SESSION['ewnioski_login']);
                       while ($danesprosoby=mysqli_fetch_array($wykonajsprosoby))
                  {
                      $osoby.=$danesprosoby[0].',';
                  }
                  $osoby=substr($osoby,0,-1);
                  $warunekosoby=" and numer_ewidencyjny IN (".$osoby.")";
        }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                if($array1[$i]=='numer_ewidencyjny')  $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                else{
                     if($array1[$i]=='typ')
                        {
                            if($_SESSION[$array2[$i]]=='0')
                                {

                                }
                                else
                                    {
                            $dodatkowy_warunek .= " AND " . $array1[$i] . " = '" . trim($_SESSION[$array2[$i]]) . "'";
                            }
                        }
                    else if($array1[$i]=='mpk' or $array1[$i]=='imie' or $array1[$i]=='nazwisko')
                        {
                            if($_SESSION[$array2[$i]]=='0')
                                {

                                }
                                else
                                    {
                            $warunek_having .= " and  " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                            }
                        }
                     else  $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                }

                $licznik++;
            }
        }
        $zapytanie="SELECT numer_ewidencyjny, data_odbicia, data_wejscia, data_wyjscia, nazwa, typ ,
(SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id 
WHERE data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']."  23:59:59'
$warunekosoby $dodatkowy_warunek
$warunek_having
 ORDER BY " . $_SESSION['ewnioski_sortowanie_odbicia'] ." ". $_SESSION['ewnioski_sortowanie_kierunek']."
";
         $zapytanieliczba="SELECT numer_ewidencyjny, data_odbicia, data_wejscia, data_wyjscia, nazwa, typ ,
(SELECT sg.imie FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Imie,
(SELECT sg.nazwisko FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as Nazwisko,
(SELECT sg.mpk FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as mpk,
(SELECT p.nazwa FROM stan_godzin sg LEFT JOIN profil p on sg.profil=p.id  WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by p.id desc LIMIT 1) as profil_nazwa,
(SELECT sg.profil FROM stan_godzin sg WHERE sg.numer_ewidencyjny=od.numer_ewidencyjny order by id desc LIMIT 1) as profil
FROM odbicia_rcp.odbicia od 
LEFT JOIN odbicia_rcp.kolowrotek ko on od.id_kolowrotka=ko.id 
WHERE data_odbicia BETWEEN  '".$_SESSION['ewnioski_data_poczatkowa_stan_wejsc']."' and '".$_SESSION['ewnioski_data_koncowa_stan_wejsc']."  23:59:59'
$warunekosoby $dodatkowy_warunek $warunek_having
 ORDER BY " . $_SESSION['ewnioski_sortowanie_odbicia'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "
";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
        ?>
       <br>
        <form method="POST">
            <input type="hidden" name="excel_export_wejscia" value="<?php echo $this->ZapamietajZapytanie($zapytanie); ?>"/>
            <input type="submit" value="Export widocznych danych do excela"/>
        </form>

<br><br>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font> <br>
           Nie wszystkie wyniki mogą być wyświetlone na stronie.</h3>
    <?php
        $this->PierwszyWierszOdbic($liczba_rekordow);
        if ($liczba_rekordow > 0) {
            $this->DaneOdbic($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);
    }
     /**
 * Wyświetlenie nagłówków i filtrów do raportu wejść
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszOdbic($liczba)
    {

        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=numer_ewidencyjny&raport=odbicia">
                        <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=Imie&raport=odbicia">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=Nazwisko&raport=odbicia">
                        <b> Nazwisko </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=data_odbicia&raport=odbicia">
                        <b>Data odbicia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=data_wejscia&raport=odbicia">
                        <b>Data wejścia </b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=data_wyjscia&raport=odbicia">
                        <b>Data wyjścia </b></a>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Zakład</b>
                </td>
                 <td align="center" class="padding_5_all bg_11 bottom border_right">
                 <a href="?odbicia&sort=typ&raport=odbicia">
                        <b>Typ odbicia</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                <a href="?odbicia&sort=nazwa&raport=odbicia">
                        <b> Miejsce odbicia </b></a>
                </td>
                  <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?odbicia&sort=mpk&raport=odbicia">
                        <b>Grupa </b></a>
                </td>
            </tr>
            <tr>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="number"  name="ewnioski_wejscia_mumer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_mumer_ewidencyjny'])) echo trim($_SESSION['ewnioski_wejscia_mumer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_imie" id="filter_id" size="20" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_imie'])) echo trim($_SESSION['ewnioski_wejscia_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_nazwisko" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_nazwisko'])) echo trim($_SESSION['ewnioski_wejscia_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_godzina_odbicia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_godzina_odbicia'])) echo trim($_SESSION['ewnioski_wejscia_godzina_odbicia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_godzina_wejscia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_godzina_wejscia'])) echo trim($_SESSION['ewnioski_wejscia_godzina_wejscia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_wejscia_godzina_wyjscia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_wejscia_godzina_wyjscia'])) echo trim($_SESSION['ewnioski_wejscia_godzina_wyjscia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST" id="form_f_1">
                    <select name="ewnioski_typ_pobytu" onchange="document.forms['form_f_1'].submit();">
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==0) echo " SELECTED"; ?> value="0">Wszystko</option>
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==1) echo " SELECTED"; ?> value="1">RCP</option>
                      <option <?php if ($_SESSION['ewnioski_typ_pobytu']==2) echo " SELECTED"; ?> value="2">Kołowrotek</option>
                    </select>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST">
                        <input type="text" name="ewnioski_miejsce_wejscia" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_miejsce_wejscia'])) echo trim($_SESSION['ewnioski_miejsce_wejscia']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST">
                        <input type="text" name="ewnioski_odbicia_mpk" id="filter_id" size="6" class="border"
                               onclick="Zaznacz('filter_id')"
                               value="<?php if (isset($_SESSION['ewnioski_odbicia_mpk'])) echo trim($_SESSION['ewnioski_odbicia_mpk']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                </td>
            </tr>

        <?php
    }
     /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DaneOdbic($wykonaj, $liczba)
    {
         $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;

        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
             ?>

            <tr>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['Imie']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['Nazwisko']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                  <?php echo $dane['data_odbicia']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
              <?php echo $dane['data_wejscia']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_wyjscia']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['profil_nazwa']; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                    if($dane['typ']=='1') echo "RCP";
                    else echo "Kołowrotek"; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>

             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['mpk']; ?>
            </td>
</tr>
            <?php
            $wiersz=$licznik+1;

            $licznik++;
        }

mysqli_close($polaczenie);
    }
 /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function RaportPracaZdalna()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('rpz.numer_ewidencyjny', 'rpz.data_wpisu', 'rpz.typ_akcji', 'rpz.uzytkownik_wpisujacy','rpz.data_rozpoczecia_pracy','u.nazwisko','u.imie');
        $array2 = array('ewnioski_pz_numer_ewidencyjny', 'ewnioski_pz_data_rozpoczecia', 'ewnioski_pz_akcja', 'ewnioski_pz_wpisujacy', 'ewnioski_pz_data_wpisu','ewnioski_pz_imie','ewnioski_pz_nazwisko');
  $widocznosc = array();
  $tym=0;
  $warunek='';

         $dodatkowy_warunek="and  (rpz.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or (SELECT numer_kierownik FROM stan_godzin where numer_ewidencyjny=rpz.numer_ewidencyjny order by id desc LIMIT 1)='".$_SESSION['ewnioski_login']."')";
      if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)){
            $dodatkowy_warunek=" and rpz.profil='".$_SESSION['ewnioski_profil']."'";
        }

        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $zapytanie = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy,apz.nazwa, rpz.uzytkownik_wpisujacy,rpz.data_wpisu, u.imie as imie_osoby,
       u.nazwisko as nazwisko_osoby, u1.imie as imie_wpisujacy, u.nazwisko as nazwisko_wpisujacy, rpz.typ_akcji
, (SELECT numer_kierownik FROM stan_godzin where numer_ewidencyjny=rpz.numer_ewidencyjny order by id desc LIMIT 1) as numer_kierownika  
FROM rozpoczecie_pracy_zdalnej rpz
LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id
LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on rpz.uzytkownik_wpisujacy=u1.numer_ewidencyjny
WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
           $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;

        $zapytanieexcel = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy,apz.nazwa, rpz.uzytkownik_wpisujacy,rpz.data_wpisu, u.imie as imie_osoby, u.nazwisko as nazwisko_osoby, u1.imie as imie_wpisujacy, u.nazwisko as nazwisko_wpisujacy
, (SELECT numer_kierownik FROM stan_godzin where numer_ewidencyjny=rpz.numer_ewidencyjny order by id desc LIMIT 1) as numer_kierownika  
FROM rozpoczecie_pracy_zdalnej rpz
LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id
LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on rpz.uzytkownik_wpisujacy=u1.numer_ewidencyjny
WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
           $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . " " ;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy,apz.nazwa, rpz.uzytkownik_wpisujacy,rpz.data_wpisu, u.imie as imie_osoby, u.nazwisko as nazwisko_osoby, u1.imie as imie_wpisujacy, u.nazwisko as nazwisko_wpisujacy
, (SELECT numer_kierownik FROM stan_godzin where numer_ewidencyjny=rpz.numer_ewidencyjny order by id desc LIMIT 1) as numer_kierownika  
FROM rozpoczecie_pracy_zdalnej rpz
LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id
LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
LEFT JOIN ewidencja_szkolen.osoby u1 on rpz.uzytkownik_wpisujacy=u1.numer_ewidencyjny
WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."'
           $dodatkowy_warunek
            ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . " " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
         ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

                     <form method="POST" action="raporty.php?praca_zdalna" id="form_excel" >
                     <input type="hidden" name="zapytanie_excel_praca_zdalna" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="button" value="Eksport do Excela" onclick="document.getElementById('form_excel').submit();" />
                     </form>
                     <br><br>
                     <?php
                     if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)) {
            ?><form method="POST" action="raporty.php?praca_zdalna&dodaj_praca_zdalna" id="dodaj_praca_zdalna" >
                     <input type="button" value="Dodaj wpis pracy zdalnej" onclick="document.getElementById('dodaj_praca_zdalna').submit();" />
                     </form><?php
        }
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszPracaZdalna($liczba_rekordow,$widocznosc);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DanePracaZdalna($wykonaj, $liczba_rekordow,$widocznosc);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);

    }
    /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszPracaZdalna($liczba,$widocznosc)
    {
        $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>

                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.numer_ewidencyjny&raport=godziny">
                        <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.nazwisko&raport=godziny">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.imie&raport=godziny">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.imie&raport=godziny">
                        <b>Data akcji</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.imie&raport=godziny">
                        <b>Typ akcji</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?stan_urlopu&sort=sg.numer_ewidencyjny&raport=godziny">
                        <b>Numer wpisującego</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Modyfikuj</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                        <b>Usuń</b>
                </td>
            </tr>
            <tr>

                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>

                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_numer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_pz_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_imie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_imie'])) echo trim($_SESSION['ewnioski_pz_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_nazwisko" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_nazwisko'])) echo trim($_SESSION['ewnioski_pz_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_data_wpisu" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_data_wpisu'])) echo trim($_SESSION['ewnioski_pz_data_wpisu']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                   <form method="POST" id="form_f_1">
                    <select name="ewnioski_pz_akcja" onchange="document.forms['form_f_1'].submit();">
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM akcje_praca_zdalna order by nazwa  ";
                        $e1 = mysqli_query($polaczenie, $q1);
                        $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_SESSION['ewnioski_pz_akcja'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_wpisujacy" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_wpisujacy'])) echo trim($_SESSION['ewnioski_pz_wpisujacy']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>
                </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>

            </tr>

        <?php
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DanePracaZdalna($wykonaj, $liczba,$widocznosc)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                  <?php echo $dane['nazwisko_osoby']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie_osoby']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['data_rozpoczecia_pracy']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['nazwa']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                   <?php echo $dane['uzytkownik_wpisujacy']?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>"  class="padding_5_all bottom right">
                <?php if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)){ ?>
                    <form method="POST" id="modyfikacja_praca_zdalna_<?php echo $licznik; ?>">
                        <input type="hidden" name="modyfikacja_praca_zdalna_data_rozpoczecia" id="modyfikacja_praca_zdalna_data_rozpoczecia" value="<?php echo $dane['data_rozpoczecia_pracy']; ?>"/>
                        <input type="hidden" name="modyfikacja_praca_zdalna_typ_akcji" id="modyfikacja_praca_zdalna_typ_akcji" value="<?php echo $dane['typ_akcji']; ?>"/>
                        <input type="hidden" name="modyfikacja_praca_zdalna_id" id="modyfikacja_praca_zdalna_id" value="<?php echo $dane['id']; ?>"/>
                        <input type="button" value="" class="edytowanie_guzik"
                                   onclick="document.getElementById('modyfikacja_praca_zdalna_<?php echo $licznik; ?>').submit();"/>
                    </form>

                    <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość zmiany wpisu mają tylko pracownicy działu HR"/> <?php
                        }?>
                </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
            <?php if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)) { ?>
                       <form method="POST" id="usun_praca_zdalna_<?php echo $licznik; ?>">
                            <input type="hidden" name="usun_praca_zdalna" value="<?php echo $dane['id']; ?>"/>
                            <input type="button" value="" class="kasowanie_guzik"
                                   onclick="CzyNaPewno('usun_praca_zdalna_<?php echo $licznik; ?>', 'Czy napewno chcesz usunać wpis pracy zdalnej z numerem ID(<?php echo $dane['id']; ?>) ');"/>
                        </form>

                    <?php }
                     else { ?>
                              <img src="../img/s_rights.png" class="border"
                                 title="Możliwość usunięcia wpisu mają tylko pracownicy działu HR"/> <?php
                        }?>
            </td>
              <?php
              $licznik++;
        }
                    ?>

</tr> <?php
mysqli_close($polaczenie);
    }
    function UsunPracZdalna()
    {
         $polaczenie = $this->MysqliConnect();

            $zapytanie = "DELETE FROM rozpoczecie_pracy_zdalnej WHERE id ='".trim($_POST['usun_praca_zdalna'])."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wpis pracy zdalnej został usunięty</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?praca_zdalna');
    }
    function FormualrzEducjiPracyZdalne()
    {
        $polaczenie = $this->MysqliConnect();
        ?>
                <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center">
                        <h2><br>Edycja rekordu pracy zdalnej.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
                <form method="POST" action="raporty.php?praca_zdalna" id="form_zmiana">
                <tr>
                <td align="center">Data rozpoczęcia pracy zdalnej</td>
                    <td align="center">
                         <input type="datetime-local" name="modyfikuj_czas" value="<?php echo $_POST['modyfikacja_praca_zdalna_data_rozpoczecia']?>"/><br><br><br>
                          <input type="hidden" name="id_do_zmiany_praca_zdalna" value="<?php echo $_POST['modyfikacja_praca_zdalna_id']?>">
                    </td>
                </tr>
                <tr>
                <td align="center">Typ akcji</td>
                    <td align="center">
                         <select name="ewnioski_kategoria" >
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM akcje_praca_zdalna order by nazwa  ";
                        $e1 = mysqli_query($polaczenie, $q1);
                        $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option <?php if ($_POST['modyfikacja_praca_zdalna_typ_akcji'] == $wiersz['id']) echo "SELECTED"; ?>
                                        value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    </td>
                </tr>
                 <tr>
                    <td colspan="2" align="center">
                        <input type="button" value="Zmien wpis" onclick="document.getElementById('form_zmiana').submit();" />
                    </td>
                </tr>

                     </form>
                </table><?php
    }
    function ModyfikujPracaZdalnaHR()
    {
        $polaczenie = $this->MysqliConnect();
            $zapytanie = "UPDATE rozpoczecie_pracy_zdalnej SET data_rozpoczecia_pracy='" . trim($_POST['modyfikuj_czas']) . "', typ_akcji='" . trim($_POST['ewnioski_kategoria']) . "',
             uzytkownik_wpisujacy='" . trim($_SESSION['ewnioski_login']) . "', data_wpisu=now() WHERE id='".$_POST['id_do_zmiany_praca_zdalna']."'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wpis został zmieniony</h3></font>';
            }
        mysqli_close($polaczenie);
        $this->Przekieruj('raporty.php?praca_zdalna');
    }
    /**
     * Raport urlopów wpisanych przez użytkownika
     */
    function DataPracyZdalnej ($nazwa_pola,$id_akcji)
    {
        $nazwa_pola="(SELECT data_rozpoczecia_pracy
                            FROM rozpoczecie_pracy_zdalnej rpz$id_akcji 
                            WHERE DATE_FORMAT(rpz$id_akcji.data_rozpoczecia_pracy,'%Y-%m-%d') >= DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') 
                            and DATE_FORMAT(rpz$id_akcji.data_rozpoczecia_pracy,'%Y-%m-%d')<= DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d')  
                            and rpz$id_akcji.numer_ewidencyjny=rpz.numer_ewidencyjny
                            and rpz$id_akcji.typ_akcji='$id_akcji' LIMIT 1) as $nazwa_pola";
        return $nazwa_pola;
    }
    function RaportPracaZdalnaCzasPracy()
    {
        $polaczenie = $this->MysqliConnect();
        $licznik_wew = 1;
       $limit = $_SESSION['ewnioski_limit_stron'];
        $array1 = array('rpz.numer_ewidencyjny', 'rpz.data_wpisu', 'rpz.typ_akcji', 'rpz.uzytkownik_wpisujacy','rpz.data_rozpoczecia_pracy','u.nazwisko','u.imie');
        $array2 = array('ewnioski_pz_numer_ewidencyjny', 'ewnioski_pz_data_rozpoczecia', 'ewnioski_pz_akcja', 'ewnioski_pz_wpisujacy', 'ewnioski_pz_data_wpisu','ewnioski_pz_imie','ewnioski_pz_nazwisko');
  $widocznosc = array();
  $tym=0;
  $warunek='';
         $dodatkowy_warunek="and  (rpz.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' or (SELECT numer_kierownik FROM stan_godzin where numer_ewidencyjny=rpz.numer_ewidencyjny order by id desc LIMIT 1)='".$_SESSION['ewnioski_login']."')";
      if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)){
            $dodatkowy_warunek=" and rpz.profil='".$_SESSION['ewnioski_profil']."'";
        }
        $licznik = 0;
        for ($i = 0; $i < count($array1); $i++) {
            if (strlen(trim($_SESSION[$array2[$i]])) > 0) {
                    $dodatkowy_warunek .= " AND " . $array1[$i] . " like '%" . trim($_SESSION[$array2[$i]]) . "%'";
                $licznik++;
            }
        }
        $data_zakonczenia=$this->DataPracyZdalnej('data_zakonczenia','2');
        $data_wstrzymania=$this->DataPracyZdalnej('data_wstrzymania','3');
        $data_wznowienia=$this->DataPracyZdalnej('data_wznowienia','4');
        $zapytanie = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy as data_rozpoczecia, u.imie,u.nazwisko,
                    $data_zakonczenia,$data_wstrzymania,$data_wznowienia
                    FROM rozpoczecie_pracy_zdalnej rpz 
                    LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id 
                    LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
                    WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."' and rpz.typ_akcji='1'$dodatkowy_warunek
                                $dodatkowy_warunek            
                                ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . "  LIMIT " . $limit;

        $zapytanieexcel = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy as data_rozpoczecia, u.imie,u.nazwisko,
                    $data_zakonczenia,$data_wstrzymania,$data_wznowienia
                    FROM rozpoczecie_pracy_zdalnej rpz 
                    LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id 
                    LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
                    WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."' and rpz.typ_akcji='1'$dodatkowy_warunek
                    $dodatkowy_warunek            
                    ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . " " ;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $zapytanieliczba = "SELECT rpz.id, rpz.numer_ewidencyjny, rpz.data_rozpoczecia_pracy as data_rozpoczecia, u.imie,u.nazwisko,
                    $data_zakonczenia,$data_wstrzymania,$data_wznowienia
                    FROM rozpoczecie_pracy_zdalnej rpz 
                    LEFT JOIN akcje_praca_zdalna apz on rpz.typ_akcji=apz.id 
                    LEFT JOIN ewidencja_szkolen.osoby u on rpz.numer_ewidencyjny=u.numer_ewidencyjny
                    WHERE DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') >= '".$_SESSION['ewnioski_data_poczatkowa']."' and DATE_FORMAT(rpz.data_rozpoczecia_pracy,'%Y-%m-%d') <='".$_SESSION['ewnioski_data_koncowa']."' and rpz.typ_akcji='1'$dodatkowy_warunek
                    $dodatkowy_warunek            
                    ORDER BY " . $_SESSION['ewnioski_sortowanie_praca_zdalna'] . ' '. $_SESSION['ewnioski_sortowanie_kierunek'] . " " ;
        $wykonajliczba = mysqli_query($polaczenie, $zapytanieliczba);
        $this->BladZapytania($polaczenie, $zapytanieliczba, $_SESSION['ewnioski_login']);
        $liczba_rekordow = mysqli_num_rows($wykonajliczba);
         ?>
        <h3>Znaleziono następującą liczbę wpisów w bazie danych: <font
                    color="#00aa00"><?php echo $liczba_rekordow; ?></font></h3>

                     <form method="POST" action="raporty.php?praca_zdalna_czas_pracy" id="form_excel" >
                     <input type="hidden" name="zapytanie_excel_praca_zdalna_czas_pracy" value="<?php echo $this->ZapamietajZapytanie($zapytanieexcel); ?>">
                     <input type="button" value="Eksport do Excela" onclick="document.getElementById('form_excel').submit();" />
                     </form>
                     <br><br>
                     <?php
        if ($liczba_rekordow > $limit) {
            $liczba_rekordow=$limit;
            ?>
            <br/><font color="#aa0000">Uwaga wynik został ograniczony do nastepującej liczby wyników
                : <?php echo $limit; ?> </font>
            <?php
        } else {

            ?>
            Wszystkie wyniki są wyświetlone na stronie.
        <?php }
        $this->PierwszyWierszPracaZdalnaCzas($liczba_rekordow,$widocznosc);
        if (mysqli_num_rows($wykonaj) > 0) {
            $this->DanePracaZdalnaCzas($wykonaj, $liczba_rekordow);
        } else {
            ?>
            </table><br/>
            <h3>Results found: <font color="#aa0000">0</font></h3>
            <?php
        }
        mysqli_close($polaczenie);
        }
        /**
 * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
* @param $liczba Liczba rekordów zwrócona z bazy danych
 */
    function PierwszyWierszPracaZdalnaCzas($liczba,$widocznosc)
    {
        $polaczenie = $this->MysqliConnect();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>

                <td align="center" class="padding_5_all bg_11 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?praca_zdalna_czas_pracy&sort=sg.numer_ewidencyjny&raport=godziny">
                        <b>Numer ewidencyjny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?praca_zdalna_czas_pracy&sort=sg.nazwisko&raport=godziny">
                        <b>Nazwisko</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?praca_zdalna_czas_pracy&sort=sg.imie&raport=godziny">
                        <b>Imię</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?praca_zdalna_czas_pracy&sort=sg.imie&raport=godziny">
                        <b>Dzień</b></a>
                </td>
                <td align="center" class="padding_5_all bg_11 bottom border_right">
                    <a href="?praca_zdalna_czas_pracy&sort=sg.imie&raport=godziny">
                        <b>Czas pracy</b></a>
                </td>
            </tr>
            <tr>

                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>

                   <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_numer_ewidencyjny" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_pz_numer_ewidencyjny']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_imie" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_imie'])) echo trim($_SESSION['ewnioski_pz_imie']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_nazwisko" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_nazwisko'])) echo trim($_SESSION['ewnioski_pz_nazwisko']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                 <td  align="center" class="padding_5_all bg_11 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_pz_data_wpisu" id="filter_id" size="15" class="border"
                               value="<?php if (isset($_SESSION['ewnioski_pz_data_wpisu'])) echo trim($_SESSION['ewnioski_pz_data_wpisu']); ?>"/>
                        <input type="submit" value="+" class="guzik_dodaj_odejmij"/>
                    </form>
                </td>
                </td>
                     <td  align="center" class="padding_5_all bg_11 bottom right">
                    <img src="../img/remove_filter.png"/>
                </td>

            </tr>

        <?php
    }
    /**
     * Dane wyplute z bazy
     * @param $wykonaj Dane z zapytania
     * @param $liczba Liczba wierszy zwrócona z tabeli
     */
    function DanePracaZdalnaCzas($wykonaj, $liczba)
    {
        $licznik = 1;
        $polaczenie = $this->MysqliConnect();
        $suma=0;
        while ($dane = mysqli_fetch_array($wykonaj)) {
             $color = $this->KolorWiersza($licznik);
            ?>
            <tr>

            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
            </td>
             <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['numer_ewidencyjny']; ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                  <?php echo $dane['nazwisko']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $dane['imie']; ?>
            </td>
              <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo date_create((string)$dane['data_rozpoczecia'])->format('Y-m-d'); ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php
                    [$godzina, $minuta] = $this->CzasPracyZdalnej($dane);
                   if($godzina<0)
                       {
                           echo 'Brak zakończenia pracy zdalnej. Zgłoś to do HR';
                       }
                   else  echo $godzina.' godzin i '.$minuta.' minut' ?>
            </td>

            </tr>
              <?php
              $licznik++;
        }
                    ?>

 <?php
mysqli_close($polaczenie);
    }
    function FormualrzDodajPracaZdalna ()
    {
         $polaczenie = $this->MysqliConnect();
        ?>
                <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center">
                        <h2><br>Edycja rekordu pracy zdalnej.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
                <form method="POST" action="raporty.php?praca_zdalna" id="form_dodaj">
                <tr>
                <td align="center">Numer ewidencyjny</td>
                    <td align="center">
                         <input type="text" name="dodaj_praca_zdalna" />
                    </td>
                </tr>
                <tr>
                <td align="center">Data rozpoczęcia pracy zdalnej</td>
                    <td align="center">
                         <input type="datetime-local" name="dodaj_praca_zdalna_czas" />
                    </td>
                </tr>
                <tr>
                <td align="center">Typ akcji</td>
                    <td align="center">
                         <select name="dodaj_praca_zdalna_kategoria" >
                        <option value="">Wszystko</option>
                          <?php
                        $q1 = "SELECT * FROM akcje_praca_zdalna order by nazwa  ";
                        $e1 = mysqli_query($polaczenie, $q1);
                        $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <option  value="<?php echo $wiersz['id']; ?>"><?php echo $wiersz['nazwa']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    </td>
                </tr>
                 <tr>
                    <td colspan="2" align="center">
                        <input type="button" value="Dodaj wpis" onclick="document.getElementById('form_dodaj').submit();" />
                    </td>
                </tr>

                     </form>
                </table><?php
    }
     function DodajPracaZdalna()
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "INSERT INTO rozpoczecie_pracy_zdalnej (numer_ewidencyjny, data_rozpoczecia_pracy, typ_akcji, uzytkownik_wpisujacy, data_wpisu, profil)
           values ('".trim($_POST['dodaj_praca_zdalna'])."','".trim($_POST['dodaj_praca_zdalna_czas'])."','".trim($_POST['dodaj_praca_zdalna_kategoria'])."' ,'".trim( $_SESSION['ewnioski_login'])."',now(),'" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."')";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if (mysqli_affected_rows($polaczenie) > 0) {
            $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Rozpoczęcie/zakończenie/wstrzymanei pracy zostało odnotowane</h3></font>';
            mysqli_close($polaczenie);
            $this->Przekieruj('raporty.php?praca_zdalna');
        }
    }

}
