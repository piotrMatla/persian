<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 10.12.2018
 * Time: 11:23
 */

class Modyfikuj extends Common
{
    /**
     * Formularz wpisania wniosku urlopowego
     */
    function FormularzUrlop()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");

        $rok = date('Y');
        $polaczenie = $this->MysqliConnect();
       // if (strpos($_SESSION['ewnioski_mail'], 'brygadzista') === false) {
            $zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly, uz,stan_urlopu_godziny
FROM stan_godzin
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'   order by data_importu desc limit 1";
            $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
            $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
            $wynik = mysqli_fetch_row($wykonaj);
            $imie=$wynik['2'].' '.$wynik['1'];
            $stanurlopu = number_format($wynik['3'], 0);
            $stangodzin = number_format($wynik['4'], 1);
            $urlopzalegly = number_format($wynik['5'], 0);
        $uz = number_format($wynik['6'], 0);
        $stanurlopugodziny = number_format($wynik['7'], 0);
        ?>
        <form method="POST" action="modyfikuj.php?wpisz" id="form_1" >
        <table border="0" align="center">
            <?php if ($_SESSION['ewnioski_mail_kierownik'] == '' or $_SESSION['ewnioski_mail_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Nie masz przypisanego przełożonego. <br>Możesz wpisać wniosiek ale nie trafi on do
                        akceptacji w
                        wersji elektronicznej do przełożonego.<br>Skontaktuj sie z następujacymi osobami w celu
                        uzupełnienia tych
                        danych:<br>
                        <?php echo $confW['UzupelnienieDanych'] ?></h2>
                    <br/><br/>
                </td>
                </tr><?php
            }
            if ($_SESSION['ewnioski_kierownik'] == '' or $_SESSION['ewnioski_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Twój przełożony nie założył jeszcze konta do programu eWnioski lub podał zły adres mailowy. <br>Najpierw on musi mieć konto z prawidłowym e-mailem aby wniosek został przypisany do akceptacji przez niego.<br>
                      </h2>
                    <br/><br/>
                </td>
                </tr><?php
            } ?>
               <tr>
                <td colspan="2" align="center"> <?php
            if (isset($_SESSION['ewnioski_komunikat'])) {
                $this->WyswietlKomunikat();
            }
            ?>
                </td>
               </tr>
            <tr>
                <td colspan="2" align="center">
                    <h2><b>Prosze wypełnić wniosek o urlop</b></h2>
                    <br/><br/>
                </td>
            </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Imię i nazwisko </b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="imie_nazwisko_stan"><?php echo $imie; ?></span> </b>
                    </td>
                </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Stan urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_urlopu"><?php echo $stanurlopu; ?></span> dni</b>
                    <input type="hidden" name="stan_urlopu" id="stan_urlopu_1" value="<?php echo $stanurlopu;?>">
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Stan urlopu w godzinach</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_urlopu_godziny"><?php echo $stanurlopugodziny; ?></span> godzin</b>
                </td>
            </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>W tym urlop zaległy</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="urlop_zalegly"><?php echo $urlopzalegly; ?></span> dni </b>
                    </td>
                </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Wykorzystany urlop na żądanie</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_uz"><?php echo $uz; ?></span> dni </b>
                </td>
            </tr>
            <td align="left" class="padding_10_all">
                <b>Stan godzin</b>
            </td>
            <td align="left" class="padding_10_all">
                <b><span id="stan_godzin"><?php echo $stangodzin; ?></span> godzin </b>
            </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Rodzaj urlopu</b>
                </td>
                <td align="left" class="padding_10_all">

                    <?php
                    // $wynik['wnioski_dla_innych']
                   // var_dump($_SESSION['inny_pracownik']);
                    if(isset($_SESSION['inny_pracownik']) AND $_SESSION['inny_pracownik'] != 0) $dodatkowy_warunek_zapytania = "";
                    else $dodatkowy_warunek_zapytania = "AND kod!='ZŚP' ";

                    $q1 = "SELECT * FROM kategoria where typ='1' ".$dodatkowy_warunek_zapytania;
                    $e1 = mysqli_query($polaczenie, $q1);
                    $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_login']);
                    if (mysqli_num_rows($e1) > 0) {
                        while ($wiersz = mysqli_fetch_array($e1)) {
                            ?>
                            <input type="radio" name="rodzaj_urlopu" id="rodzaj_urlopu_<?php echo $wiersz['id']; ?>"
                                   value="<?php echo $wiersz['id']; ?>"
                                   onclick="Pokaz(<?php echo $wiersz['id']; ?>)"><?php echo $wiersz['nazwa']; ?><br>
                        <?php }
                    }
                    ?>

                </td>
            </tr>
            <tr id="okolicznosciowy" class="hide">
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Proszę pamiętać o dostarczeniu aktu urodzenia/zgonu/ślubu/zaświadzczenia ze stacji
                        krwiodawstwa</br>W innym przypadku urlop nie bedzie wypłacony.</h2>
                </td>
            </tr>
            <tr id="stawienicto" class="hide">
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Proszę pamiętać o dostarczeniu potwierdzenia stawiennictwa w sądzie </br>W innym przypadku będzie to nieobecność nieusprawiedliwiona.</h2>
                </td>
            </tr>
            <tr id="dziecko" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Imię dziecka/ Imię i nazwisko</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="imie_dziecka" id="imie_dziecka"/>
                </td>
            </tr>
            <tr id="dziecko2" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Data urodzenia dziecka</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="date" name="data_urodzenia" id="data_urodzenia"/>
                </td>
            </tr>
            <tr id="dziecko3" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Zaznacz jeśli cały dzień</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="checkbox" name="caly_dzien" id="caly_dzien" checked="checked" onchange="PokazGodziny()"/>
                </td>
            </tr>
            <tr id="przyczyna" class="hide">
                <td align="left" class="padding_10_all">
                    <b id="przyczyna_label">Przyczyna konieczności</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="przyczyna_koniecznosci" id="przyczyna_koniecznosci"/>
                    <br><span id="przyczyna_wymagane" style="color:red; font-size: 11px; display:none;">To pole musi być wypełnione</span>
                </td>
            </tr>
            <tr id="czlonek" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Zaznacz jeśli to członek rodziny</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="checkbox" name="czlonek_rodziny" id="czlonek_rodziny" checked="checked" onchange="PokazPole('czlonek_rodziny','stopien','adres')"/>
                </td>
            </tr>
            <tr id="stopien" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Stopien pokrewieństwa</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="stopien_pokrewienstwa" id="stopien_pokrewienstwa"/>
                </td>
            </tr>
            <tr id="adres" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Adres zamieszkania </b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="adres_zamieszkania" id="adres_zamieszkania"/>
                </td>
            </tr>
            <?php
            if (strpos(strtolower($_SESSION['ewnioski_mail']), 'brygadzista') !== false or  $_SESSION['inny_pracownik']=='1') { ?>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Numer ewidencyjny pracownika</b>
                    </td>
                    <td align="left" class="padding_10_all">
                       <input type="text" name="numer_ewidencyjny" id="numer_ewidencyjny" value="<?php echo $_SESSION['ewnioski_login']?>" autocomplete="off" onblur="PobierzDane('numer_ewidencyjny','imie_nazwisko');setTimeout(function (){PobierzUrlop('numer_ewidencyjny')},500) " />
                   </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Imię i nazwisko pracownika</b>
                    <td align="left" class="padding_10_all">
                        <input type="text" name="imie_nazwisko1" id="imie_nazwisko1"  value="<?php echo  $_SESSION['ewnioski_full_name']?>" disabled/>
                        <input type="hidden" name="imie_nazwisko" id="imie_nazwisko" value="<?php echo  $_SESSION['ewnioski_full_name']?>"/>
                    </td>
                </tr>
            <?php } else {
                ?>
                <input type="hidden" name="numer_ewidencyjny" id="numer_ewidencyjny"
                       value="<?php echo $_SESSION['ewnioski_login']; ?>"/>
                <input type="hidden" name="imie_nazwisko" id="imie_nazwisko"
                       value="<?php echo $_SESSION['ewnioski_full_name'] ?>"/>
                <?php
            }
            ?>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Początek urlopu</b>
                </td>
                <td align="left" class="padding_10_all" >
                    <input type="date" name="poczatek_urlopu" id="poczatek_urlopu"/>
                </td>
            </tr>
            <tr id="koniec_urlopu">
                <td align="left" class="padding_10_all">
                    <b>Koniec urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="date" name="koniec_urlopu" id="koniec_urlopu"/>
                </td>
            </tr>
            <tr id="liczba_godzin" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Liczba godzin </b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="number" name="liczba_godzin" id="liczba_godzin" min="0"/>
                </td>
            </tr>

            <tr>
                <td colspan="2" align="center">
                    <input type="hidden" name="zlagrupa" id="zlagrupa" value="dobrze">
                    <input type="button" value="Wpisz urlop" id="weryfikacja"
                           onclick="Weryfikacja()"/>
                </td>
            </tr>
        </table>
        </form>
        <?php
        mysqli_close($polaczenie);
    }

    /**
     * Wpisanie nowego urlopu do bazy danych
     */
    function WpiszUrlop()
    {

if(isset($_GET['dotyk'])) $strona='dotyk.php?raport_urlopow&czas';
else $strona='raporty.php?urlop';
        $_SESSION['ewnioski_komunikat'] = '<font color="red"><h3>Wystąpił bład podczas wpisywania urlopu. Skontaktuj się z administratorem systemu. </h3></font>';
        $kierownik = $_SESSION['ewnioski_kierownik'];
        $polaczenie = $this->MysqliConnect();
        if ($_POST['liczba_godzin'] != '') {
            $_POST['koniec_urlopu'] = $_POST['poczatek_urlopu'];
        }
        $wpiszgodzinyenova=abs($_POST['liczba_godzin']);
        $poczatek = strtotime($_POST['poczatek_urlopu']);
        $koniec = strtotime($_POST['koniec_urlopu']);
        $miesiacpoczatek = date('m', $poczatek);
        $miesiackoniec = date('m', $koniec);
        $rokpoczatek = date('Y', $poczatek);
        $rokkoniec = date('Y', $koniec);
        $tymdata = strtotime($_POST['poczatek_urlopu']);
        $tymdataliczba = strtotime($_POST['poczatek_urlopu']);
        $status = '1';
        $mail = '1';
        $poleprzelozony = '';
        $dataprzelozony = '';

        if ($_POST['numer_ewidencyjny'] != $_SESSION['ewnioski_login'] ) {
            $poleprzelozony = ',data_kierownik, login_kierownik';
            $dataprzelozony = ",now(),'" . $_SESSION['ewnioski_full_name'] . "'";
            $mail = '2';
            // $kierownik = $_SESSION['ewnioski_kierownik'];
            if ($_POST['rodzaj_urlopu'] == 2) {
                $status = "8";
            } else {
                // Sprawdzamy czy osoba wpisująca to bezpośredni przełożony pracownika
                $q_kierownik = "SELECT numer_kierownik FROM stan_godzin 
                        WHERE numer_ewidencyjny='".$_POST['numer_ewidencyjny']."' 
                        ORDER BY id DESC LIMIT 1";
                $e_kierownik = mysqli_query($polaczenie, $q_kierownik) OR DIE(mysqli_error($polaczenie));
                $d_kierownik = mysqli_fetch_array($e_kierownik);

                // Jeśli osoba wpisująca JEST bezpośrednim przełożonym
                if($d_kierownik && $d_kierownik['numer_kierownik'] == $_SESSION['ewnioski_login']) {
                    $status = "2"; // Automatycznie zaakceptowany przez bezpośredniego przełożonego
                }
                else {
                    // Osoba wpisująca NIE jest bezpośrednim przełożonym
                    /** Zakaz akceptacji automatycznej wniosków brygadzistą */
                    if(Urlopy::SprawdzCzyBrygadzista($_POST['numer_ewidencyjny'])) {
                        $status = "1"; // Czeka na akceptację właściwego kierownika
                    }
                    else {
                        $status = "2"; // Automatycznie zaakceptowany
                    }
                }
            }
        }

        // Jeżeli typ wniosku to: Zwolnienie z obowiązku świadczenia pracy, i dotyczy tej samej osoby która go wpisuję to akceptacja nie jest automatyczna
        if($_POST['rodzaj_urlopu'] == 3 AND $_POST['numer_ewidencyjny'] == $_SESSION['ewnioski_login']) {
            $status = '1';
        }

        // Jeżeli wniosek dotyczy brygadzisty i typ wniosku to: Zwolnienie z obowiązku świadczenia pracy, wniosek nie akceptuję się automatycznie
        $q1 = "SELECT * FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$_POST['numer_ewidencyjny']."'";
        $e1 = mysqli_query($polaczenie, $q1) OR DIE(mysqli_error($polaczenie));
        $d1 = mysqli_fetch_array($e1);
        if($_POST['rodzaj_urlopu'] == 3 AND preg_match("/brygadzista/i", $d1['email']) == true) $status = '1';

        if ($_POST['rodzaj_urlopu'] == 1) {
            $liczbadnieksport = 0;
            while ($tymdataliczba <= $koniec) {
                if (((int)date('N', $tymdataliczba)) < 6 or (date("Y-m-d",$tymdata)=='2022-04-23')) {
                    $datazapytanie = date('m-d', $tymdataliczba);
                    $zapytaniespr = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%m-%d')='" . $datazapytanie . "' and stale='1'";
                    $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
                    $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
                    $danespr = mysqli_fetch_row($wykonajspr);
                    if ($danespr != '' or $danespr===NULL) {
                        if ($danespr[1] == 0) {
                            $datazapytanie2 = date('Y-m-d', $tymdataliczba);
                            $zapytaniespr2 = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%Y-%m-%d')='" . $datazapytanie2 . "'";
                            $wykonajspr2 = mysqli_query($polaczenie, $zapytaniespr2);
                            $this->BladZapytania($polaczenie, $zapytaniespr2, $_SESSION['ewnioski_login']);
                            $danespr2 = mysqli_fetch_row($wykonajspr2);
                            if ($danespr2 == '') {
                                $liczbadnieksport++;;
                            }
                        }
                    } else {
                        $liczbadnieksport++;
                    }
                }
                $tymdataliczba = strtotime(" + 1 day", $tymdataliczba);
            }
            if ($_POST['stan_urlopu'] != 'Pracownik agencyjny') {
                if ($miesiacpoczatek == '01' and date("m") == '12') {
                }
                else{
                     if ($_POST['stan_urlopu'] < $liczbadnieksport) {
                        $_SESSION['ewnioski_komunikat'] = '<font color="red"><h1>Wystąpił błąd podczas wpisywania urlopu. <br>Liczba urlopu dostępnego jest mniejsza niż chcesz wypisać.<br>Liczba dni dostępnych: ' . $_POST['stan_urlopu'] . ' <br>a wpisano: ' . $liczbadnieksport . '  </h1></font>';
                        if (isset($_GET['dotyk'])) $strona = "dotyk.php?dodaj_urlop";
                        else $strona = "modyfikuj.php?urlop";
                        ?>
                        <script type="text/javascript">
                            (window.location = '<?php echo $strona?>');
                        </script>
                        <?php
                        exit;
                    }

            }
        }
    }
        if ($miesiacpoczatek == $miesiackoniec) {
            $liczbadnieksport = 0;
            while ($tymdata <= $koniec) {
                if (((int)date('N', $tymdata)) < 6 or (date("Y-m-d",$tymdata)=='2022-04-23')) {
                    $datazapytanie = date('m-d', $tymdata);
                    $zapytaniespr = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%m-%d')='" . $datazapytanie . "'";
                    $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
                    $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
                    $danespr = mysqli_fetch_row($wykonajspr);
                    if ($danespr != '') {
                        if ($danespr[1] == 0) {
                            $datazapytanie2 = date('Y-m-d', $tymdata);
                            $zapytaniespr2 = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%Y-%m-%d')='" . $datazapytanie2 . "'";
                            $wykonajspr2 = mysqli_query($polaczenie, $zapytaniespr2);
                            $this->BladZapytania($polaczenie, $zapytaniespr2, $_SESSION['ewnioski_login']);
                            $danespr2 = mysqli_fetch_row($wykonajspr2);
                            if ($danespr2 == '') {
                                $liczbadnieksport++;;
                            }
                        }
                    } else {
                        $liczbadnieksport++;
                    }
                }
                $tymdata = strtotime(" + 1 day", $tymdata);
            }
            if($_POST['liczba_godzin']!='')
            {
                $_POST['liczba_godzin']=str_replace(',','.', $_POST['liczba_godzin']);
                $liczbadnieksport=(float) $_POST['liczba_godzin']/(float) 8;
            }
            $zapytaniesprawdzwniosek="SELECT * FROM wniosek where numer_ewidencyjny='" . trim($_POST['numer_ewidencyjny']) . "' and id_status!=6
            and(( poczatek_wolnego<='" . trim($_POST['poczatek_urlopu']) . "' and  koniec_wolnego>='" . trim($_POST['poczatek_urlopu']) . "') 
            or ( poczatek_wolnego<='" . trim($_POST['koniec_urlopu']) . "' and  koniec_wolnego>='" . trim($_POST['koniec_urlopu']) . "') )";
            $wykonajsprawdzwniosek = mysqli_query($polaczenie, $zapytaniesprawdzwniosek);
            $this->BladZapytania($polaczenie, $zapytaniesprawdzwniosek, $_SESSION['ewnioski_full_name']);
            if (mysqli_num_rows($wykonajsprawdzwniosek) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="red"><h1>Dla tej osoby w tych dniach jest już wpisany wniosek urlopowy.</br> Program nie pozwala wpisać dwóch wniosków w tym samym czasie dla jednej osoby </h1></font>';
                mysqli_close($polaczenie);
                $strona = "modyfikuj.php?urlop";
        ?>
        <script type="text/javascript">
            (window.location = '<?php echo $strona ?>');
        </script>
        <?php
        exit;
            }
            $zapytanie = "INSERT INTO wniosek (id_kategorii, data_wpisania, osoba_wpisujaca, imie_nazwisko, poczatek_wolnego, koniec_wolnego,  liczba_dni_eksport, imie_dziecka, data_urodzenia_dziecka, numer_ewidencyjny,
 dane_mpk, id_status, numer_ewidencyjny_wpisujacy,przyczyna,stopien_pokrewienstwa,profil,adres_zamieszkania,liczba_godzin_enova $poleprzelozony )
           values ('" . trim($_POST['rodzaj_urlopu']) . "',now(),'" . trim($_SESSION['ewnioski_full_name']) . "','" . trim($_POST['imie_nazwisko']) . "','" . trim($_POST['poczatek_urlopu']) . "',
           '" . trim($_POST['koniec_urlopu']) . "','" . $liczbadnieksport . "','" . trim($_POST['imie_dziecka']) . "','" . trim($_POST['data_urodzenia']) . "','" . trim($_POST['numer_ewidencyjny']) . "'
          ,'" . trim($kierownik) . "','" . $status . "','" . trim($_SESSION['ewnioski_login']) . "','" . trim($_POST['przyczyna_koniecznosci']) . "','" . trim($_POST['stopien_pokrewienstwa']) . "','" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."','" . trim($_POST['adres_zamieszkania']) . "','" . trim($wpiszgodzinyenova) . "'$dataprzelozony)";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Urlop został wpisany do systemu</h3></font>';
                if ($mail == '1') {
                    $adresat = $_SESSION['ewnioski_mail'] . ',' . $_SESSION['ewnioski_mail_kierownik'];
                    $osoba = $_POST['imie_nazwisko'];
                    $przedzial = $_POST['poczatek_urlopu'] . " - " . $_POST['koniec_urlopu'];
                    $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
        <h2>Wpisano nowy wniosek urlopowy dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/raporty.php?urlop > eWniosków</a>
         </center>
                                </body></html>
                                ";

                    $this->Wyslij($adresat, 'Wpisano nowy wniosek urlopowy', $page);
                }
            }
        } else {
            if ($rokpoczatek == $rokkoniec) {
                $tymmiesiac = $miesiacpoczatek;
                $liczbadnieksport = 0;
                $poczatekwpisz = $tymdata;
                while ($tymdata <= $koniec) {
                    $koniecwpisz = $tymdata;
                    if (((int)date('N', $tymdata)) < 6) {
                        $datazapytanie = date('m-d', $tymdata);
                        $zapytaniespr = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%m-%d')='" . $datazapytanie . "'";
                        $wykonajspr = mysqli_query($polaczenie, $zapytaniespr);
                        $this->BladZapytania($polaczenie, $zapytaniespr, $_SESSION['ewnioski_login']);
                        $danespr = mysqli_fetch_row($wykonajspr);
                        if ($danespr != '') {
                            if ($danespr[1] == 0) {
                                $datazapytanie2 = date('Y-m-d', $tymdata);
                                $zapytaniespr2 = "SELECT dzien,stale  FROM dni_wolne WHERE  DATE_FORMAT(dzien, '%Y-%m-%d')='" . $datazapytanie2 . "'";
                                $wykonajspr2 = mysqli_query($polaczenie, $zapytaniespr2);
                                $this->BladZapytania($polaczenie, $zapytaniespr2, $_SESSION['ewnioski_login']);
                                $danespr2 = mysqli_fetch_row($wykonajspr2);
                                if ($danespr2 == '') {
                                    $liczbadnieksport++;
                                }
                            }
                        } else {
                            $liczbadnieksport++;
                        }
                    }
                    $tymdata = strtotime(" + 1 day", $tymdata);
                    if ($tymmiesiac != date('m', $tymdata)) {
                        $zapytanie = "INSERT INTO wniosek (id_kategorii, data_wpisania, osoba_wpisujaca, imie_nazwisko, poczatek_wolnego, koniec_wolnego,  liczba_dni_eksport, imie_dziecka, data_urodzenia_dziecka, numer_ewidencyjny, dane_mpk, id_status, numer_ewidencyjny_wpisujacy,profil $poleprzelozony)
           values ('" . trim($_POST['rodzaj_urlopu']) . "',now(),'" . trim($_SESSION['ewnioski_full_name']) . "','" . trim($_POST['imie_nazwisko']) . "','" . trim(date('Y-m-d', $poczatekwpisz)) . "',
           '" . trim(date('Y-m-d', $koniecwpisz)) . "','" . $liczbadnieksport . "','" . trim($_POST['imie_dziecka']) . "','" . trim($_POST['data_urodzenia']) . "','" . trim($_POST['numer_ewidencyjny']) . "'
          ,'" . trim($kierownik) . "','" . $status . "','" . trim($_SESSION['ewnioski_login']) . "','" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."'  $dataprzelozony)";
                       $wykonaj = mysqli_query($polaczenie, $zapytanie);
                        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
                        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
                        if (mysqli_affected_rows($polaczenie) > 0) {
                            $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Urlop został wpisany do systemu</h3></font>';
                            if ($mail == '1') {
                                $adresat = $_SESSION['ewnioski_mail'] . ',' . $_SESSION['ewnioski_mail_kierownik'];
                                $osoba = $_POST['imie_nazwisko'];
                                $przedzial = $_POST['poczatek_urlopu'] . " - " . $_POST['koniec_urlopu'];
                                $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
        <h2>Wpisano nowy wniosek urlopowy dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/raporty.php?urlop > eWniosków</a>
         </center>
                                </body></html>
                                ";
                                $this->Wyslij($adresat, 'Wpisano nowy wniosek urlopowy', $page);
                            }
                        }

                        $tymmiesiac = date('m', $tymdata);
                        $poczatekwpisz = strtotime(" + 1 day", $koniecwpisz);
                        $liczbadnieksport = 0;
                    }
                }
                if ($liczbadnieksport != 0) {
                    $zapytanie = "INSERT INTO wniosek (id_kategorii, data_wpisania, osoba_wpisujaca, imie_nazwisko, poczatek_wolnego, koniec_wolnego,  liczba_dni_eksport, imie_dziecka, data_urodzenia_dziecka,  numer_ewidencyjny, dane_mpk, id_status,numer_ewidencyjny_wpisujacy,profil $poleprzelozony)
           values ('" . trim($_POST['rodzaj_urlopu']) . "',now(),'" . trim($_SESSION['ewnioski_full_name']) . "','" . trim($_POST['imie_nazwisko']) . "','" . trim(date('Y-m-d', $poczatekwpisz)) . "',
           '" . trim(date('Y-m-d', $koniecwpisz)) . "','" . $liczbadnieksport . "','" . trim($_POST['imie_dziecka']) . "','" . trim($_POST['data_urodzenia']) . "','" . trim($_POST['numer_ewidencyjny']) . "'
          ,'" . trim($kierownik) . "','" . $status . "' ,'" . trim($_SESSION['ewnioski_login']) . "','" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."' $dataprzelozony)";
                    $wykonaj = mysqli_query($polaczenie, $zapytanie);
                    $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
                    $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
                    if (mysqli_affected_rows($polaczenie) > 0) {
                        $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Urlop został wpisany do systemu</h3></font>';
                        if ($mail == '1') {
                            $adresat = $_SESSION['ewnioski_mail'] . ',' . $_SESSION['ewnioski_mail_kierownik'];
                            $osoba = $_POST['imie_nazwisko'];
                            $przedzial = $_POST['poczatek_urlopu'] . " - " . $_POST['koniec_urlopu'];
                            $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
        <h2>Wpisano nowy wniosek urlopowy dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
        Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/raporty.php?urlop > eWniosków</a>
         </center>
                                </body></html>
                                ";
                            $this->Wyslij($adresat, 'Wpisano nowy wniosek urlopowy', $page);
                        }
                    }
                }
            }
            else{
                $_SESSION['ewnioski_komunikat'] = '<font color="red"><h1>Program nie obsługuje możliwości wpisywania urlopu na przełomie różnych roków.</br> Proszę wpisać dwa osobne wnioski na każdy rok z osobna. </h1></font>';
            }
        }

        mysqli_close($polaczenie);
        ?>
        <script type="text/javascript">
            (window.location = '<?php echo $strona ?>');
        </script>
        <?php
        exit;
    }

    /**
     * Formularz wpisania wniosku o zwolnienie
     */
    function FormularzZwolnienie()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");
        $polaczenie = $this->MysqliConnect();
        $zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly,uz
FROM stan_godzin
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'   order by data_importu desc limit 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
        $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
        $wynik = mysqli_fetch_row($wykonaj);
        $imie=$wynik['2'].' '.$wynik['1'];
        $stanurlopu = number_format($wynik['3'], 0);
        $stangodzin = number_format($wynik['4'], 1);
        $urlopzalegly = number_format($wynik['5'], 0);
        $uz = number_format($wynik['6'], 0);

        ?>

        <style>
            .multi-datepicker {
                display: inline-block;
                border: 1px solid #ccc;
                border-radius: 4px;
                padding: 8px;
                width: 220px;
                font-family: inherit;
                user-select: none;
            }
            .dp-header { display: flex; justify-content: space-between; align-items: center; font-weight: bold; margin-bottom: 6px; }
            .dp-nav { border: none; background: #f0f0f0; cursor: pointer; padding: 2px 8px; border-radius: 3px; }
            .dp-nav:hover { background: #e0e0e0; }
            .dp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
            .dp-daylabel { text-align: center; font-size: 0.8em; color: #888; padding: 2px 0; }
            .dp-day { text-align: center; padding: 4px 0; border-radius: 3px; cursor: pointer; }
            .dp-day:hover { background: #eef; }
            .dp-day.dp-selected { background: #2b6cb0; color: white; font-weight: bold; }
            .selected-days { margin-top: 6px; font-size: 0.9em; color: #444; }
        </style>
        <form method="POST" action="modyfikuj.php?wpiszzwolnienie" id="form_1">
            <table border="0" align="center">
                <?php if ($_SESSION['ewnioski_mail_kierownik'] == '' or $_SESSION['ewnioski_mail_kierownik'] == 'brak') { ?>
                    <tr>
                    <td colspan="2" align="center" class="text_red">
                        <h2><br>Nie masz przypisanego przełożonego. <br>Możesz wpisać wniosiek ale nie trafi on do
                            akceptacji w wersji elektronicznej do przełożonego.<br>Skontaktuj sie z następujacymi osobami w
                            celu uzupełnienia tych danych:<br>
                            <?php echo $confW['UzupelnienieDanych'] ?></h2>
                        <br/><br/>
                    </tr><?php
                }
                if ($_SESSION['ewnioski_kierownik'] == '' or $_SESSION['ewnioski_kierownik'] == 'brak') { ?>
                    <tr>
                    <td colspan="2" align="center" class="text_red">
                        <h2><br>Twój przełożony nie założył jeszcze konta do programu eWnioski lub podał zły adres mailowy. <br>Najpierw on musi mieć konto z prawidłowym e-mailem aby wniosek został przypisany do akceptacji przez niego.<br>
                            Adres mailowy twojego przełożonego to: <?php echo $_SESSION['ewnioski_mail_kierownik'];?>.
                        </h2>
                        <br/><br/>
                    </td>
                    </tr><?php
                }
                ?>

                <tr>
                    <td colspan="2" align="center">
                        <h2><b>Prosze wybrać odpowiedni wniosek</b></h2>
                        <br/><br/>
                    </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Imię i nazwisko </b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="imie_nazwisko_stan"><?php echo $imie; ?></span> </b>
                    </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Stan urlopu</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="stan_urlopu"><?php echo $stanurlopu; ?></span> dni</b>
                        <input type="hidden" name="stan_urlopu" id="stan_urlopu_1" value="<?php echo $stanurlopu;?>">
                    </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>W tym urlop zaległy</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="urlop_zalegly"><?php echo $urlopzalegly; ?></span> dni </b>
                    </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Wykorzystany urlop na żądanie</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <b><span id="urlop_uz"><?php echo $uz; ?></span> dni </b>
                    </td>
                </tr>
                <td align="left" class="padding_10_all">
                    <b>Stan godzin</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_godzin"><?php echo $stangodzin; ?></span> godzin </b>
                </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Rodzaj wniosku</b>
                    </td>
                    <td align="left" class="padding_10_all">

                        <?php
                        if((isset($_SESSION['inny_pracownik']) AND $_SESSION['inny_pracownik'] != 0) or $_SESSION['ewnioski_kierownik'] == '10004') $dodatkowy_warunek_zapytania = "";
                        else $dodatkowy_warunek_zapytania = "AND kod!='ZOSP' AND kod!='PZ' ";

                        $q1 = "SELECT * FROM kategoria WHERE typ='0' ".$dodatkowy_warunek_zapytania;
                        $e1 = mysqli_query($polaczenie, $q1);
                        $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_login']);
                        if (mysqli_num_rows($e1) > 0) {
                            while ($wiersz = mysqli_fetch_array($e1)) {
                                ?>
                                <input type="radio" name="rodzaj_urlopu" id="rodzaj_urlopu_<?php echo $wiersz['id']; ?>"
                                       value="<?php echo $wiersz['id']; ?>"
                                       onclick="PokazWniosek(<?php echo $wiersz['id']; ?>)"><?php echo $wiersz['nazwa']; ?>
                                <br>
                            <?php }
                        }
                        ?>

                    </td>
                </tr>
                <tr id="godziny5" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Data początku zwolnienia / pracy zdalnej</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="date" name="data_zwolnienia_lub_pracy_zdalnej" id="data_zwolnienia_lub_pracy_zdalnej"/>
                    </td>
                </tr>
                <tr id="godziny6" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Data końca zwolnienia / pracy zdalnej</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="date" name="liczba_godzin_zwolnienia_lub_pracy_zdalnej" id="liczba_godzin_zwolnienia_lub_pracy_zdalnej" value="8" />
                    </td>
                </tr>
                <tr id="okolicznosciowy" class="hide">
                    <td colspan="2" align="center" class="text_red">
                        <h2><br>Proszę pamiętać o dostarczeniu </br>aktu urodzenia/zgonu/ślubu/zaświadzczenia ze stacji
                            krwiodawstwa </br>W innym przypadku urlop nie bedzie wypłacony.</h2>
                    </td>
                </tr>
                <tr id="godziny4" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Powód zwolnienia</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="text" name="powod" id="powod"/>
                    </td>
                </tr>
                <tr id="godziny1" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Liczba godzin</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="text" name="liczba_godzin" id="liczba_godzin"/>
                    </td>
                </tr>
                <tr id="godziny2" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Data zwolnienia</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <p>od <input type="date" name="data_zwolnienia_od" id="data_zwolnienia_od" required/></p>
                        <p>do <input type="date" name="data_zwolnienia_do" id="data_zwolnienia_do" required/></p>
                    </td>
                </tr>
                <tr id="godziny3" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Dni w któych były odpracowane</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <div class="multi-datepicker" id="picker_odpracowane"></div>
                        <input type="hidden" name="data_odpracowania" id="data_odpracowania"/>
                        <div class="selected-days" id="selected_days_display"></div>
                    </td>
                </tr>
                <tr id="zwolnienie1" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Zmiana czasu pracy w okresie</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <p>od <input type="date" name="zmiana_czasu_od" id="zmiana_czasu_od" required/></p>
                        <p>do <input type="date" name="zmiana_czasu_do" id="zmiana_czasu_do" required/></p>
                    </td>
                </tr>
                <tr id="zwolnienie2" class="hide">
                    <td align="left" class="padding_10_all">
                        <b>Godzina rozpoczęcia</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <select name="godzina_rozpoczecia_h" id="godzina_rozpoczecia_h">
                            <?php for($h = 0; $h <= 23; $h++) { ?>
                                <option value="<?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?></option>
                            <?php } ?>
                        </select>
                        :
                        <select name="godzina_rozpoczecia_m" id="godzina_rozpoczecia_m">
                            <option value="00">00</option>
                            <option value="30">30</option>
                        </select>
                    </td>
                </tr>
                <tr id="zwolnienie3" class="hide">
                    <td align="left" class="padding_10_all" colspan="2">
                        <p style="font-weight: bold; color: red;">uwaga! na podstawie Twojego wniosku nastąpi zmiana harmonogramu</p>
                    </td>
                </tr>

                <?php
                if (strpos(strtolower($_SESSION['ewnioski_mail']), 'brygadzista') !== false or  $_SESSION['inny_pracownik']=='1') { ?>
                    <tr>
                        <td align="left" class="padding_10_all">
                            <b>Numer ewidencyjny pracownika</b>
                        </td>
                        <td align="left" class="padding_10_all">
                            <input type="text" name="numer_ewidencyjny" id="numer_ewidencyjny" autocomplete="off" onkeyup="PobierzDane('numer_ewidencyjny','imie_nazwisko')" onfocusout="PobierzUrlop('numer_ewidencyjny')"/>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" class="padding_10_all">
                            <b>Imię i nazwisko pracownika</b>
                        </td>
                        <td align="left" class="padding_10_all">
                            <input type="hidden" name="imie_nazwisko" id="imie_nazwisko" />
                            <input type="text" name="imie_nazwisko1" id="imie_nazwisko1" disabled/>
                        </td>
                    </tr>
                <?php } else {
                    ?>
                    <input type="hidden" name="numer_ewidencyjny" id="numer_ewidencyjny"
                           value="<?php echo $_SESSION['ewnioski_login']; ?>"/>
                    <input type="hidden" name="imie_nazwisko" id="imie_nazwisko"
                           value="<?php echo $_SESSION['ewnioski_full_name'] ?>"/>
                    <?php
                }
                ?>


                <tr>
                    <td colspan="2" align="center">
                        <input type="button" value="Wpisz wniosek"
                               onclick="WeryfikacjaZwolnienie()"/>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            function MultiDatePicker(containerId, hiddenInputId, displayId) {
                var container = document.getElementById(containerId);
                var hiddenInput = document.getElementById(hiddenInputId);
                var display = document.getElementById(displayId);
                var selected = new Set();
                var current = new Date();
                current.setDate(1);

                var monthNames = ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
                    'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'];
                var dayLabels = ['Pn','Wt','Śr','Cz','Pt','So','Nd'];

                function render() {
                    var year = current.getFullYear();
                    var month = current.getMonth();
                    var firstDay = new Date(year, month, 1).getDay();
                    firstDay = firstDay === 0 ? 6 : firstDay - 1;
                    var daysInMonth = new Date(year, month + 1, 0).getDate();

                    var html = '<div class="dp-header">'
                        + '<button type="button" class="dp-nav" data-dir="-1">&lt;</button>'
                        + '<span>' + monthNames[month] + ' ' + year + '</span>'
                        + '<button type="button" class="dp-nav" data-dir="1">&gt;</button>'
                        + '</div><div class="dp-grid">';

                    for (var i = 0; i < 7; i++) {
                        html += '<div class="dp-daylabel">' + dayLabels[i] + '</div>';
                    }
                    for (var i = 0; i < firstDay; i++) {
                        html += '<div class="dp-empty"></div>';
                    }
                    for (var d = 1; d <= daysInMonth; d++) {
                        var mm = (month + 1) < 10 ? '0' + (month + 1) : (month + 1);
                        var dd = d < 10 ? '0' + d : d;
                        var iso = year + '-' + mm + '-' + dd;
                        html += '<div class="dp-day' + (selected.has(iso) ? ' dp-selected' : '')
                            + '" data-date="' + iso + '">' + d + '</div>';
                    }
                    html += '</div>';
                    container.innerHTML = html;

                    container.querySelectorAll('.dp-nav').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            current.setMonth(current.getMonth() + parseInt(this.getAttribute('data-dir'), 10));
                            render();
                        });
                    });

                    container.querySelectorAll('.dp-day').forEach(function (cell) {
                        cell.addEventListener('click', function () {
                            var iso = this.getAttribute('data-date');
                            if (selected.has(iso)) {
                                selected.delete(iso);
                                this.classList.remove('dp-selected');
                            } else {
                                selected.add(iso);
                                this.classList.add('dp-selected');
                            }
                            var sorted = Array.from(selected).sort();
                            hiddenInput.value = sorted.join(',');
                            display.textContent = sorted.length > 0
                                ? 'Wybrano: ' + sorted.map(function (isoDate) {
                                var p = isoDate.split('-');
                                return p[2] + '.' + p[1] + '.' + p[0];
                            }).join(', ')
                                : '';
                        });
                    });
                }

                render();
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('picker_odpracowane')) {
                    MultiDatePicker('picker_odpracowane', 'data_odpracowania', 'selected_days_display');
                }
            });
        </script>
        <?php
        mysqli_close($polaczenie);
    }

    /**
     * Wpisanie nowego zwolnienia do bazy danych
     */
    function WpiszZwolnienie()
    {
        if(!isset($_POST['data_odpracowania']))$_POST['data_odpracowania']='';
        if(!isset($_POST['godzina_rozpoczecia']))$_POST['godzina_rozpoczecia']='';
        if(!isset($_POST['godzina_rozpoczecia']))$_POST['godzina_rozpoczecia']='';
        if(!isset($_POST['powod']))$_POST['powod']='';
        if(!isset($_POST['zmiana_czasu']))$_POST['zmiana_czasu']='';

        // --- Złożenie godziny rozpoczęcia z dwóch selectów (godzina + 00/30 min) w jedno pole godzina_rozpoczecia ---
        // Pola pochodzą z selectów godzina_rozpoczecia_h / godzina_rozpoczecia_m w FormularzZwolnienie()
        if(!isset($_POST['godzina_rozpoczecia_h']))$_POST['godzina_rozpoczecia_h']='';
        if(!isset($_POST['godzina_rozpoczecia_m']))$_POST['godzina_rozpoczecia_m']='';
        if($_POST['godzina_rozpoczecia_h']!='' and $_POST['godzina_rozpoczecia_m']!='') {
            $_POST['godzina_rozpoczecia'] = $_POST['godzina_rozpoczecia_h'] . ':' . $_POST['godzina_rozpoczecia_m'];
        }
        // --- koniec złożenia godziny rozpoczęcia ---

        if($_POST['powod']!='')$_POST['godzina_rozpoczecia']=$_POST['powod'];

        // --- Złożenie zakresu dat "Data zwolnienia" (godziny2: od/do) w jedno pole data_zwolnienia ---
        // Pola pochodzą z inputów data_zwolnienia_od / data_zwolnienia_do w FormularzZwolnienie()
        if(!isset($_POST['data_zwolnienia_od']))$_POST['data_zwolnienia_od']='';
        if(!isset($_POST['data_zwolnienia_do']))$_POST['data_zwolnienia_do']='';
        if($_POST['data_zwolnienia_od']!='' and $_POST['data_zwolnienia_do']!='') {
            $_POST['data_zwolnienia'] = trim($_POST['data_zwolnienia_od']) . ' do ' . trim($_POST['data_zwolnienia_do']);
        }
        // --- koniec złożenia zakresu dat zwolnienia ---

        // --- Złożenie zakresu dat "Zmiana czasu pracy w okresie" (zwolnienie1: od/do) w jedno pole zmiana_czasu ---
        // Pola pochodzą z inputów zmiana_czasu_od / zmiana_czasu_do w FormularzZwolnienie()
        if(!isset($_POST['zmiana_czasu_od']))$_POST['zmiana_czasu_od']='';
        if(!isset($_POST['zmiana_czasu_do']))$_POST['zmiana_czasu_do']='';
        if($_POST['zmiana_czasu_od']!='' and $_POST['zmiana_czasu_do']!='') {
            $_POST['zmiana_czasu'] = trim($_POST['zmiana_czasu_od']) . ' do ' . trim($_POST['zmiana_czasu_do']);
        }
        // --- koniec złożenia zakresu zmiany czasu pracy ---

        // --- Walidacja pola data_odpracowania (Opcja A: string z listą dat rozdzielonych przecinkami) ---
        // Picker JS wysyła np. "2026-07-10,2026-07-15" - tutaj filtrujemy tylko poprawne daty w formacie YYYY-MM-DD
        // i odrzucamy wszystko inne, zanim trafi do zapytania SQL.
        $dniOdpracowaniaRaw = trim($_POST['data_odpracowania']);
        $listaDat = array_filter(explode(',', $dniOdpracowaniaRaw));
        $dniOdpracowaniaBezpieczne = array();
        foreach ($listaDat as $dzien) {
            $dzien = trim($dzien);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dzien)) {
                $dniOdpracowaniaBezpieczne[] = $dzien;
            }
        }
        $_POST['data_odpracowania'] = implode(',', $dniOdpracowaniaBezpieczne);
        // --- koniec walidacji ---

        $polaczenie = $this->MysqliConnect();
        if(isset($_GET['dotyk'])) $strona='dotyk.php?raport_zwolnien&czas';
        else $strona='raporty.php?zwolnienia';
        $_SESSION['ewnioski_komunikat'] = '<font color="red"><h3>Wystąpił bład podczas wpisywania urlopu. Skontaktuj się z administratorem systemu. </h3></font>';
        $status = '1';
        $poleprzelozony = '';
        $dataprzelozony = '';
        $polepracazdalna='';
        $danepracazdalna='';
        $kierownik=$_SESSION['ewnioski_kierownik'];
        $mail = '1';
        if ($_POST['numer_ewidencyjny'] != $_SESSION['ewnioski_login']) {
            $status = '2';
            $poleprzelozony = ',data_kierownik, login_kierownik';
            $dataprzelozony = ",now(),'" . $_SESSION['ewnioski_full_name'] . "'";
            //$kierownik=$_SESSION['ewnioski_login'];
            $mail = '2';
        }

        if(isset($_POST['liczba_godzin_zwolnienia_lub_pracy_zdalnej']) and ($_POST['rodzaj_urlopu'] == 12 OR $_POST['rodzaj_urlopu'] == 13 or $_POST['rodzaj_urlopu'] == 17 OR $_POST['rodzaj_urlopu'] == 18)) $_POST['liczba_godzin'] = $_POST['liczba_godzin_zwolnienia_lub_pracy_zdalnej'];
        if(isset($_POST['data_zwolnienia_lub_pracy_zdalnej'])and ($_POST['rodzaj_urlopu'] == 12 OR $_POST['rodzaj_urlopu'] == 13 or $_POST['rodzaj_urlopu'] == 17 OR $_POST['rodzaj_urlopu'] == 18)) $_POST['data_zwolnienia'] = $_POST['data_zwolnienia_lub_pracy_zdalnej'].' do '.$_POST['liczba_godzin_zwolnienia_lub_pracy_zdalnej'];
        if(isset($_POST['miejsce_pracy_zdalnej']))$_POST['data_odpracowania']=$_POST['miejsce_pracy_zdalnej'];
        // Jeżeli typ wniosku to: Zwolnienie z obowiązku świadczenia pracy, i dotyczy tej samej osoby która go wpisuję to akceptacja nie jest automatyczna
        if(($_POST['rodzaj_urlopu'] == 12 OR $_POST['rodzaj_urlopu'] == 13 or $_POST['rodzaj_urlopu'] == 17 OR $_POST['rodzaj_urlopu'] == 18) AND $_POST['numer_ewidencyjny'] == $_SESSION['ewnioski_login']) {
            $status = '1';
        }

        // Jeżeli wniosek dotyczy brygadzisty i typ wniosku to: Zwolnienie z obowiązku świadczenia pracy, wniosek nie akceptuję się automatycznies
        $q1 = "SELECT * FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='".$_POST['numer_ewidencyjny']."'";
        $e1 = mysqli_query($polaczenie, $q1) OR DIE(mysqli_error($polaczenie));
        $d1 = mysqli_fetch_array($e1);
        if(($_POST['rodzaj_urlopu'] == 12 OR $_POST['rodzaj_urlopu'] == 13  or $_POST['rodzaj_urlopu'] == 17 OR $_POST['rodzaj_urlopu'] == 18) AND preg_match("/brygadzista/i", $d1['email']) == true) $status = '1';
        $_POST['liczba_godzin']=str_replace(',','.',$_POST['liczba_godzin']);
        if($_POST['rodzaj_urlopu'] == 17 OR $_POST['rodzaj_urlopu'] == 18)
        {
            $polepracazdalna = ',poczatek_pracy_zdalnej, koniec_pracy_zdalnej';
            $danepracazdalna=",'" . trim($_POST['data_zwolnienia_lub_pracy_zdalnej']) . "','" . trim($_POST['liczba_godzin_zwolnienia_lub_pracy_zdalnej']) . "'";
            $_POST['liczba_godzin']='';
        }

        if(in_array($_POST['rodzaj_urlopu'], ['17', '18'])) {

            $DatyWnioskow = [];
            $PoczatekPracyZdalnej = $_POST['data_zwolnienia_lub_pracy_zdalnej'];
            $KoniecPracyZdalnej = $_POST['liczba_godzin_zwolnienia_lub_pracy_zdalnej'];

            $DataPoczatkuPracyZdalnej = new DateTime($PoczatekPracyZdalnej);
            $DataKoncaPracyZdalnej = new DateTime($KoniecPracyZdalnej);

            if($DataPoczatkuPracyZdalnej->format('m') != $DataKoncaPracyZdalnej->format('m')) {
                $DatyWnioskow =
                        [
                                [
                                        'data_poczatkowa' => $DataPoczatkuPracyZdalnej->format('Y-m-d'),
                                        'data_koncowa' => $DataPoczatkuPracyZdalnej->format('Y-m-t')
                                ],
                                [
                                        'data_poczatkowa' => $DataKoncaPracyZdalnej->format('Y-m-01'),
                                        'data_koncowa' => $DataKoncaPracyZdalnej->format('Y-m-d')
                                ]
                        ];
            }
            else {
                $DatyWnioskow =
                        [
                                [
                                        'data_poczatkowa' => $DataPoczatkuPracyZdalnej->format('Y-m-d'),
                                        'data_koncowa' => $DataKoncaPracyZdalnej->format('Y-m-d')
                                ]
                        ];
            }

            foreach ($DatyWnioskow as $Daty) {
                $zapytanie = "INSERT INTO zwolnienie (
                id_kategorii, 
                data_wpisania, 
                osoba_wpisujaca, 
                imie_nazwisko, 
                numer_ewidencyjny, 
                liczba_godzin, 
                data_zwolnienia,  
                data_odpracowania, 
                poczatek_pracy, 
                dane_mpk, 
                id_status, 
                przedzial_czasowy,
                numer_ewidencyjny_wpisujacy,
                profil,
                poczatek_pracy_zdalnej, 
                koniec_pracy_zdalnej
                ) values (
                  '" . trim($_POST['rodzaj_urlopu']) . "',
                  now(),
                  '" . trim($_SESSION['ewnioski_full_name']) . "',
                  '" . trim($_POST['imie_nazwisko']) . "',
                  '" . trim($_POST['numer_ewidencyjny']) . "',
                  '" . trim($_POST['liczba_godzin']) . "',
                   '" . trim($Daty['data_poczatkowa'])." do ".trim($Daty['data_koncowa']) . "',
                   '" . trim($_POST['data_odpracowania']) . "',
                   '" . trim($_POST['godzina_rozpoczecia']) . "',
                   '" . trim($kierownik) . "',
                   '" . $status."',
                   '" . trim($_POST['zmiana_czasu']) . "' ,
                   '" . trim($_SESSION['ewnioski_login']) . "',
                   '" . trim($_SESSION['ewnioski_profil_wpisz_dane']) . "', 
                   '" .trim($Daty['data_poczatkowa'])."',
                   '" .trim($Daty['data_koncowa'])."'
               )";

                $wykonaj = mysqli_query($polaczenie, $zapytanie) OR DIE(mysqli_error($polaczenie));
                if (mysqli_affected_rows($polaczenie) > 0) {
                    $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wniosek został wpisany do systemu</h3></font>';
                    if ($mail == '1') {
                        $adresat = $_SESSION['ewnioski_mail'] . ',' . $_SESSION['ewnioski_mail_kierownik'];
                        // $adresat = "pawel.dabrowski@motherson.com";
                        $osoba = $_POST['imie_nazwisko'];
                        $przedzial = trim($Daty['data_poczatkowa']) . "   " . trim($Daty['data_koncowa']);
                        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                            <body align='center'>
                            <center>
                            <h2>Wpisano nowy wniosek zwolnienia z godzin, zmiany czasu pracy lub pracy zdalnej dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
                            Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/raporty.php?urlop > eWniosków</a>
                             </center>
                             </body></html>
                             ";

                        $this->Wyslij($adresat, 'Wpisano nowy wniosek zwolnienia/ zmiany czasu/ pracy zdalnej', $page);
                    }
                }
            }
        }
        else {
            $zapytanie = "INSERT INTO zwolnienie (id_kategorii, data_wpisania, osoba_wpisujaca, imie_nazwisko, numer_ewidencyjny, liczba_godzin, data_zwolnienia,  data_odpracowania, poczatek_pracy, dane_mpk, id_status, przedzial_czasowy,numer_ewidencyjny_wpisujacy,profil $poleprzelozony $polepracazdalna)
               values ('" . trim($_POST['rodzaj_urlopu']) . "',now(),'" . trim($_SESSION['ewnioski_full_name']) . "','" . trim($_POST['imie_nazwisko']) . "','" . trim($_POST['numer_ewidencyjny']) . "','" . trim($_POST['liczba_godzin']) . "',
               '" . trim($_POST['data_zwolnienia']) . "','" . trim($_POST['data_odpracowania']) . "','" . trim($_POST['godzina_rozpoczecia']) . "','" . trim($kierownik) . "','" . $status . "','" . trim($_POST['zmiana_czasu']) . "' ,'" . trim($_SESSION['ewnioski_login']) . "','" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."' $dataprzelozony $danepracazdalna)";

            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Wniosek został wpisany do systemu</h3></font>';
                if ($mail == '1') {
                    $adresat = $_SESSION['ewnioski_mail'] . ',' . $_SESSION['ewnioski_mail_kierownik'];
                    $osoba = $_POST['imie_nazwisko'];
                    $przedzial = $_POST['data_zwolnienia'] . "   " . $_POST['zmiana_czasu'];
                    $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                    <body align='center'>
                                    <center>
                                    <h2>Wpisano nowy wniosek zwolnienia z godzin, zmiany czasu pracy lub pracy zdalnej dla osoby: $osoba .<br>W przedziale czasowym   $przedzial</h2><br>
                                    Aby przejść do programu naciśnij następujący link do <a href=http://lora.pkc.motherson.com/eWnioski/php/raporty.php?urlop > eWniosków</a>
                                     </center>
                                    </body></html>
                                    ";

                    $this->Wyslij($adresat, 'Wpisano nowy wniosek zwolnienia/ zmiany czasu/ pracy zdalnej', $page);
                }
            }
        }

        mysqli_close($polaczenie);
        ?>
        <script type="text/javascript">
            (window.location = '<?php echo $strona; ?>');
        </script>
        <?php
        exit;
    }

    /**
     * Formularz wpisania wniosku o pracę zdalną
     */
    function FormularzZdalna()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");
        $polaczenie = $this->MysqliConnect();
        $zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly,uz
FROM stan_godzin
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'   order by data_importu desc limit 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
        $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
        $wynik = mysqli_fetch_row($wykonaj);
        $imie=$wynik['2'].' '.$wynik['1'];
        $stanurlopu = number_format($wynik['3'], 0);
        $stangodzin = number_format($wynik['4'], 1);
        $urlopzalegly = number_format($wynik['5'], 0);
        $uz = number_format($wynik['6'], 0);

        ?>
        <form method="POST" action="modyfikuj.php?wpiszzwolnienie" id="form_1">
        <table border="0" align="center">
            <?php if ($_SESSION['ewnioski_mail_kierownik'] == '' or $_SESSION['ewnioski_mail_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Nie masz przypisanego przełożonego. <br>Możesz wpisać wniosiek ale nie trafi on do
                        akceptacji w wersji elektronicznej do przełożonego.<br>Skontaktuj sie z następujacymi osobami w
                        celu uzupełnienia tych danych:<br>
                        <?php echo $confW['UzupelnienieDanych'] ?></h2>
                    <br/><br/>
                </tr><?php
            }
            if ($_SESSION['ewnioski_kierownik'] == '' or $_SESSION['ewnioski_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Twój przełożony nie założył jeszcze konta do programu eWnioski lub podał zły adres mailowy. <br>Najpierw on musi mieć konto z prawidłowym e-mailem aby wniosek został przypisany do akceptacji przez niego.<br>
                        Adres mailowy twojego przełożonego to: <?php echo $_SESSION['ewnioski_mail_kierownik'];?>.
                    </h2>
                    <br/><br/>
                </td>
                </tr><?php
            }
            if ( $_SESSION['praca_zdalna']==0) { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Brak podpisanego regulaminu pracy zdalnej. Skontaktuj się z działem HR aby móc wpisać wniosek o pracę zdalną. </h2>
                    <br/><br/>
                </td>
                </tr><?php
            }
            else{

            ?>

            <tr>
                <td colspan="2" align="center">
                    <h2><b>Prosze wybrać odpowiedni wniosek</b></h2>
                    <br/><br/>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Imię i nazwisko </b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="imie_nazwisko_stan"><?php echo $imie; ?></span> </b>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Stan urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_urlopu"><?php echo $stanurlopu; ?></span> dni</b>
                    <input type="hidden" name="stan_urlopu" id="stan_urlopu_1" value="<?php echo $stanurlopu;?>">
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>W tym urlop zaległy</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_zalegly"><?php echo $urlopzalegly; ?></span> dni </b>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Wykorzystany urlop na żądanie</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_uz"><?php echo $uz; ?></span> dni </b>
                </td>
            </tr>
            <td align="left" class="padding_10_all">
                <b>Stan godzin</b>
            </td>
            <td align="left" class="padding_10_all">
                <b><span id="stan_godzin"><?php echo $stangodzin; ?></span> godzin </b>
            </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Rodzaj wniosku</b>
                </td>
                <td align="left" class="padding_10_all">

                    <?php
                    //var_dump( $_SESSION['ewnioski_kierownik']);
                    if((isset($_SESSION['inny_pracownik']) AND $_SESSION['inny_pracownik'] != 0) or $_SESSION['ewnioski_kierownik'] == '10004') $dodatkowy_warunek_zapytania = "";
                    else $dodatkowy_warunek_zapytania = "AND kod!='ZOSP' AND kod!='PZ' ";

                    $q1 = "SELECT * FROM kategoria WHERE typ='3' ".$dodatkowy_warunek_zapytania;
                    $e1 = mysqli_query($polaczenie, $q1);
                    $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_login']);
                    if (mysqli_num_rows($e1) > 0) {
                        while ($wiersz = mysqli_fetch_array($e1)) {
                            ?>
                            <input type="radio" name="rodzaj_urlopu" id="rodzaj_urlopu_<?php echo $wiersz['id']; ?>"
                                   value="<?php echo $wiersz['id']; ?>"s><?php echo $wiersz['nazwa']; ?>
                            <br>
                        <?php }
                    }
                    ?>

                </td>
            </tr>
            <tr id="godziny5" >
                <td align="left" class="padding_10_all">
                    <b>Data początku pracy zdalnej</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="date" name="data_zwolnienia_lub_pracy_zdalnej" id="data_zwolnienia_lub_pracy_zdalnej"/>
                </td>
            </tr>
            <tr id="godziny6" >
                <td align="left" class="padding_10_all">
                    <b>Data końca pracy zdalnej</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="date" name="liczba_godzin_zwolnienia_lub_pracy_zdalnej" id="liczba_godzin_zwolnienia_lub_pracy_zdalnej" value="8" onchange="getDaysDifference('#liczba_godzin_zwolnienia_lub_pracy_zdalnej', '#data_zwolnienia_lub_pracy_zdalnej');"/>
                </td>
            </tr>
                <tr id="miejsce" >
                    <td align="left" class="padding_10_all">
                        <b>Adres świadczenia pracy zdalnej</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="text" name="miejsce_pracy_zdalnej" id="miejsce_pracy_zdalnej"  size="40"/>
                    </td>
                </tr>

            <?php
            if (strpos(strtolower($_SESSION['ewnioski_mail']), 'brygadzista') !== false or  $_SESSION['inny_pracownik']=='1') { ?>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Numer ewidencyjny pracownika</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="text" name="numer_ewidencyjny" id="numer_ewidencyjny" autocomplete="off" onkeyup="PobierzDane('numer_ewidencyjny','imie_nazwisko')" onfocusout="PobierzUrlop('numer_ewidencyjny')"/>
                    </td>
                </tr>
                <tr>
                    <td align="left" class="padding_10_all">
                        <b>Imię i nazwisko pracownika</b>
                    </td>
                    <td align="left" class="padding_10_all">
                        <input type="hidden" name="imie_nazwisko" id="imie_nazwisko" />
                        <input type="text" name="imie_nazwisko1" id="imie_nazwisko1" disabled/>
                    </td>
                </tr>
            <?php } else {
                ?>
                <input type="hidden" name="numer_ewidencyjny" id="numer_ewidencyjny"
                       value="<?php echo $_SESSION['ewnioski_login']; ?>"/>
                <input type="hidden" name="imie_nazwisko" id="imie_nazwisko"
                       value="<?php echo $_SESSION['ewnioski_full_name'] ?>"/>
                <?php
            }
            ?>

                <tr>
                    <td colspan="2" align="center" class="text_red">
                        <h2><br>Jednocześnie oświadczam, <br>iż pod wyżej wskazanym adresem posiadam warunki lokalowe, <br>techniczne oraz zapewnione bezpieczne i higieniczne<br> warunki do wykonywania pracy zdalnej.</h2>
                        <br/><br/>
                </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="button" value="Wpisz wniosek"
                           onclick="WeryfikacjaPracaZdalna()"/>
                </td>
            </tr>
        </table>
        </form><?php
        mysqli_close($polaczenie);
            }
    }
    function FormularzNowyWniosekDotyk()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");

        $rok = date('Y');
        $polaczenie = $this->MysqliConnect();
        $zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly, uz
FROM stan_godzin
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'   order by data_importu desc limit 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
        $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
        $wynik = mysqli_fetch_row($wykonaj);
        $imie=$wynik['2'].' '.$wynik['1'];
        if($wynik['3']===NULL) $stanurlopu='Pracownik agencyjny';
        else {
            $stanurlopu = number_format($wynik['3'], 0);
        }

        $stangodzin = number_format($wynik['4'], 1);
        $urlopzalegly = number_format($wynik['5'], 0);
        $uz = number_format($wynik['6'], 0);
        ?>
        <form method="POST" action="modyfikuj.php?wpisz&dotyk" id="form_1">
        <table border="0" align="center">
            <?php if ($_SESSION['ewnioski_mail_kierownik'] == '' or $_SESSION['ewnioski_mail_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="8" align="center" class="text_red">
                    <h2><br>Nie masz przypisanego przełożonego. <br>Możesz wpisać wniosiek ale nie trafi on do
                        akceptacji w
                        wersji elektronicznej do przełożonego.<br>Skontaktuj sie z następujacymi osobami w celu
                        uzupełnienia tych
                        danych:<br>
                        <?php echo $confW['UzupelnienieDanych'] ?></h2>
                    <br/><br/>
                </td>
                </tr><?php
            }
            if ($_SESSION['ewnioski_kierownik'] == '' or $_SESSION['ewnioski_kierownik'] == 'brak') { ?>
                <tr>
                    <td colspan="8" align="center" class="text_red">
                        <h2><br>Twój przełożony nie założył jeszcze konta do programu eWnioski lub podał zły adres mailowy. <br>Najpierw on musi mieć konto z prawidłowym e-mailem aby wniosek został przypisany do akceptacji przez niego.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr><?php
            } ?>
            <tr>
                <td colspan="8" align="center"> <?php
                    if (isset($_SESSION['ewnioski_komunikat'])) {
                        $this->WyswietlKomunikat();
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" align="center">
                    <h2><b>Prosze wypełnić wniosek o urlop</b></h2>
                    <br/><br/>
                </td>
            </tr>
            <tr>
            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Imię i nazwisko</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="imie_nazwisko_stan"><?php echo $imie; ?></span> </b>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Stan urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_urlopu"><?php echo $stanurlopu; ?></span> dni</b>
                    <input type="hidden" name="stan_urlopu" id="stan_urlopu_1" value="<?php echo $stanurlopu;?>">
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>W tym urlop zaległy</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_zalegly"><?php echo $urlopzalegly; ?></span> dni </b>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Wykorzystany urlop na żądanie</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_uz"><?php echo $uz; ?></span> dni </b>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                </td>
            <td align="left" class="padding_10_all">
                <b>Stan godzin</b>
            </td>
            <td align="left" class="padding_10_all">
                <b><span id="stan_godzin"><?php echo $stangodzin; ?></span> godzin </b>
            </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all" colspan="2">
                    <b>Rodzaj urlopu</b>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr>

                    <?php
                    $q1 = "SELECT * FROM kategoria where typ='1' and id!='3'";
                    $e1 = mysqli_query($polaczenie, $q1);
                    $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_login']);
                    if (mysqli_num_rows($e1) > 0) {
                        while ($wiersz = mysqli_fetch_array($e1)) {
                            ?>
            <td align="left" class="padding_10_all">
                <label>
                            <input type="radio" name="rodzaj_urlopu" class="radio_dotyk" id="rodzaj_urlopu_<?php echo $wiersz['id']; ?>"
                                   value="<?php echo $wiersz['id']; ?>"
                                   onclick="Pokaz(<?php echo $wiersz['id']; ?>)"> <img src="../img/<?php echo $wiersz['plik']; ?>"></td>
                            </label>
                        <?php }
                    }
                    ?>


            </tr>
            <tr id="okolicznosciowy" class="hide">
                <td colspan="8" align="center" class="text_red">
                    <h2><br>Proszę pamiętać o dostarczeniu aktu urodzenia/zgonu/ślubu/zaświadzczenia ze stacji
                        krwiodawstwa</br>W innym przypadku urlop nie bedzie wypłacony.</h2>
                </td>
            </tr>
            <tr id="stawienicto" class="hide">
                <td colspan="8" align="center" class="text_red">
                    <h2><br>Proszę pamiętać o dostarczeniu potwierdzenia stawiennictwa w sądzie </br>W innym przypadku będzie to nieobecność nieusprawiedliwiona.</h2>
                </td>
            </tr>
            <tr id="dziecko" class="hide">
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Imię dziecka</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="imie_dziecka" autocomplet="off" id="imie_dziecka" onclick="$('#virtualKeyboard').fadeToggle();" />
                </td>
                <td colspan="3" align="center">
                </td>
                <div id="virtualKeyboard" style="display: none; position: absolute;">
                </div>
                <script src="../js/vendor/bootstrap.min.js"></script>
                <script type="text/javascript" src="../js/jsKeyboard.js"></script>
                <script src="../js/main.js"></script>
            </tr>
            <tr id="dziecko2" class="hide">
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Data urodzenia dziecka</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="date" name="data_urodzenia"  autocomplete="off" id="data_urodzenia" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr id="dziecko3" class="hide">
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Zaznacz jeśli cały dzień</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="checkbox" name="caly_dzien" id="caly_dzien" checked="checked" onchange="PokazGodziny()"/>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
                <input type="hidden" name="numer_ewidencyjny" id="numer_ewidencyjny"
                       value="<?php echo $_SESSION['ewnioski_login']; ?>"/>
                <input type="hidden" name="imie_nazwisko" id="imie_nazwisko"
                       value="<?php echo $_SESSION['ewnioski_full_name'] ?>"/>

            <tr>
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Początek urlopu</b>
                </td>
                <td align="left" class="padding_10_all" >
                    <input  name="poczatek_urlopu"  autocomplete="off" id="poczatek_urlopu" onclick='scwShow(this, event);' />
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr id="koniec_urlopu">
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Koniec urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input name="koniec_urlopu"  autocomplete="off" id="koniec_urlopu"  onclick='scwShow(this, event);'/>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>
            <tr id="liczba_godzin" class="hide">
                <td colspan="3" align="center">
                </td>
                <td align="left" class="padding_10_all">
                    <b>Liczba godzin </b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="liczba_godzin" id="liczba_godzin" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
                <td colspan="3" align="center">
                </td>
            </tr>


            <tr>
                <td colspan="8" align="center">
                    <img src="../img/wpiszwniosek.png"
                           onclick="WeryfikacjaDotyk()"/>
                </td>
            </tr>
        </table>
        </form>
        <?php
        mysqli_close($polaczenie);
    }
    /**
     * Formularz wpisania wniosku o zwolnienie
     */
    function FormularzZwolnienieDotyk()
    {
        $confW = $this->ParseIniE("../conf/confe.ini");
        $polaczenie = $this->MysqliConnect();
        $zapytanieurlop = "SELECT numer_ewidencyjny, nazwisko, imie, stan_urlopu, stan_godzin, urlop_zalegly,uz
FROM stan_godzin
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'   order by data_importu desc limit 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanieurlop);
        $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
        $wynik = mysqli_fetch_row($wykonaj);
        $imie=$wynik['2'].' '.$wynik['1'];
        $stanurlopu = number_format($wynik['3'], 0);
        $stangodzin = number_format($wynik['4'], 1);
        $urlopzalegly = number_format($wynik['5'], 0);
        $uz = number_format($wynik['6'], 0);

        ?>
        <form method="POST" action="modyfikuj.php?wpiszzwolnienie&dotyk" id="form_1">
        <table border="0" align="center">
            <?php if ($_SESSION['ewnioski_mail_kierownik'] == '' or $_SESSION['ewnioski_mail_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Nie masz przypisanego przełożonego. <br>Możesz wpisać wniosiek ale nie trafi on do
                        akceptacji w wersji elektronicznej do przełożonego.<br>Skontaktuj sie z następujacymi osobami w
                        celu uzupełnienia tych danych:<br>
                        <?php echo $confW['UzupelnienieDanych'] ?></h2>
                    <br/><br/>
                </tr><?php
            }
            if ($_SESSION['ewnioski_kierownik'] == '' or $_SESSION['ewnioski_kierownik'] == 'brak') { ?>
                <tr>
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Twój przełożony nie założył jeszcze konta do programu eWnioski lub podał zły adres mailowy. <br>Najpierw on musi mieć konto z prawidłowym e-mailem aby wniosek został przypisany do akceptacji przez niego.<br>
                        Adres mailowy twojego przełożonego to: <?php echo $_SESSION['ewnioski_mail_kierownik'];?>.
                    </h2>
                    <br/><br/>
                </td>
                </tr><?php
            }
            ?>
            <tr>
                <td colspan="2" align="center">
                    <h2><b>Prosze wybrać odpowiedni wniosek</b></h2>
                    <br/><br/>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Imię i nazwisko </b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="imie_nazwisko_stan"><?php echo $imie; ?></span> </b>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Stan urlopu</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="stan_urlopu"><?php echo $stanurlopu; ?></span> dni</b>
                    <input type="hidden" name="stan_urlopu" id="stan_urlopu_1" value="<?php echo $stanurlopu;?>">
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>W tym urlop zaległy</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_zalegly"><?php echo $urlopzalegly; ?></span> dni </b>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Wykorzystany urlop na żądanie</b>
                </td>
                <td align="left" class="padding_10_all">
                    <b><span id="urlop_uz"><?php echo $uz; ?></span> dni </b>
                </td>
            </tr>
            <td align="left" class="padding_10_all">
                <b>Stan godzin</b>
            </td>
            <td align="left" class="padding_10_all">
                <b><span id="stan_godzin"><?php echo $stangodzin; ?></span> godzin </b>
            </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Rodzaj wniosku</b>
                </td>
                <td align="left" class="padding_10_all">

                    <?php

                    $q1 = "SELECT * FROM kategoria WHERE typ='0' AND kod!='ZOSP' AND kod!='PZ'";
                    $e1 = mysqli_query($polaczenie, $q1);
                    $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_login']);
                    if (mysqli_num_rows($e1) > 0) {
                        while ($wiersz = mysqli_fetch_array($e1)) {
                            ?>
                    <label>
                        <input type="radio" name="rodzaj_urlopu" class="radio_dotyk" id="rodzaj_urlopu_<?php echo $wiersz['id']; ?>"
                               value="<?php echo $wiersz['id']; ?>"
                               onclick="PokazWniosek(<?php echo $wiersz['id']; ?>)"> <img src="../img/<?php echo $wiersz['plik']; ?>">
                </label>
                        <?php }
                    }
                    ?>

                </td>
            </tr>
            <tr id="okolicznosciowy" class="hide">
                <td colspan="2" align="center" class="text_red">
                    <h2><br>Proszę pamiętać o dostarczeniu </br>aktu urodzenia/zgonu/ślubu/zaświadzczenia ze stacji
                        krwiodawstwa </br>W innym przypadku urlop nie bedzie wypłacony.</h2>
                </td>
            </tr>
            <tr id="godziny1" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Liczba godzin</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="liczba_godzin" id="liczba_godzin" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
            </tr>
            <tr id="godziny2" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Data zwolnienia</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input  onclick="scwShow(this, event); $('#virtualKeyboard').fadeToggle(); " name="data_zwolnienia" id="data_zwolnienia"/>
                </td>
            </tr>
            <tr id="godziny3" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Dni w których były odpracowane</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="data_odpracowania" id="data_odpracowania" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
            </tr>
            <tr id="zwolnienie1" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Zmiana czasu pracy w okresie</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="zmiana_czasu" id="zmiana_czasu" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
            </tr>
            <tr id="zwolnienie2" class="hide">
                <td align="left" class="padding_10_all">
                    <b>Godzina rozpoczęcia</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input  name="godzina_rozpoczecia" id="godzina_rozpoczecia" onclick="$('#virtualKeyboard').fadeToggle();"/>
                </td>
            </tr>
            <div id="virtualKeyboard" style="display: none; position: absolute;">
            </div>
            <script src="../js/vendor/bootstrap.min.js"></script>
            <script type="text/javascript" src="../js/jsKeyboard.js"></script>
            <script src="../js/main.js"></script>
                <input type="hidden" name="numer_ewidencyjny" id="numer_ewidencyjny"
                       value="<?php echo $_SESSION['ewnioski_login']; ?>"/>
                <input type="hidden" name="imie_nazwisko" id="imie_nazwisko"
                       value="<?php echo $_SESSION['ewnioski_full_name'] ?>"/>

            <tr>
                <td colspan="2" align="center">
                    <img src="../img/wpiszwniosek.png"
                           onclick="WeryfikacjaZwolnienie()"/>
                </td>
            </tr>
        </table>
        </form><?php
        mysqli_close($polaczenie);
    }
    function FormularzRozpocznijZdalna()
    {
        function ObliczCzasZdalnejDzisiaj($polaczenie, $numer_ewidencyjny)
        {
            $dzis = date('Y-m-d');
            $query = "SELECT typ_akcji, data_rozpoczecia_pracy 
              FROM rozpoczecie_pracy_zdalnej 
              WHERE numer_ewidencyjny = '$numer_ewidencyjny' 
              AND data_rozpoczecia_pracy LIKE '$dzis%' 
              ORDER BY id ASC";

            $result = mysqli_query($polaczenie, $query);
            $czas_sumaryczny = 0;
            $start = null;

            while ($row = mysqli_fetch_assoc($result)) {
                $typ = $row['typ_akcji'];
                $czas = new DateTime($row['data_rozpoczecia_pracy']);

                if ($typ == 1 || $typ == 4) {
                    $start = $czas;
                } elseif (($typ == 3 || $typ == 2) && $start !== null) {
                    $czas_sumaryczny += $czas->getTimestamp() - $start->getTimestamp();
                    $start = null;
                }
            }

            // Jeśli nadal trwa sesja - dodaj czas do teraz
            if ($start !== null) {
                $czas_sumaryczny += time() - $start->getTimestamp();
            }

            $godzin = floor($czas_sumaryczny / 3600);
            $minut = floor(($czas_sumaryczny % 3600) / 60);

            return [$godzin, $minut];
        }
        $polaczenie = $this->MysqliConnect();
        $dzis=date('Y-m-d');
        $zapytaniezdalna = "SELECT *
FROM zwolnienie
where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "'  and id_kategorii IN(17,18)  and poczatek_pracy_zdalnej<='$dzis' and koniec_pracy_zdalnej>='$dzis'";
        $wykonaj = mysqli_query($polaczenie, $zapytaniezdalna);
        $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
        $wynik = mysqli_fetch_row($wykonaj);
        if($wynik===NULL)
        {
            ?>
            <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center" class="text_red">
                        <h2><br>Brak wpisanej pracy zdalnej dla dzisiejszej daty. <br>Najpierw wpisz wniosek o pracę zdalną dopiero będziesz mógł ją rozpocząć/zakończyć.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
            </table><?php
        }
        else {
            $zapytaniezdalna = "SELECT typ_akcji,data_rozpoczecia_pracy
            FROM rozpoczecie_pracy_zdalnej
            where numer_ewidencyjny='" . $_SESSION['ewnioski_login'] . "' and  data_rozpoczecia_pracy like '$dzis%' order by id desc";
            $wykonaj = mysqli_query($polaczenie, $zapytaniezdalna);
            $this->BladZapytania($polaczenie, $wykonaj, $_SESSION['ewnioski_login']);
            $wynik = mysqli_fetch_row($wykonaj);
            if($wynik===NULL)
            {
                ?>
                <table border="0" align="center">
                    <tr>
                        <td colspan="2" align="center" class="font_green">
                            <h2><br>Rozpoczynasz właśnię pracę zdalna. <br>Naciśnij poniższy przycisk aby rozpocząć.<br>
                            </h2>
                            <br/><br/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_1">
                                <input type="button" value="Rozpocznij pracę zdalną"
                                       onclick="document.forms['form_1'].submit();"/><br><br><br>
                                <input type="hidden" name="rozpocznij_prace_zdalna" value="1"/>
                            </form>
                        </td>
                    </tr>
                </table><?php
            }
            elseif($wynik[0]==1)
            {
                list($godzin, $minut) = ObliczCzasZdalnejDzisiaj($polaczenie, $_SESSION['ewnioski_login']);
                ?>
                <table border="0" align="center">
                    <tr>
                        <td colspan="2" align="center" class="font_green">
                            <h2><br>Pracujesz już <?php echo $godzin ?> godzin i  <?php echo $minut ?> minut. <br>Możesz zakończyć albo wstrzymać pracę.<br>
                            </h2>
                            <br/><br/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_1">
                                <input type="button" value="Wstrzymaj pracę zdalną"
                                       onclick="document.forms['form_1'].submit();"/><br><br><br>
                                <input type="hidden" name="rozpocznij_prace_zdalna" value="3"/>
                            </form>
                        </td>
                    </tr>
                <tr>
                    <td colspan="2" align="center">
                        <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_2">
                            <input type="button" value="Zakończ pracę zdalną"
                                   onclick="document.forms['form_2'].submit();"/><br><br><br>
                            <input type="hidden" name="rozpocznij_prace_zdalna" value="2"/>
                        </form>
                    </td>
                </tr>
                </table><?php
            }
            elseif($wynik[0]==3)
            {
                list($godzin, $minut) = ObliczCzasZdalnejDzisiaj($polaczenie, $_SESSION['ewnioski_login']);
                ?>
                <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center" class="font_green">
                        <h2><br>Pracujesz już <?php echo $godzin ?> godzin i  <?php echo $minut ?> minut. <br>Możesz zakończyć albo wznowić pracę.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_1">
                            <input type="button" value="Wznów pracę zdalną"
                                   onclick="document.forms['form_1'].submit();"/><br><br><br>
                            <input type="hidden" name="rozpocznij_prace_zdalna" value="4"/>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_2">
                            <input type="button" value="Zakończ pracę zdalną"
                                   onclick="document.forms['form_2'].submit();"/><br><br><br>
                            <input type="hidden" name="rozpocznij_prace_zdalna" value="2"/>
                        </form>
                    </td>
                </tr>
                </table><?php
            }
            elseif($wynik[0]==4)
            {
                list($godzin, $minut) = ObliczCzasZdalnejDzisiaj($polaczenie, $_SESSION['ewnioski_login']);
                ?>
                <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center" class="font_green">
                        <h2><br>Pracujesz już <?php echo $godzin ?> godzin i  <?php echo $minut ?> minut. <br>Możesz zakończyć albo wstrzymać pracę.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_1">
                            <input type="button" value="Wstrzymaj pracę zdalną"
                                   onclick="document.forms['form_1'].submit();"/><br><br><br>
                            <input type="hidden" name="rozpocznij_prace_zdalna" value="3"/>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <form method="POST" action="modyfikuj.php?rozpocznij_zdalna" id="form_2">
                            <input type="button" value="Zakończ pracę zdalną"
                                   onclick="document.forms['form_2'].submit();"/><br><br><br>
                            <input type="hidden" name="rozpocznij_prace_zdalna" value="2"/>
                        </form>
                    </td>
                </tr>
                </table><?php
            }
            elseif($wynik[0]==2)
            {
                list($godzin, $minut) = ObliczCzasZdalnejDzisiaj($polaczenie, $_SESSION['ewnioski_login']);
                ?>
                <table border="0" align="center">
                <tr>
                    <td colspan="2" align="center" class="font_green">
                        <h2><br>Pracowałeś dziś  <?php echo $godzin ?> godzin i  <?php echo $minut ?> minut. <br>Twój czas pracy został odnotowany i przekazany do działu HR.<br>
                        </h2>
                        <br/><br/>
                    </td>
                </tr>
                </table><?php
            }
        }

    }
    function ZmianaPracaZdalna($wartosc)
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "INSERT INTO rozpoczecie_pracy_zdalnej (numer_ewidencyjny, data_rozpoczecia_pracy, typ_akcji, uzytkownik_wpisujacy, data_wpisu,profil)
           values ('".trim($_SESSION['ewnioski_login'])."',now(),'".$wartosc."' ,'".trim( $_SESSION['ewnioski_login'])."',now(),'" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."') ";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if (mysqli_affected_rows($polaczenie) > 0) {
            $_SESSION['ewnioski_komunikat'] = '<font color="#00aa00"><h3>Rozpoczęcie/zakończenie/wstrzymanei pracy zostało odnotowane</h3></font>';
        mysqli_close($polaczenie);
        ?>
        <script type="text/javascript">
            (window.location = 'modyfikuj.php?rozpocznij_zdalna');
        </script>
        <?php
        exit;
        }
    }

}
