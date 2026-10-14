<?php
class Nadgodziny extends Common
{
    public $DodatkowyMail = "pkc.phpalerts@motherson.com";
    protected $RaportNadgodzinyLimit = 600;
    public $RaportNadgodzinyFiltry = array(
            'ewnioski_produktywnosc_filter_nadgodziny_id',
            'ewnioski_produktywnosc_filter_nadgodziny_pracownik',
            'ewnioski_produktywnosc_filter_nadgodziny_numer_ewidencyjny',
            'ewnioski_produktywnosc_filter_nadgodziny_grupa',
            'ewnioski_produktywnosc_filter_nadgodziny_ilosc_godzin_nadliczbowych',
            'ewnioski_produktywnosc_filter_nadgodziny_data_wykonywania_nadgodzin',
            'ewnioski_produktywnosc_filter_nadgodziny_typ_wpisu',
            'ewnioski_produktywnosc_filter_nadgodziny_wprowadzil',
            'ewnioski_produktywnosc_filter_nadgodziny_odpracowanie',
            'ewnioski_produktywnosc_filter_nadgodziny_akceptacja_kierownik',
            'ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny',
            'ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie',
            'ewnioski_produktywnosc_filter_nadgodziny_brygadzista_notatka',
            'ewnioski_produktywnosc_filter_nadgodziny_kierownik_notatka',
            'ewnioski_produktywnosc_filter_nadgodziny_hr_notatka',
            'ewnioski_produktywnosc_filter_nadgodziny_data_akceptacji_dzial_personalny',
            'ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny_user',
    );
    public $RaportNadgodzinyKolumny = array(
            'go.id',
            'go.pracownik',
            'go.numer_ewidencyjny',
            'go.grupa',
            'go.ilosc_godzin_nadliczbowych',
            'go.data_wykonywania_nadgodzin',
            'go.typ_wpisu',
            'go.wprowadzil',
            'go.data_odpracowania',
            'go.akceptacja_kierownik',
            'go.akceptacja_dzial_personalny',
            'go.uzasadnienie',
            'go.brygadzista_notatka',
            'go.kierownik_notatka',
            'go.hr_notatka',
            'DATE_FORMAT(go.data_akceptacji_dzial_personalny,\'%Y-%m-%d\')',
            'go.akceptacja_dzial_personalny_user'
    );
    function Paginator($ilosc) {
        ?>
        <b style="font-size: 12pt;">Liczba stron:
            <?php
            for($i = 0; $i < $ilosc; $i++) { ?>
                <a href="<?php echo "?offset=".($i * $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek']); ?>" <?php if(($i * $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek']) == $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset']) echo "style='color: #aa0000;'" ?> title="Kliknij żeby przejść na inną stronę" ><?php echo $i + 1; ?> &nbsp; </a>
            <?php }
            ?>
        </b>
        <?php
    }
    function IloscDanychNaStronieLista() {
        if(!isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'] = $this->RaportNadgodzinyLimit;
        if(isset($_POST['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'])) {
            $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'] = $_POST['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'];
            $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset'] = 0;
        }
        ?>
        <b>Ogranicz dane do:
            <form method="POST" class="inline">
                <select onchange="$(this).parent().parent().submit();" name="ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek">
                    <?php
                    for($i = 200; $i <= 10000; $i += 200) { ?>
                        <option value="<?php echo $i; ?>" <?php if($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'] == $i) echo "SELECTED"; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php }
                    ?>
                </select> linijek</b>
        </form>
    <?php }
    function WyslijMaila($adresat, $html, $temat, $from) {
        $sciezka_serwera = "http://lora.pkc.motherson.com/Oddelegowania/img/";
        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
<body align='center' background='".$sciezka_serwera."bg_1.jpg'>
<center>
<h3>Ewidencja nadgodzin</h3>
<h4>
    $html
</h4> 
</center>
</body></html>";
        $tresc =<<<KONIEC
$page
KONIEC;
        $naglowki =<<<KONIEC
From: $from
Content-Type: text/html
KONIEC;

        mail( $adresat, $temat, $tresc, $naglowki );
    }
    function PobierzMaileKierownikow($grupy) {
        $polaczenie = $this->MysqliConnect();
        if($_SESSION['ewnioski_oddelegowania_mpk']===null)
        {
            return $_SESSION['ewnioski_mail_kierownik'];
        }
        else {

            $brygadzista_grupy = explode(",", $grupy);
            $dodatkowy_warunek_zapytania = "";
            foreach ($brygadzista_grupy as $brygadzista_grupa) {
                if (strlen($dodatkowy_warunek_zapytania) > 0) $dodatkowy_warunek_zapytania .= " OR mpk like '%" . $brygadzista_grupa . "%'";
                else $dodatkowy_warunek_zapytania .= " mpk like '%" . $brygadzista_grupa . "%'";
            }
            $zapytanie = "SELECT DISTINCT mail as mail FROM oddelegowania.users WHERE permisions='kierownik' AND length(mail) > '0' AND (" . $dodatkowy_warunek_zapytania . ")";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            if (mysqli_num_rows($wykonaj) > 0) {
                $mail = "";
                while ($dane = mysqli_fetch_assoc($wykonaj)) {
                    $mail .= $dane['mail'] . ",";
                }
                return substr($mail, 0, -1);
            } else {
                return $_SESSION['ewnioski_mail_kierownik'];
            }
        }
    }
    function WyslijMailaEwindencjaNadgodzinAnulowaniePozycji($notatka, $mail, $dane_pozycji)
    {
        $sciezka_serwera = "http://lora.pkc.motherson.com/Oddelegowania/img/";
        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
<body align='center' background='".$sciezka_serwera."bg_1.jpg'>
<center>
<img src='".$sciezka_serwera."Warning.png'/>
<h3>Ewidencja nadgodzin</h3>
<h4>Pracownik Działu Personalnego musiał anulować wpisaną pozycję ze względu na niezgodność<br/>
Dane pozycji: ".$dane_pozycji."
Anulowanie pozycji przez: ".$_SESSION['ewnioski_full_name']." ".date('Y-m-d H:i:s')."<br/>
Z powodu: ".$notatka."
</h4>
</center>
</body></html>";
        $temat =  'Ewidencja nadgodzin: anulowanie pozycji';
        $tresc =<<<KONIEC
$page
KONIEC;
        $naglowki =<<<KONIEC
From: EwidencjaNadgodzinAdmin@motherson.com
Content-Type: text/html
KONIEC;
        $adresat = $mail;
        $adresat .= ",".$this->DodatkowyMail;

        mail( $adresat, $temat, $tresc, $naglowki );
    }
    function WyslijMailaEwindencjaNadgodzinEdycjaPozycjiHr($dane_pozycji, $mail)
    {
        $sciezka_serwera = "http://lora.pkc.motherson.com/Oddelegowania/img/";
        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
<body align='center' background='".$sciezka_serwera."bg_1.jpg'>
<center>
<img src='".$sciezka_serwera."Edit_pencil.png'/>
<h3>Ewidencja nadgodzin</h3>
<h4>Pracownik Działu Personalnego wprowadził korektę pozycji<br/> Nowe dane: ".$dane_pozycji."<br/>
Edycja pozycji przez: ".$_SESSION['ewnioski_full_name']." ".date('Y-m-d H:i:s')."<br/>
</h4>
</center>
</body></html>";
        $temat =  'Ewidencja nadgodzin: korekta pozycji';
        $tresc =<<<KONIEC
$page
KONIEC;
        $naglowki =<<<KONIEC
From: EwidencjaNadgodzinAdmin@motherson.com
Content-Type: text/html
KONIEC;
        $adresat = $mail.",".$this->DodatkowyMail;
        mail( $adresat, $temat, $tresc, $naglowki );
    }
    function ZapiszGlobalnaModyfikacjeKierownik($ids, $stan) {
        $polaczenie = $this->MysqliConnect();
        $ids = explode(",",$ids);
        $zapisano = 0;
        foreach($ids as $id) {
            $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_kierownik='".$stan."', akceptacja_kierownik_user ='".$_SESSION['ewnioski_full_name']."', data_akceptacji_kierownik=now()  WHERE id='".$id."'";
            mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            $zapisano += mysqli_affected_rows($polaczenie);
        }
        if($zapisano == count($ids)) { ?>
            <div align="center" style="color: #00aa00"><b>Dane zostały zapisane</b></div>
        <?php }
        else { ?>
            <div style="color: #aa0000"><b>Dane nie zostały zapisane<br/><?php echo mysqli_error($polaczenie); ?></b></div>
        <?php }
    }
    function ZapiszGlobalnaModyfikacjeHR($ids, $stan) {
        $polaczenie = $this->MysqliConnect();
        $ids = explode(",",$ids);
        $zapisano = 0;
        foreach($ids as $id) {
            $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_dzial_personalny='".$stan."', akceptacja_dzial_personalny_user='".$_SESSION['ewnioski_full_name']."', data_akceptacji_dzial_personalny=now()  WHERE id='".$id."'";
            mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
            $zapisano += mysqli_affected_rows($polaczenie);
        }
        if($zapisano == count($ids)) { ?>
            <div align="center" style="color: #00aa00"><b>Dane zostały zapisane</b></div>
        <?php }
        else { ?>
            <div style="color: #aa0000"><b>Dane nie zostały zapisane<br/><?php echo mysqli_error($polaczenie); ?></b></div>
        <?php }
    }
    function FilterDatowy() {
        if(!isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa'] = date('Y-m-d',strtotime("now - 1 week"));
        if(!isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa'] = date('Y-m-d');
        if(isset($_POST['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa'] = trim($_POST['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa']);
        if(isset($_POST['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa'] = trim($_POST['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa']);
        ?>
        <form method="POST">
            <b>Data początkowa: </b><input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa" onclick="scwShow(this,event);" value="<?php echo $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa']; ?>" class="border" />
            <b>Data końcowa: </b><input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_data_koncowa" onclick="scwShow(this,event);" value="<?php echo $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa']; ?>" class="border" />
            <input type="submit" value="Filtrowanie">
        </form>
    <?php }
    function ZapiszNotatke($id, $kolumna, $wartosc) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET ".$kolumna."='".$wartosc."' WHERE id='".$id."'";
        mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_affected_rows($polaczenie) > 0) { ?>
            <div align="center" style="color: #00aa00"><b>Dane zostały zapisane</b></div>
            <script type="text/javascript">
                setTimeout(function() {
                    window.opener.location = window.opener.location;
                    window.close();
                }, 2000)
            </script>
        <?php }
        else { ?>
            <div style="color: #aa0000"><b>Dane nie zostały zapisane<br/><?php echo mysqli_error($polaczenie); ?></b></div>
        <?php }
    }
    function FormularzEdycjiNotatki($id, $kolumna) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT * FROM godziny_nadliczbowe_godziny WHERE id='".$id."'";
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        if(mysqli_num_rows($wykonaj) > 0) {
            $dane = mysqli_fetch_array($wykonaj);
        }
        else die;
        ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="kolumna" value="<?php echo $kolumna; ?>">
            <table align="center" cellpadding="15">
                <tr>
                    <td colspan="2" align="center">
                        <h3>Edycja notatki</h3>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <b>Dane nadgodzin:</b>
                    </td>
                    <td align="left">
                        <b><?php echo $dane['pracownik']." ".$dane['numer_ewidencyjny']." ".$dane['grupa']." ".$dane['data_wykonywania_nadgodzin']." ".$dane['ilosc_godzin_nadliczbowych']; ?></b>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <b>Twoja notatka:</b>
                    </td>
                    <td align="left">
                        <textarea name="notatka" cols="40" style="border: 1px #000000 solid"><?php echo $dane[$kolumna]; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Popraw dane">
                    </td>
                </tr>
            </table>
        </form>
    <?php }
    function WyciaganieMaila($imie_nazwisko) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT mail FROM oddelegowania.users WHERE full_name='".$imie_nazwisko."'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie) ;
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        $mail = null;
        if(mysqli_num_rows($wykonaj) > 0) {
            $dane = mysqli_fetch_object($wykonaj);
            $mail = $dane->mail;
        }
        return $mail;
    }
    function AnulowaniePozycji($id, $full_name, $notatka) {
        $polaczenie = $this->MysqliConnect();
        $q1 = "SELECT * FROM godziny_nadliczbowe_godziny WHERE id='".$id."'";
        $e1 = mysqli_query($polaczenie, $q1);
        $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
        if(mysqli_num_rows($e1) > 0) {
            $d1 = mysqli_fetch_object($e1);
            if($d1->anulowano == 'tak') $wartosc = 'nie';
            else $wartosc = 'tak';
            $q2 = "UPDATE godziny_nadliczbowe_godziny SET anulowano='".$wartosc."' WHERE id='".$id."'";
            $e2 = mysqli_query($polaczenie, $q2);
            $this->BladZapytania($polaczenie, $q2, $_SESSION['ewnioski_full_name']);
            $this->ZapiszLog('Zapytanie;' . $q2, $_SESSION['ewnioski_full_name']);
            if(mysqli_affected_rows($polaczenie) > 0) {
                $mail = $this->WyciaganieMaila($full_name);
                if($mail != null AND strlen($mail) > 0 AND $d1->anulowano == 'nie') {
                    $dane_pozycji = "Pracownik: ".$d1->pracownik.", Numer Ewidencyjny: ".$d1->numer_ewidencyjny." Data nadgodzin: ".$d1->data_wykonywania_nadgodzin.", Ilość nadgodzin: ".$d1->ilosc_godzin_nadliczbowych;
                    $this->WyslijMailaEwindencjaNadgodzinAnulowaniePozycji($notatka, $mail, $dane_pozycji);
                    ?>
                    <b style="color: #00aa00">Mail został wysłany do: <?php echo $full_name; ?></b><br/>
                    <?php
                }
                else { ?>
                    <b style="color: #aa0000">Mail nie został wysłany do: <?php echo $full_name; ?> najprawdopodobniej nie jest on uzupełniony</b><br/>
                <?php } ?>
                <b style="color: #00aa00">Dane zostały anulowane</b>
            <?php }
            else { ?>
                <b style="color: #aa0000">Dane nie zostały anulowane <br/><?php echo mysqli_error($polaczenie); ?></b>
            <?php }
        }
    }
    function ZapiszZmienioneDane($id, $ilosc_godzin, $data_wykonywania_nadgodzin) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET ilosc_godzin_nadliczbowych='".$ilosc_godzin."', data_wykonywania_nadgodzin='".$data_wykonywania_nadgodzin."' WHERE id='".$id."'";
        mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_affected_rows($polaczenie) > 0) {
            if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr') {
                // Wyciąganie maila właściciela
                $q1 = "SELECT * FROM godziny_nadliczbowe_godziny gn JOIN users us ON(gn.wprowadzil=us.full_name) WHERE gn.id='".$id."' LIMIT 1";
                $e1 = mysqli_query($polaczenie, $q1);
                $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
                if(mysqli_num_rows($e1) > 0) {
                    $d1 = mysqli_fetch_array($e1);
                    if(strlen(trim($d1['mail'])) > 0) {
                        $dane_pozycji = "Pracownik: ".$d1['pracownik'].", Numer Ewidencyjny: ".$d1['numer_ewidencyjny']." Data nadgodzin: ".$d1['data_wykonywania_nadgodzin'].", Ilość nadgodzin: ".$d1['ilosc_godzin_nadliczbowych'];
                        $this->WyslijMailaEwindencjaNadgodzinEdycjaPozycjiHr($dane_pozycji, $d1['mail']);
                    }
                }
            }
            ?>
            <div align="center" style="color: #00aa00"><b>Dane zostały zapisane</b></div>
            <script type="text/javascript">
                setTimeout(function() {
                    window.opener.location = window.opener.location;
                    window.close();
                }, 2000)
            </script>
        <?php }
        else { ?>
            <div style="color: #aa0000"><b>Dane nie zostały zapisane<br/><?php echo mysqli_error($polaczenie); ?></b></div>
        <?php }
    }
    function FormularzPoprawyDanych($id) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT * FROM godziny_nadliczbowe_godziny WHERE id='".base64_decode($id)."'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_num_rows($wykonaj) > 0) {
            $dane = mysqli_fetch_object($wykonaj);
        }
        else die;
        ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo base64_decode($id); ?>">
            <table align="center" cellpadding="15">
                <tr>
                    <td colspan="2" align="center">
                        <h3>Edycja danych</h3>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <b>Dane pracownika:</b>
                    </td>
                    <td align="left">
                        <b><?php echo $dane->pracownik." ".$dane->numer_ewidencyjny." ".$dane->grupa; ?></b>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <b>ilość nadgodzin:</b>
                    </td>
                    <td align="left">
                        <?php
                        $godziny_db = $dane->ilosc_godzin_nadliczbowych;
                        $godz_calkowite = (int)floor($godziny_db);
                        $minuty_z_db    = (int)round(($godziny_db - $godz_calkowite) * 60);
                        ?>
                        <select name="godziny_h">
                            <?php for($x = 0; $x <= 15; $x++) { ?>
                                <option value="<?php echo $x; ?>" <?php if($x == $godz_calkowite) echo "SELECTED"; ?>>
                                    <?php echo $x; ?> h
                                </option>
                            <?php } ?>
                        </select>
                        <select name="godziny_m">
                            <?php for($m = 0; $m < 60; $m++) { ?>
                                <option value="<?php echo $m; ?>" <?php if($m == $minuty_z_db) echo "SELECTED"; ?>>
                                    <?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?> min
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <b>Data wykonywania nadgodzin:</b>
                    </td>
                    <td align="left">
                        <input type="text" name="data_wykonywania_nadgodzin" value="<?php echo $dane->data_wykonywania_nadgodzin; ?>" onclick="scwShow(this,event);" class="border padding_2_all">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Popraw dane">
                    </td>
                </tr>
            </table>
        </form>
    <?php }
    function ZmienStatusHr($id, $status) {
        $this->DomyslneUstawiania();
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_dzial_personalny='".$status."', akceptacja_dzial_personalny_user='".$_SESSION['ewnioski_full_name']."', data_akceptacji_dzial_personalny=now() WHERE id='".$id."'";
        mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_affected_rows($polaczenie) > 0) {
            echo "";
        }
        else echo "Wystąpił problem ze zmianą statusu: ".mysqli_error($polaczenie);
    }
    function ZmienStatusKierownik($id, $status) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_kierownik='".$status."', akceptacja_kierownik_user='".$_SESSION['ewnioski_full_name']."', data_akceptacji_kierownik=now() WHERE id='".$id."'";
        mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_affected_rows($polaczenie) > 0) {
            echo "";
        }
        else echo "Wystąpił problem ze zmianą statusu: ".mysqli_error($polaczenie);
    }
    function FormularzWprowadzaniaGodzinNadliczbowych() {
        $polaczenie = $this->MysqliConnect();

        $rym=$this->PobierzMaileKierownikow($_SESSION['ewnioski_oddelegowania_mpk']);
        ?>
        <style>
            .multi-datepicker {
                position: absolute;
                z-index: 100;
                background: white;
                border: 1px solid #ccc;
                border-radius: 4px;
                padding: 8px;
                width: 220px;
                font-family: inherit;
                user-select: none;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            }
            .multi-datepicker.hide {
                display: none;
            }
            .dp-header { display: flex; justify-content: space-between; align-items: center; font-weight: bold; margin-bottom: 6px; }
            .dp-nav { border: none; background: #f0f0f0; cursor: pointer; padding: 2px 8px; border-radius: 3px; }
            .dp-nav:hover { background: #e0e0e0; }
            .dp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
            .dp-daylabel { text-align: center; font-size: 0.8em; color: #888; padding: 2px 0; }
            .dp-day { text-align: center; padding: 4px 0; border-radius: 3px; cursor: pointer; }
            .dp-day:hover { background: #eef; }
            .dp-day.dp-selected { background: #2b6cb0; color: white; font-weight: bold; }
            /* WAŻNE: klasa dedykowana tylko komórkom z date pickerem.
               NIGDY nie ustawiaj position: relative globalnie na "td" -
               psuje to wyliczanie pozycji przez bibliotekę cssdropdown.js (menu Chrome Dropdown),
               która liczy offsetParent w górę drzewa DOM. */
            .nadgodziny-cell {
                position: relative;
            }
        </style>
        <form method="POST" id="form_1">
            <table>
                <tr>
                    <td colspan="2" align="center">
                        <h3>POLECENIE PRACY W GODZINACH NADLICZBOWYCH</h3>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <h4>Na podstawie art. 151§ k.p. powierzam do wykorzystania pracę w godzinach nadliczbowych.<br/>
                            Wykonanie tych prac stanowi szczególną potrzebę pracodawcy.
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <h4>Rodzaj wpisu:</h4>
                        <select name="typ_wpisu_nadgodzin" id="typ_wpisu_nadgodzin" class="padding_5_all"
                                onchange="AktualizujMinutyNadgodzin(); AktualizujWymaganeOdbior(); AktualizujWidocznoscOdbioru(); FiltrujPowodyPoziomII();">
                            <option value="" disabled selected>WYBIERZ RODZAJ WPISU</option>
                            <option value="nadgodziny">Nadgodziny</option>
                            <option value="odpracowanie_godzin">Odpracowanie godzin</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <h4>Uzasadnienie pracy w godzinach nadliczbowych:</h4>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <select name="uzasadnienie_godzin" id="uzasadnienie_godzin" class="padding_5_all" onchange="
                        console.log($('option:selected',this).attr('multi'));
                        if($('option:selected',this).attr('multi') == 1) {
                            $('#poziom_drugi').html('Wybierz powód <select name=\'dodatkowa_przyczyna1\' id=\'dodatkowa_przyczyna1\' class=\'border\' onfocusin= \'PodpowiadaniePrzyczyna()\' onchange=\'Tym()\'></select>').hide().fadeIn();
                        }
                        else $('#poziom_drugi').hide();
                        console.log($('option:selected',this).attr('multi'));
                        if($('option:selected',this).attr('multi') == 2) {
                            $('#dodatkowa_przyczyna').html('Podaj dostawce <textarea name=\'dodatkowa_przyczyna\' id=\'dodatkowa_przyczyna_text\' class=\'border\'></textarea>').hide().fadeIn();
                            $('#poziom_drugi_inne').hide();
                        }
                        else if($('option:selected',this).attr('multi') == 3) {
                            $('#dodatkowa_przyczyna').html('Podaj klienta <textarea type=\'text\' name=\'dodatkowa_przyczyna\'  id=\'dodatkowa_przyczyna_text\' class=\'border\'></textarea>').hide().fadeIn();
                            $('#poziom_drugi_inne').hide();
                        }
                         else if($('option:selected',this).attr('multi') == 4) {
                            $('#dodatkowa_przyczyna').html('Podaj nazwę zakładu PKC <textarea name=\'dodatkowa_przyczyna\'  id=\'dodatkowa_przyczyna_text\' class=\'border\'></textarea>').hide().fadeIn();
                            $('#poziom_drugi_inne').hide();
                        }
                        else $('#dodatkowa_przyczyna').hide();
                        console.log($('option:selected',this).attr('multi'));
                      /*  if($('option:selected',this).attr('multi') == 3) {
                            $('#dodatkowa_przyczyna').html('Podaj klienta <textarea name=\'dodatkowa_przyczyna\' class=\'border\'></textarea>').hide().fadeIn();
                        }
                        else $('#dodatkowa_przyczyna').hide();*/

                        ">
                            <option style="background-color:red;" disabled="disabled" selected="selected"><p style="background-color:red;">WYBIERZ POWÓD</p></option>
                            <?php
                            $q1 = "SELECT DISTINCT(nazwa_I), dodatkowe_pole,  id FROM godziny_nadliczbowe_przyczyny  GROUP BY nazwa_I order by id";
                            $e1 = mysqli_query($polaczenie, $q1) ;
                            $this->BladZapytania($polaczenie, $q1, $_SESSION['ewnioski_full_name']);
                            if(mysqli_num_rows($e1) > 0) {
                                while($d1 = mysqli_fetch_object($e1)) {;
                                    ?>
                                    <option value="<?php echo $d1->nazwa_I; ?>" multi="<?php if($d1->nazwa_I == 'Koszt PKC') echo 1;
                                    else if($d1->nazwa_I == 'Koszt dostawcy') echo 2;
                                    else if($d1->nazwa_I == 'Koszt klient') echo 3;
                                    else if($d1->nazwa_I == 'Koszt innego zakładu PKC') echo 4;
                                    else echo 0;  ?>"><?php echo $d1->nazwa_I; ?></option>
                                <?php }
                            } ?>
                        </select>
                        <br />
                        <br />
                        <div id="dodatkowa_przyczyna"></div>
                        <br />
                        <div id="poziom_drugi"></div>
                        <br />
                        <div id="poziom_drugi_inne" style="display: none"> Wpisz uwagi <textarea name='inny_powod' id='inny_powod_pole' lass=\'border\'></textarea></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <h4>Za pracę w tym dniu pracownicy otrzymają dzień wolny lub wynagrodzenie zgodnie z umową i przepisami Kodeksu Pracy.</h4>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <table align="center" cellspacing="0" cellpadding="0" class="border_top border_left">
                            <tr>
                                <td align="left" class="padding_5_all border_right border_bottom bg_10">
                                    <b>Lp.<b>
                                </td>
                                <td align="left" class="padding_5_all border_right border_bottom bg_10">
                                    <b>Pracownik<b>
                                </td>
                                <td align="left" class="padding_5_all border_right border_bottom bg_10">
                                    <b>Data świadczenia pracy <br/>w godzinach nadliczbowych<b>
                                </td><td align="left" id="naglowek_odbior" class="padding_5_all border_right border_bottom bg_10">
                                    <b>Data odbioru nadgodzin <br/> lub odpracowanych godzin <span id="odbior_wymagany_gwiazdka" style="color:red;"></span><b>
                                </td>
                                <td align="left" class="padding_5_all border_right border_bottom bg_10">
                                    <b>Ilość godzin nadliczbowych<b>
                                </td>
                            </tr>
                            <?php
                            for($i = 0; $i < 20; $i++) {
                                if($i% 2 == 0) $kolor = "#FFFACD";
                                else $kolor = "#";
                                ?>
                                <tr>
                                    <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all border_right border_bottom">
                                        <?php echo $i + 1; ?>
                                    </td>
                                    <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all border_right border_bottom">
                                        <input type="text" name="imie_nazwisko_<?php echo $i; ?>" id="imie_nazwisko_<?php echo $i; ?>" size="80" class="border padding_2_all" onblur="SprawdzZdublowaneWpisy('<?php echo $i; ?>');" onfocus="if($('#data_wykonywania_nadgodzin_<?php echo $i; ?>').val().length == 0) $('#data_wykonywania_nadgodzin_<?php echo $i; ?>').val($('#data_wykonywania_nadgodzin_0').val());" onkeyup="Autopodpowiadanie(this, 'autocomplete/autocomplete_pracownicy.php', 2, true); ">
                                    </td>
                                    <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all border_right border_bottom nadgodziny-cell">
                                        <input type="text" readonly
                                               name="data_wykonywania_nadgodzin_<?php echo $i; ?>"
                                               id="data_wykonywania_nadgodzin_<?php echo $i; ?>"
                                               class="border padding_2_all"
                                               placeholder="Kliknij aby wybrać datę"
                                               style="cursor:pointer; width: 200px;"
                                               onclick="PrzelaczSingleDatePicker(<?php echo $i; ?>)"
                                               onblur="SprawdzZdublowaneWpisy('<?php echo $i; ?>');"/>
                                        <div class="multi-datepicker hide" id="picker_wykonania_<?php echo $i; ?>"></div>
                                    </td>
                                    <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all border_right border_bottom nadgodziny-cell kolumna_odbior">
                                        <input type="text" readonly
                                               id="trigger_odbior_<?php echo $i; ?>"
                                               class="border padding_2_all"
                                               placeholder="Kliknij aby wybrać daty"
                                               style="cursor:pointer; width: 200px;"
                                               onclick="PrzelaczPicker(<?php echo $i; ?>)"/>
                                        <div class="multi-datepicker hide" id="picker_odbior_<?php echo $i; ?>"></div>
                                        <input type="hidden" name="data_odbioru_nadgodzin_<?php echo $i; ?>" id="data_odbioru_nadgodzin_<?php echo $i; ?>"/>
                                    </td>
                                    <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all border_right border_bottom">
                                        <b>Godzin:</b>
                                        <select name="godziny_h_<?php echo $i; ?>" id="godziny_h_<?php echo $i; ?>" onchange="SprawdzZdublowaneWpisy('<?php echo $i; ?>');">
                                            <?php for($x = 0; $x <= 15; $x++) { ?>
                                                <option value="<?php echo $x; ?>"><?php echo $x; ?> h</option>
                                            <?php } ?>
                                        </select>
                                        <select name="godziny_m_<?php echo $i; ?>" id="godziny_m_<?php echo $i; ?>" onchange="SprawdzZdublowaneWpisy('<?php echo $i; ?>');">
                                            <?php for($m = 0; $m < 60; $m++) { ?>
                                                <option value="<?php echo $m; ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?> min</option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php }
                            ?>
                        </table>
                        <div align="center">
                            <input type="hidden" name="nowy_rekord"/>
                            <input type="button" value="Zapisz dane" onclick="WeryfikacjaNadgodziny()">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            /* ============================================================
               AktualizujMinutyNadgodzin - ogranicza wybór minut w polach
               "godziny_m_i" w zależności od wybranego typu wpisu:
               - "nadgodziny"          -> tylko 00 i 30 min
               - "odpracowanie_godzin" -> pełen zakres 00-59 min
               - "" (placeholder)      -> pełen zakres (bez ograniczenia,
                 dopóki użytkownik nie wybierze konkretnego typu)
               ============================================================ */
            function AktualizujMinutyNadgodzin() {
                var typ = document.getElementById('typ_wpisu_nadgodzin').value;
                for (var i = 0; i < 20; i++) {
                    var sel = document.getElementById('godziny_m_' + i);
                    if (!sel) continue;
                    var aktualnaWartosc = sel.value;
                    sel.innerHTML = '';

                    var dozwoloneMinuty = [];
                    if (typ === 'nadgodziny') {
                        dozwoloneMinuty = [0, 30];
                    } else {
                        for (var m = 0; m < 60; m++) {
                            dozwoloneMinuty.push(m);
                        }
                    }

                    dozwoloneMinuty.forEach(function (m) {
                        var opt = document.createElement('option');
                        opt.value = m;
                        opt.textContent = (m < 10 ? '0' + m : m) + ' min';
                        sel.appendChild(opt);
                    });

                    var czyDostepna = dozwoloneMinuty.indexOf(parseInt(aktualnaWartosc, 10)) !== -1;
                    if (czyDostepna) {
                        sel.value = aktualnaWartosc;
                    } else {
                        sel.value = 0;
                    }
                }
            }

            /* ============================================================
               AktualizujWymaganeOdbior - pokazuje gwiazdkę (*) przy nagłówku
               kolumny "Data odbioru nadgodzin", gdy pole jest wymagane
               (typ_wpisu = "odpracowanie_godzin"). Sama walidacja odbywa się
               w WeryfikacjaNadgodziny() - to tylko wskazówka wizualna.
               ============================================================ */
            function AktualizujWymaganeOdbior() {
                var typ = document.getElementById('typ_wpisu_nadgodzin').value;
                var gwiazdka = document.getElementById('odbior_wymagany_gwiazdka');
                if (!gwiazdka) return;
                gwiazdka.textContent = (typ === 'odpracowanie_godzin') ? '*' : '';
            }

            /* ============================================================
               AktualizujWidocznoscOdbioru - ukrywa całą kolumnę "Data odbioru
               nadgodzin" dla typu "nadgodziny". Przy ukrywaniu czyści wartości
               i re-inicjalizuje pickery, żeby stary wybór dat nie wrócił po
               przełączeniu typu z powrotem na "odpracowanie_godzin".
               ============================================================ */
            function AktualizujWidocznoscOdbioru() {
                var typ = document.getElementById('typ_wpisu_nadgodzin').value;
                var pokaz = (typ !== 'nadgodziny');

                var naglowek = document.getElementById('naglowek_odbior');
                if (naglowek) naglowek.style.display = pokaz ? '' : 'none';

                var komorki = document.querySelectorAll('.kolumna_odbior');
                for (var k = 0; k < komorki.length; k++) {
                    komorki[k].style.display = pokaz ? '' : 'none';
                }

                if (!pokaz) {
                    for (var i = 0; i < 20; i++) {
                        var hidden  = document.getElementById('data_odbioru_nadgodzin_' + i);
                        var trigger = document.getElementById('trigger_odbior_' + i);
                        var picker  = document.getElementById('picker_odbior_' + i);
                        if (hidden)  hidden.value = '';
                        if (trigger) trigger.value = '';
                        if (picker) {
                            picker.classList.add('hide');
                            MultiDatePicker('picker_odbior_' + i, 'data_odbioru_nadgodzin_' + i, 'trigger_odbior_' + i);
                        }
                    }
                }
            }

            /* ============================================================
               FiltrujPowodyPoziomII - dla typu "nadgodziny" usuwa z listy
               "Wybierz powód" pozycję "Odpracowanie godzin minusowych".
               Lista jest pobierana ajaxem przy każdym wejściu w pole, więc
               usunięcie opcji nie jest trwałe - przy zmianie typu na
               "odpracowanie_godzin" wróci z kolejnym pobraniem.
               ============================================================ */
            function FiltrujPowodyPoziomII() {
                var typ = document.getElementById('typ_wpisu_nadgodzin').value;
                var sel = document.getElementById('dodatkowa_przyczyna1');
                if (!sel) return;
                if (typ !== 'nadgodziny') return;

                for (var i = sel.options.length - 1; i >= 0; i--) {
                    var opt = sel.options[i];
                    var tekst = (opt.text + ' ' + opt.value).toLowerCase().replace(/_/g, ' ');
                    if (tekst.indexOf('odpracowanie godzin minusowych') !== -1) {
                        opt.parentNode.removeChild(opt);
                    }
                }
            }

            /* ============================================================
               MultiDatePicker - kalendarz z możliwością zaznaczenia wielu dni
               Używany przez pole "Data odbioru nadgodzin" (picker_odbior_i)
               ============================================================ */
            function MultiDatePicker(containerId, hiddenInputId, triggerId) {
                var container = document.getElementById(containerId);
                var hiddenInput = document.getElementById(hiddenInputId);
                var trigger = document.getElementById(triggerId);
                var selected = new Set();
                var current = new Date();
                current.setDate(1);

                var monthNames = ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
                    'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'];
                var dayLabels = ['Pn','Wt','Śr','Cz','Pt','So','Nd'];

                function updateTrigger() {
                    var sorted = Array.from(selected).sort();
                    hiddenInput.value = sorted.join(',');
                    trigger.value = sorted.length > 0
                        ? sorted.map(function (isoDate) {
                            var p = isoDate.split('-');
                            return p[2] + '.' + p[1];
                        }).join(', ')
                        : '';
                }

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
                        btn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            current.setMonth(current.getMonth() + parseInt(this.getAttribute('data-dir'), 10));
                            render();
                        });
                    });

                    container.querySelectorAll('.dp-day').forEach(function (cell) {
                        cell.addEventListener('click', function (e) {
                            e.stopPropagation();
                            var iso = this.getAttribute('data-date');
                            if (selected.has(iso)) {
                                selected.delete(iso);
                                this.classList.remove('dp-selected');
                            } else {
                                selected.add(iso);
                                this.classList.add('dp-selected');
                            }
                            updateTrigger();
                        });
                    });
                }

                render();
            }

            function PrzelaczPicker(i) {
                var picker = document.getElementById('picker_odbior_' + i);
                var byloOtwarte = !picker.classList.contains('hide');

                document.querySelectorAll('.multi-datepicker').forEach(function (p) {
                    p.classList.add('hide');
                });

                if (!byloOtwarte) {
                    picker.classList.remove('hide');
                }
            }

            /* ============================================================
               SingleDatePicker - kalendarz jednodniowy
               Używany przez pole "Data świadczenia pracy" (picker_wykonania_i)
               ============================================================ */
            function SingleDatePicker(containerId, inputId) {
                var container = document.getElementById(containerId);
                var input = document.getElementById(inputId);
                var current = new Date();
                current.setDate(1);
                var selectedIso = input.value || null;

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
                        html += '<div class="dp-day' + (iso === selectedIso ? ' dp-selected' : '')
                            + '" data-date="' + iso + '">' + d + '</div>';
                    }
                    html += '</div>';
                    container.innerHTML = html;

                    container.querySelectorAll('.dp-nav').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            current.setMonth(current.getMonth() + parseInt(this.getAttribute('data-dir'), 10));
                            render();
                        });
                    });

                    container.querySelectorAll('.dp-day').forEach(function (cell) {
                        cell.addEventListener('click', function (e) {
                            e.stopPropagation();
                            selectedIso = this.getAttribute('data-date');
                            input.value = selectedIso;
                            container.classList.add('hide');
                        });
                    });
                }

                render();
            }

            function PrzelaczSingleDatePicker(i) {
                var picker = document.getElementById('picker_wykonania_' + i);
                var byloOtwarte = !picker.classList.contains('hide');

                document.querySelectorAll('.multi-datepicker').forEach(function (p) {
                    p.classList.add('hide');
                });

                if (!byloOtwarte) {
                    picker.classList.remove('hide');
                    SingleDatePicker('picker_wykonania_' + i, 'data_wykonywania_nadgodzin_' + i);
                }
            }

            /* Zamykanie każdego otwartego kalendarza po kliknięciu gdziekolwiek poza nim
               lub poza polami wyzwalającymi (trigger_odbior_i / data_wykonywania_nadgodzin_i) */
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.multi-datepicker')
                    && !e.target.closest('[id^="trigger_odbior_"]')
                    && !e.target.closest('[id^="data_wykonywania_nadgodzin_"]')) {
                    document.querySelectorAll('.multi-datepicker').forEach(function (p) {
                        p.classList.add('hide');
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                // Placeholder jest domyślnie zaznaczony, więc na starcie strony
                // minuty pozostają w pełnym zakresie, a gwiazdka wymagalności jest pusta
                AktualizujMinutyNadgodzin();
                AktualizujWymaganeOdbior();
                AktualizujWidocznoscOdbioru();

                for (var i = 0; i < 20; i++) {
                    if (document.getElementById('picker_odbior_' + i)) {
                        MultiDatePicker('picker_odbior_' + i, 'data_odbioru_nadgodzin_' + i, 'trigger_odbior_' + i);
                    }
                    // picker_wykonania_i (jednodniowy) tworzy się dopiero na kliknięcie w PrzelaczSingleDatePicker()
                }
            });
        </script>
    <?php }
    function ZapiszNadgodziny() {
        $ids = "";
        $uzasadnienie_godzin = $_POST['uzasadnienie_godzin'];

        // Typ wpisu (nadgodziny / odpracowanie_godzin), wybierany raz dla całego formularza
        if(!isset($_POST['typ_wpisu_nadgodzin'])) $_POST['typ_wpisu_nadgodzin'] = '';
        $typ_wpisu = $_POST['typ_wpisu_nadgodzin'];

        if(!isset($_POST['dodatkowa_przyczyna1'])) $_POST['dodatkowa_przyczyna1'] = '';
        if(!isset($_POST['dodatkowa_przyczyna'])) $_POST['dodatkowa_przyczyna'] = $_POST['dodatkowa_przyczyna1'];
        $dodatkowa_przyczyna = $_POST['dodatkowa_przyczyna'];
        if($_POST['dodatkowa_przyczyna1'] == 'Inne') $dodatkowa_przyczyna = $_POST['dodatkowa_przyczyna1'];

        $dodatkowa_przyczyna = str_replace('_', ' ', $dodatkowa_przyczyna);
        if(!isset($_POST['inny_powod'])) $_POST['inny_powod'] = '';
        $inny_powod = $_POST['inny_powod'];

        unset($_POST['uzasadnienie_godzin']);
        unset($_POST['nowy_rekord']);

        $polaczenie = $this->MysqliConnect();
        $mail_wyslij = false;   // inicjalizacja - używane po pętli
        $mail_tabelka = "<tr>
                <td align='center' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; border-top: 1px solid #000000; border-left: 1px solid #000000; background-color: #4ba1ea;'><b>Lp.</b></td>
                <td align='center' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; border-top: 1px solid #000000; background-color: #4ba1ea;'><b>Pracownik</b></td>
                <td align='center' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; border-top: 1px solid #000000; background-color: #4ba1ea;'><b>Data świadczenia pracy w godzinach nadliczbowych</b></td>
                <td align='center' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; border-top: 1px solid #000000; background-color: #4ba1ea;'><b>Ilość godzin nadliczbowych</b></td>
            </tr>";
        $numer_formularza = $this->WyciagnijNastepnyNumerFormularza();

        for($i = 0; $i < 20; $i++) {
            if(!isset($_POST['data_odbioru_nadgodzin_'.$i])) $_POST['data_odbioru_nadgodzin_'.$i] = '';

            $odbiorWymagany = ($typ_wpisu == 'odpracowanie_godzin');
            // UWAGA: nawiasy / && są konieczne - "AND" ma niższy priorytet niż "="
            $brakOdbioru = ($odbiorWymagany && strlen(trim($_POST['data_odbioru_nadgodzin_'.$i])) == 0);

            if(strlen(trim($_POST['imie_nazwisko_'.$i])) == 0 AND strlen(trim($_POST['data_wykonywania_nadgodzin_'.$i])) == 0 AND strlen(trim($_POST['data_odbioru_nadgodzin_'.$i])) == 0 AND (int)$_POST['godziny_h_'.$i] == 0 AND (int)$_POST['godziny_m_'.$i] == 0) { ?>
                <div style="color: #808080"><b><?php echo $i + 1; ?> &nbsp; &nbsp; Linijka nie uzupełniona: pomijam </b></div>
            <?php }
            else if(strlen(trim($_POST['imie_nazwisko_'.$i])) == 0 OR strlen(trim($_POST['data_wykonywania_nadgodzin_'.$i])) == 0 OR $brakOdbioru OR ((int)$_POST['godziny_h_'.$i] == 0 AND (int)$_POST['godziny_m_'.$i] == 0)) { ?>
                <div style="color: #aa0000"><b><?php echo $i + 1; ?> &nbsp; &nbsp; Dane nie zostały zapisane: Część kolumn nie została poprawnie uzupełniona </b></div>
            <?php }
            else {
                $pracownik = explode("---", $_POST['imie_nazwisko_'.$i]);

                $dostep = false;
                $grupyx = explode(",", $_SESSION['ewnioski_oddelegowania_mpk']);
                foreach($grupyx as $grupax) {
                    $grupyz = explode(",", $pracownik[3]);
                    foreach($grupyz as $grupaz) {
                        if($grupax == $grupaz AND strlen(trim($grupax)) > 0 AND strlen(trim($grupaz)) > 0) $dostep = true;
                    }
                }

                if(($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' || $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager') && $dostep == true && $_SESSION['ewnioski_login'] != $pracownik[2]) {
                    $akceptacja_kierownik = 'tak';
                    $akceptacja_kierownik_user = $_SESSION['ewnioski_login'];
                    $data_akceptacji_kierownik = "now()";
                    $mail_wyslij = false;
                }
                else {
                    $akceptacja_kierownik = "nie";
                    $akceptacja_kierownik_user = '';
                    $data_akceptacji_kierownik = "''";
                    $mail_wyslij = true;
                }

                if($_SESSION['ewnioski_oddelegowania_mpk'] != '') $mpk = $_SESSION['ewnioski_oddelegowania_mpk'];
                else $mpk = $_SESSION['ewnioski_mpk'];

                $ilosc_godzin_decimal = (int)$_POST['godziny_h_'.$i] + round((int)$_POST['godziny_m_'.$i] / 60, 4);

                $odbiorRaw = trim($_POST['data_odbioru_nadgodzin_'.$i]);
                $listaDatOdbioru = array_filter(explode(',', $odbiorRaw));
                $odbiorBezpieczny = array();
                foreach ($listaDatOdbioru as $dzien) {
                    $dzien = trim($dzien);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dzien)) {
                        $odbiorBezpieczny[] = $dzien;
                    }
                }
                $odbiorNadgodzinDoZapisu = implode(',', $odbiorBezpieczny);

                $zapytanie = "INSERT INTO godziny_nadliczbowe_godziny (pracownik, numer_ewidencyjny, grupa, ilosc_godzin_nadliczbowych, data_wykonywania_nadgodzin, data_odpracowania, typ_wpisu, wprowadzil, data_wprowadzenia, uzasadnienie, uzasadnienie_poziom_2, dodatkowe_uzasadnienie,akceptacja_kierownik, akceptacja_kierownik_user, data_akceptacji_kierownik, numer_formularza, dir, oddelegowania_grupa,profil)
            VALUES ('".$pracownik[0]." ".$pracownik[1]."', '".$pracownik[2]."', '".addslashes($pracownik[3])."','".$ilosc_godzin_decimal."', '".str_replace("-","",$_POST['data_wykonywania_nadgodzin_'.$i])."' , '".$odbiorNadgodzinDoZapisu."', '".$typ_wpisu."', '".$_SESSION['ewnioski_full_name']."',
            now(),'".$uzasadnienie_godzin."','".$dodatkowa_przyczyna."','".$inny_powod."','".$akceptacja_kierownik."', '".$akceptacja_kierownik_user."', ".$data_akceptacji_kierownik.",'".$numer_formularza."', '".$_SESSION['ewnioski_oddelegowania_dir']."', '".$mpk."','" . trim($_SESSION['ewnioski_profil_wpisz_dane'])."')";

                $wykonaj = mysqli_query($polaczenie, $zapytanie);
                $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
                $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);

                if(mysqli_affected_rows($polaczenie) > 0) { ?>
                    <div style="color: #00aa00"><b><?php echo $i + 1; ?>. &nbsp; &nbsp;
                            Dane zostały zapisane: Imię: <?php echo $pracownik[0]; ?> ,
                            Nazwisko: <?php echo $pracownik[1]; ?>,
                            Numer ewidencyjny: <?php echo $pracownik[2]; ?>,
                            Grupa: <?php echo $pracownik[3]; ?>,
                            Ilość nadgodzin: <?php echo $ilosc_godzin_decimal; ?></b></div>
                    <?php
                    if($i % 2 == 0) $kolor = "#86ec9c";
                    else $kolor = "#6fc381";
                    $mail_tabelka .= "<tr>
                <td align='left' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; background-color: ".$kolor.";'>".($i+1)."</td>
                <td align='left' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; background-color: ".$kolor.";'>".$_POST['imie_nazwisko_'.$i]."</td>
                <td align='left' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; background-color: ".$kolor.";'>".$_POST['data_wykonywania_nadgodzin_'.$i]."</td>
                <td align='left' style='padding: 5px 5px 5px 5px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; background-color: ".$kolor.";'>".$ilosc_godzin_decimal."</td>
            </tr>";
                }
                else { ?>
                    <div style="color: #aa0000"><b><?php echo $i + 1; ?> &nbsp; &nbsp;Dane nie zostały zapisane: <?php echo mysqli_error($polaczenie); ?></b></div>
                <?php }
            }
        }

        // Mail do kierownika
        $adres = explode("/", $_SERVER['HTTP_REFERER']);
        $adres[5] = "akceptacja_nadgodzin.php";
        $adres = implode("/", $adres);
        $mail_tabelka = "<div align='center'><h3>Pracownik: ".$_SESSION['ewnioski_full_name']." wprowadził następujące nadgodziny:
    <br/>Jeżli chcesz je wszystkie zaakceptować kliknij ten link: <a href='".$adres."?formularz=".base64_encode($numer_formularza)."'><span style='color: #aa0000;'>Akceptacja</span></a></h3></div><table align='center' cellspacing='0' cellpadding='0'>".$mail_tabelka."</table>";
        $maile = $this->DodatkowyMail.",".$this->PobierzMaileKierownikow($_SESSION['ewnioski_oddelegowania_mpk']);
        if(strlen($maile) > 0) {
            echo "<b style='color: #00aa00;'>Mail został wysłany do: ".$maile."</b>";
            if($mail_wyslij) {
                $this->WyslijMaila($maile, $mail_tabelka, "PKC Ewidencja Nadgodzin nadgodziny do zaakceptowania ", "EwidencjaNadgodzin.Admin@motherson.com");
            }
        }
        else echo "<b style='color: #aa0000;'>Mail nie został wysłany do prawdopodobnie mail jest nieuzupełniony</b>";
    }
    function WyciagnijNastepnyNumerFormularza() {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT CASE WHEN MAX(numer_formularza) IS NULL THEN '1' ELSE (MAX(numer_formularza) + 1) END as numer_formularza FROM godziny_nadliczbowe_godziny";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $dane = mysqli_fetch_array($wykonaj);
        return $dane['numer_formularza'];
    }
    function GuzikPoleceniePracyWGodzinachNadliczbowych() { ?>
        <form action="" method="post">
            <input type="hidden" name="nowe_nadgodziny">
            <input type="submit" value="Polecenie pracy w godzinach nadliczbowych">
        </form>
    <?php }
    function GuzikEksportExcel($zapytanie) { ?>
        <form action="" method="post">
            <input type="hidden" name="excel_export" value="<?php echo base64_encode($zapytanie); ?>">
            <input type="submit" value="Eksport do Excela">
        </form>
    <?php }
    function RaportNadgodziny() {
        if(!isset($_GET['sort_dir'])) $_GET['sort_dir'] = "DESC";
        if(!isset($_GET['sort'])) $_GET['sort'] = "id";
        $this->IloscDanychNaStronieLista();
        ?>
        <table width="100%" cellspacing="0" class="border_top">
            <tr>
                <td colspan="24" align="center" class="padding_5_all bg_10 bottom">
                    <?php
                    $this->FilterDatowy();
                    ?>
                </td>
            </tr>
            <tr>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <b>Lp.</b>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <b>Edy.</b>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <b>Kas.</b>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <b>Zaz.</b>
                    <?php
                    if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'log_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin') { ?>
                        <input type="checkbox" id="global_check" onclick="
                    var stan = $(this).is(':checked');
                    var id_list = '';
                    $('input:checkbox').each(function(index, element){
                        if(stan == true) $(element).prop('checked', true);
                        else $(element).prop('checked', false);
                        if(element.id != 'global_check' && $('#'+element.id).is(':checked')) id_list += element.value+',';
                    });
                     $('#zaznaczenie_ids').val(id_list.substring(0,(id_list.length -1)));
                     $('#ilosc_zaznaczonych').html((id_list.split(',').length - 1));
                    ">
                    <?php }
                    ?>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=id&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'id' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Id</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    Zakład
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=numer_ewidencyjny&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'numer_ewidencyjny' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Numer</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=pracownik&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'pracownik' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Pracownik</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=grupa&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'grupa' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Grupa</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=ilosc_godzin_nadliczbowych&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'ilosc_godzin_nadliczbowych' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Godziny</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=data_wykonywania_nadgodzin&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'data_wykonywania_nadgodzin' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Data nadgodzin</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=typ_wpisu&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'typ_wpisu' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Typ wpisu</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=wprowadzil&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'wprowadzil' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Wprowadził</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=odpracowanie&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'odpracowanie' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Data odpracowania</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=uzasadnienie&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'uzasadnienie' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Uzasadnienie</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=uzasadnienie&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'uzasadnienie' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Uzasadnienie poziom II</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=uzasadnienie&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'uzasadnienie' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Uzasadnienie poziom III</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=akceptacja_kierownik&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'akceptacja_kierownik' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Akceptacja Kierownik</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=akceptacja_dzial_personalny&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'akceptacja_dzial_personalny' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Akceptacja HR</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=brygadzista_notatka&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'brygadzista_notatka' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Bryg. notatka</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=kierownik_notatka&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'kierownik_notatka' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Kier. notatka</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=hr_notatka&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'hr_notatka' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>HR. notatka</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom right">
                    <a href="?sort=hr_notatka&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'data_akceptacji_dzial_personalny' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Data HR</b></a>
                </td>
                <td align="center" class="padding_5_all bg_10 bottom">
                    <a href="?sort=hr_notatka&sort_dir=<?php
                    if(isset($_GET['sort']) AND isset($_GET['sort_dir']) AND $_GET['sort'] == 'akceptacja_dzial_personalny_user' AND $_GET['sort_dir'] == 'ASC') echo "DESC";
                    else echo "ASC";
                    ?>"><b>Zaakcept. HR</b></a>
                </td>
            </tr>
            <?php
            for($i = 0; $i < count($this->RaportNadgodzinyFiltry); $i++) {
                if(!isset($_SESSION[$this->RaportNadgodzinyFiltry[$i]])) $_SESSION[$this->RaportNadgodzinyFiltry[$i]] = "";
                elseif(isset($_POST[$this->RaportNadgodzinyFiltry[$i]])) $_SESSION[$this->RaportNadgodzinyFiltry[$i]] = trim($_POST[$this->RaportNadgodzinyFiltry[$i]]);
            }
            ?>

<!--                FILTRY ###############################################################         -->
            <tr>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <img src="../img/filter_del.gif">
                </td>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <img src="../img/filter_del.gif">
                </td>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <img src="../img/filter_del.gif">
                </td>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <img src="../img/filter_del.gif">
                </td>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_id" size="4"  onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_id'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_id']); ?>"/>
                    </form>
                </td>
                <td nowrap align="center" class="padding_5_all bg_10 bottom right">
                    <img src="../img/filter_del.gif">
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_numer_ewidencyjny" size="12"  onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_numer_ewidencyjny'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_numer_ewidencyjny']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_pracownik" size="12"  onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_pracownik'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_pracownik']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_grupa" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_grupa'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_grupa']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_ilosc_godzin_nadliczbowych" size="12"  onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_ilosc_godzin_nadliczbowych'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_ilosc_godzin_nadliczbowych']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" onclick="scwShow(this,event);" name="ewnioski_produktywnosc_filter_nadgodziny_data_wykonywania_nadgodzin"  size="12" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_wykonywania_nadgodzin'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_wykonywania_nadgodzin']); ?>"/>
                        <input type="button" value="+"  class="guzik_dodaj_odejmij" onclick="this.parentNode.submit();"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST" id="form_f_typ_wpisu">
                        <select name="ewnioski_produktywnosc_filter_nadgodziny_typ_wpisu" onchange="document.forms['form_f_typ_wpisu'].submit();">
                            <option value="">Wszystko</option>
                            <option value="nadgodziny" <?php if($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_typ_wpisu']=='nadgodziny') echo "SELECTED"; ?>>Nadgodziny</option>
                            <option value="odpracowanie_godzin" <?php if($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_typ_wpisu']=='odpracowanie_godzin') echo "SELECTED"; ?>>Odpracowanie godzin</option>
                        </select>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_wprowadzil" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_wprowadzil'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_wprowadzil']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_odpracowanie" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_odpracowanie'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_odpracowanie']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_II" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_II'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_II']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_III" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_III'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_uzasadnienie_poziom_III']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_akceptacja_kierownik" size="12"  onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_kierownik'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_kierownik']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_brygadzista_notatka" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_brygadzista_notatka'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_brygadzista_notatka']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_kierownik_notatka" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_kierownik_notatka'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_kierownik_notatka']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_hr_notatka" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_hr_notatka'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_hr_notatka']); ?>"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom right">
                    <form method="POST">
                        <input type="text"  onclick="scwShow(this,event);" size="8" name="ewnioski_produktywnosc_filter_nadgodziny_data_akceptacji_dzial_personalny"  size="12"  class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_akceptacji_dzial_personalny'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_akceptacji_dzial_personalny']); ?>"/>
                        <input type="button" value="+"  class="guzik_dodaj_odejmij" onclick="this.parentNode.submit();"/>
                    </form>
                </td>
                <td align="center" nowrap class="padding_5_all bg_10 bottom">
                    <form method="POST">
                        <input type="text" name="ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny_user" size="12" onblur="this.parentNode.submit();" class="border" value="<?php if(isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny_user'])) echo trim($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_akceptacja_dzial_personalny_user']); ?>"/>
                    </form>
                </td>
            </tr>
            <?php
            $polaczenie = $this->MysqliConnect();

            if(!isset($_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset'] = 0;
            if(isset($_GET['offset'])) $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset'] =  $_GET['offset'];

            $licznik = 1;
            $dodatkowy_warunek = "";
            $dodatkowy_warunek="  go.profil='".$_SESSION['ewnioski_profil']."'";
            for($i = 0; $i < count($this->RaportNadgodzinyFiltry); $i++) {
                if(isset($_SESSION[$this->RaportNadgodzinyFiltry[$i]]) AND strlen(trim($_SESSION[$this->RaportNadgodzinyFiltry[$i]])) > 0) {
                    if(preg_match("/%/",trim($_SESSION[$this->RaportNadgodzinyFiltry[$i]]))) $operator = " like ";
                    else $operator = "=";
                    if(strlen($dodatkowy_warunek) > 0) $dodatkowy_warunek .= " AND ".$this->RaportNadgodzinyKolumny[$i]." ".$operator." '".trim($_SESSION[$this->RaportNadgodzinyFiltry[$i]])."' ";
                    else $dodatkowy_warunek .= " ".$this->RaportNadgodzinyKolumny[$i]." ".$operator." '".trim($_SESSION[$this->RaportNadgodzinyFiltry[$i]])."' ";
                }
            }

            // Dodatkowy warunek widoczność rekordów

            $dodatkowy_warunek_widocznosc = "";
            // Hr widzi wszystko
            if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'HR' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin') {
                // $dodatkowy_warunek.="  go.profil='".$_SESSION['ewnioski_profil']."'";
            }
            // Administracja widzi tylko dane swoich działów
            else  {
                // $dodatkowy_warunek.="  go.profil='".$_SESSION['ewnioski_profil']."'";
                $q1 = "SELECT numer_ewidencyjny FROM ewnioski.stan_godzin WHERE numer_kierownik='".trim($_SESSION['ewnioski_login'])."' ORDER BY id DESC ";
                $e1 = mysqli_query($polaczenie, $q1) ;
                $this->BladZapytania($polaczenie, $e1, $_SESSION['ewnioski_login']);
                while($d1 = mysqli_fetch_assoc($e1)) {
                    $dodatkowy_warunek_widocznosc .= " OR go.numer_ewidencyjny like '%".$d1['numer_ewidencyjny']."%'";
                }

                $grup = explode(",", $_SESSION['ewnioski_oddelegowania_mpk']);
                foreach($grup as $grup_element) {
                    if(strlen(trim($grup_element)) > 0) {
                        $dodatkowy_warunek_widocznosc .= " OR go.oddelegowania_grupa like '%".$grup_element."%'";
                    }
                }
                $dodatkowy_warunek_widocznosc = "AND (go.numer_ewidencyjny='".trim($_SESSION['ewnioski_login'])."' ".$dodatkowy_warunek_widocznosc.")";

            }
            /* elseif($_SESSION['ewnioski_oddelegowania_dir'] == 'dir') {
                // $dodatkowy_warunek_widocznosc = "AND (go.dir='dir' OR go.dir IS NULL)";
             }*/
            if(strlen($dodatkowy_warunek) > 0) $dodatkowy_warunek = "AND (".$dodatkowy_warunek.") ";
            if(isset($_GET['sort']) AND isset($_GET['sort_dir'])) $sort = " ORDER BY ".trim($_GET['sort'])." ".trim($_GET['sort_dir']);
            else $sort = "";
            $zapytanie = "SELECT 
            DISTINCT
            go.id as id,
            go.pracownik,
            go.numer_ewidencyjny,
            go.grupa,
            go.ilosc_godzin_nadliczbowych,
            go.data_wykonywania_nadgodzin,
            go.data_odpracowania,
            go.typ_wpisu,
            go.wprowadzil,
            go.uzasadnienie,
       go.uzasadnienie_poziom_2,
       go.dodatkowe_uzasadnienie,
            go.akceptacja_kierownik,
            go.akceptacja_dzial_personalny,
            go.brygadzista_notatka,
            go.kierownik_notatka,
            go.hr_notatka,
            go.anulowano,
            go.akceptacja_kierownik_user,
            go.akceptacja_dzial_personalny_user,
            akceptacja_kierownik_user,
            go.data_akceptacji_kierownik,
            go.data_akceptacji_dzial_personalny,
            go.data_wprowadzenia,
            us.mpk,
            go.profil,
            p.nazwa as nazwa_profilu            
            FROM godziny_nadliczbowe_godziny go 
            LEFT JOIN oddelegowania.users us ON(
                us.id=(SELECT us1.id FROM oddelegowania.users us1 WHERE (us1.numer_ewidencyjny=go.numer_ewidencyjny OR go.numer_ewidencyjny='".$_SESSION['ewnioski_login']."' OR us1.numer_ewidencyjny='".$_SESSION['ewnioski_login']."') OR us1.full_name=go.wprowadzil LIMIT 1)
            )
            LEFT JOIN profil p ON(go.profil=p.id) 
            WHERE  DATE_FORMAT(go.data_wykonywania_nadgodzin,'%Y%m%d') >= '".str_replace("-","",$_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_poczatkowa'])."'
            AND DATE_FORMAT(go.data_wykonywania_nadgodzin,'%Y%m%d') <= '".str_replace("-","",$_SESSION['ewnioski_produktywnosc_filter_nadgodziny_data_koncowa'])."'
            ".$dodatkowy_warunek.$dodatkowy_warunek_widocznosc.$sort;

            $zapytanie_limit = $zapytanie." LIMIT ".$_SESSION['ewnioski_produktywnosc_filter_nadgodziny_offset'].",".$_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek'];
            $wykonaj = mysqli_query($polaczenie, $zapytanie_limit);
            $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
            if(mysqli_num_rows($wykonaj) > 0) {
                if(mysqli_num_rows($wykonaj) == $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek']) { ?>
                    <h3 style="color: #aa0000">Uwaga liczba wyników przekracza <?php echo $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek']; ?> pozycji nie wszytkie wyniki są pokazane na ekranie Proszę użyć dodatkowych filtrów lub przełączyć stronę</h3>
                <?php }

                $wykonaj_liczba_ston = mysqli_query($polaczenie, $zapytanie);
                $wykonaj_liczba_ston = ceil(mysqli_num_rows($wykonaj_liczba_ston) / $_SESSION['ewnioski_produktywnosc_filter_nadgodziny_liczba_linijek']);

                $this->Paginator($wykonaj_liczba_ston);

                while($dane = mysqli_fetch_object($wykonaj)) {

                    if($licznik % 2 == 0) $kolor = "#fffacd";
                    else $kolor = "#f4efc0";

                    if($dane->akceptacja_kierownik == 'tak') {
                        if($licznik % 2 == 0) $kolor = "#c187c8";
                        else $kolor = "#e3a6e7";
                    }

                    if($dane->akceptacja_dzial_personalny == 'tak') {
                        if($licznik % 2 == 0) $kolor = "#7dc47d";
                        else $kolor = "#84dc84";
                    }

                    if($dane->anulowano == 'tak') {
                        if($licznik % 2 == 0) $kolor = "#7c7c7c";
                        else $kolor = "#8e8e8e";
                    }
                    $dostep = false;
                    $grupyx = explode(",",$_SESSION['ewnioski_oddelegowania_mpk']);

                    foreach($grupyx as $grupax) {
                        // var_dump($grupax);
                        $grupyz = explode(",",$dane->mpk);
                        //var_dump($dane);
                        foreach($grupyz as $grupaz) {
                            if($_SESSION['ewnioski_login'] == 'jbugaj') {
                                var_dump($grupax);
                                var_dump($grupaz);
                                echo "<hr/>";
                            }
                            //  var_dump($grupyz);
                            if($grupax == $grupaz AND strlen(trim($grupax)) > 0 AND strlen(trim($grupaz)) > 0) $dostep = true;
                            if( $_SESSION['ewnioski_login']=='47021') $dostep = true;
                        }
                    }
                    //var_dump($dostep);

                    ?>
                    <tr <?php if($dane->anulowano == 'tak') echo "style='text-decoration: line-through; '"; ?>>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $licznik; ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            // Edycja
                            if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr'
                                    OR ($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik'||$_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' AND $dostep == true AND $dane->akceptacja_dzial_personalny == 'nie')
                                    OR ($dane->wprowadzil == trim($_SESSION['ewnioski_full_name']) AND $dane->akceptacja_kierownik == 'nie' AND $dane->akceptacja_dzial_personalny == 'nie')) { ?>
                                <img src="../img/b_edit.png" class="border" style="cursor: hand; cursor: pointer;" onclick="window.open('nadgodziny_edycja.php?id=<?php echo base64_encode($dane->id); ?>','_blank', 'width=640, height=480, menubar=0, scrollbars=0,toolbar=0')">
                            <?php }
                            else { ?>
                                <img src="../img/s_rights.png" title="Brak uprawnień do edycji, uprawnienia posiada: <?php echo $dane->wprowadzil; ?> <?php if($dane->akceptacja_dzial_personalny == 'tak') echo "Dział personalny zaakceptował tą pozycję edycja nie jest już możliwa"; ?>"/>
                            <?php } ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            // Kasowanie
                            if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr'
                                    OR ($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik'||$_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' AND $dostep == true AND $dane->akceptacja_dzial_personalny == 'nie')
                                    OR ($dane->wprowadzil == trim($_SESSION['ewnioski_full_name']) AND $dane->akceptacja_kierownik == 'nie' AND $dane->akceptacja_dzial_personalny == 'nie'))  { ?>
                                <form method="POST" id="form_<?php echo $licznik; ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo $dane->id; ?>">
                                    <input type="button" class="kasowanie_guzik" style="cursor: hand; cursor: pointer;" onclick="CzyNaPewno('form_<?php echo $licznik; ?>','Czy jesteś pewien/pewna że chcesz skasować tą pozycję?')">
                                </form>
                            <?php }
                            else { ?>
                                <img src="../img/s_rights.png" title="Brak uprawnień do kasowania, uprawnienia posiada: <?php echo $dane->wprowadzil; ?> <?php if($dane->akceptacja_dzial_personalny == 'tak') echo "Dział personalny zaakceptował tą pozycję edycja nie jest już możliwa"; ?>"/>
                            <?php } ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            // Zaznaczenie
                            if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr'
                                    OR (($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'log_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin') AND $dostep == true AND $dane->akceptacja_dzial_personalny == 'nie'))  { ?>
                                <input type="checkbox" onclick="PrzekazIdZaznaczonychCheckboxow();" id="checkbox_<?php echo $licznik; ?>" value="<?php echo $dane->id; ?>"/>
                            <?php }
                            else { ?>
                                <img src="../img/s_rights.png" Title="Brak uprawnień do globalnej modyfikacji"/>
                            <?php }?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $dane->id; ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->nazwa_profilu); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->numer_ewidencyjny); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->pracownik); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->grupa); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            $g_val  = $dane->ilosc_godzin_nadliczbowych;
                            if ($g_val !== null) {
                                $g_h = (int)floor((float)$g_val);
                                $g_m = (int)round(((float)$g_val - $g_h) * 60);
                                echo $g_h . ' h ' . str_pad($g_m, 2, '0', STR_PAD_LEFT) . ' min';
                            } else {
                                echo $this->BrakDanych($g_val);
                            }
                            ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->data_wykonywania_nadgodzin); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->typ_wpisu == 'odpracowanie_godzin' ? 'Odpracowanie godzin' : 'Nadgodziny'); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->wprowadzil); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych(str_replace(',', '<br>', $dane->data_odpracowania)); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->uzasadnienie); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->uzasadnienie_poziom_2); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->dodatkowe_uzasadnienie); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            if($dane->akceptacja_dzial_personalny == 'nie' AND (($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'log_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin') AND $dostep == true)) { ?>
                                <select style="background-color: <?php if($dane->akceptacja_kierownik == 'nie') echo '#aa0000;'; else echo '#00aa00'; ?>; border: 1px #000000 solid;"  onchange="
                                        $.post( '../ajax/ChangeStatusKierownik.php', { id: '<?php echo $dane->id; ?>', status: $(this).val() })
                                        .done(function( data ) {
                                        if(data.length > 0) alert(data);
                                        window.location = window.location;
                                        });
                                        if($(this).val() == 'tak') $('.row_color_<?php echo $licznik; ?>').css('background-color','#00aa00');
                                        else $('.row_color_<?php echo $licznik; ?>').css('background-color','#aa0000');">
                                    <option value="nie" <?php if($dane->akceptacja_kierownik == 'nie') echo "SELECTED"; ?> >Nie</option>
                                    <option value="tak" <?php if($dane->akceptacja_kierownik == 'tak') echo "SELECTED"; ?>  >Tak</option>
                                </select>
                                <?php

                            }
                            else echo ucfirst($dane->akceptacja_kierownik);
                            ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            if(isset($_SESSION['ewnioski_oddelegowania_permision']) AND $_SESSION['ewnioski_oddelegowania_permision'] == 'hr' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'master_admin' ) { ?>
                                <select style="background-color: <?php if($dane->akceptacja_dzial_personalny == 'nie') echo '#aa0000;'; else echo '#00aa00'; ?>; border: 1px #000000 solid;" onchange="
                                        $.post( '../ajax/ChangeStatusHR.php', { id: '<?php echo $dane->id; ?>', status: $(this).val() })
                                        .done(function( data ) {
                                        if(data.length > 0) alert(data);
                                        window.location = window.location;
                                        });

                                        if($(this).val() == 'tak') $('.row_color_<?php echo $licznik; ?>').css('background-color','#00aa00');
                                        else $('.row_color_<?php echo $licznik; ?>').css('background-color','#aa0000');">
                                    <option value="nie" <?php if($dane->akceptacja_dzial_personalny == 'nie') echo "SELECTED"; ?> >Nie</option>
                                    <option value="tak" <?php if($dane->akceptacja_dzial_personalny == 'tak') echo "SELECTED"; ?>  >Tak</option>
                                </select>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="anulowanie_notatka" value="<?php echo $dane->hr_notatka; ?>"/> &nbsp;
                                    <input type="hidden" name="anulowanie_id" value="<?php echo $dane->id; ?>"/> &nbsp;
                                    <input type="hidden" name="anulowanie_full_name" value="<?php echo $dane->wprowadzil; ?>"/>
                                    <img src="../img/x.png" style="cursor: hand; cursor: pointer;" title="Jeśli chcesz anulować tą pozycję kliknij mnie" onclick="if(confirm('Czy na pewno chcesz anulować tą pozycję?')) $(this).parent().submit(); "/>
                                </form>
                            <?php }
                            else echo ucfirst($dane->akceptacja_dzial_personalny);
                            ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            if($dane->wprowadzil == trim($_SESSION['ewnioski_full_name'])) { ?>
                                <img src="../img/b_edit.png" title="Edycja notatki" class="border" style="cursor: hand; cursor: pointer;" onclick="window.open('edycja_notatki.php?id=<?php echo base64_encode($dane->id); ?>&kolumna=<?php echo base64_encode('brygadzista_notatka'); ?>','_blank', 'width=640, height=480, menubar=0, scrollbars=0,toolbar=0')">
                            <?php }
                            echo $this->BrakDanych($dane->brygadzista_notatka);
                            ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            if(isset($_SESSION['ewnioski_oddelegowania_permision']) AND ($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager') AND $dostep == true) { ?>
                                <img src="../img/b_edit.png" title="Edycja notatki" class="border" style="cursor: hand; cursor: pointer;" onclick="window.open('edycja_notatki.php?id=<?php echo base64_encode($dane->id); ?>&kolumna=<?php echo base64_encode('kierownik_notatka'); ?>','_blank', 'width=640, height=480, menubar=0, scrollbars=0,toolbar=0')">
                            <?php }
                            echo $this->BrakDanych($dane->kierownik_notatka);
                            ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php
                            if(isset($_SESSION['ewnioski_oddelegowania_permision']) AND $_SESSION['ewnioski_oddelegowania_permision'] == 'hr') { ?>
                                <img src="../img/b_edit.png" title="Edycja notatki" class="border" style="cursor: hand; cursor: pointer;" onclick="window.open('edycja_notatki.php?id=<?php echo base64_encode($dane->id); ?>&kolumna=<?php echo base64_encode('hr_notatka'); ?>','_blank', 'width=640, height=480, menubar=0, scrollbars=0,toolbar=0')">
                            <?php }
                            echo $this->BrakDanych($dane->hr_notatka); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom right row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->data_akceptacji_dzial_personalny); ?>
                        </td>
                        <td align="center" style="background-color: <?php echo $kolor; ?>" class="padding_5_all bottom row_color_<?php echo $licznik; ?>">
                            <?php echo $this->BrakDanych($dane->akceptacja_dzial_personalny_user); ?>
                        </td>
                    </tr>
                    <?php
                    $licznik++; }
            } ?>
            <tr>
                <td colspan="6" align="center" class="padding_5_all bottom bg_10 border_right">
                    <b>Zaznaczonych pozycji:</b>
                    &nbsp; <b id="ilosc_zaznaczonych">0</b>
                    &nbsp; <b>Zmień akceptację na: </b>
                    <?php
                    if($_SESSION['ewnioski_oddelegowania_permision'] == 'hr') {
                        $this->GlobalnaZmianaStatusuHr();
                    }
                    if($_SESSION['ewnioski_oddelegowania_permision'] == 'kierownik' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'prod_manager' OR $_SESSION['ewnioski_oddelegowania_permision'] == 'log_manager') {
                        $this->GlobalnaZmianaStatusuKierownik();
                    } ?>
                </td>
                <td colspan="20" align="center" class="padding_5_all bottom bg_10">
                    <?php echo $this->GuzikEksportExcel($zapytanie); ?>
                </td>
            </tr>
        </table>
    <?php }
    function GlobalnaZmianaStatusuHr() { ?>
        <form method="post" class="inline">
            <input type="hidden" id="zaznaczenie_ids" name="zaznaczenie_ids" value="" />
            <select name="hr_globalna_zmiana" onchange="if(confirm('Czy na pewnoc chcesz zmienić status wszystkich zaznaczonych pozycji?')) this.parentNode.submit();">
                <option value=""></option>
                <option value="tak">Tak</option>
                <option value="nie">Nie</option>
            </select>
        </form>
    <?php }
    function GlobalnaZmianaStatusuKierownik() { ?>
        <form method="post" class="inline">
            <input type="hidden" id="zaznaczenie_ids" name="zaznaczenie_ids" value="" />
            <select name="kierownik_globalna_zmiana" onchange="if(confirm('Czy na pewnoc chcesz zmienić status wszystkich zaznaczonych pozycji?')) this.parentNode.submit();">
                <option value=""></option>
                <option value="tak">Tak</option>
                <option value="nie">Nie</option>
            </select>
        </form>
    <?php }
    function KasowanieLinieNadgodzin($id) {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "DELETE FROM godziny_nadliczbowe_godziny  WHERE id='".$id."'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_full_name']);
        $this->ZapiszLog('Zapytanie;' . $zapytanie, $_SESSION['ewnioski_full_name']);
        if(mysqli_affected_rows($polaczenie) > 0) { ?>
            <b style="color: #00aa00">Dane zostały usunięte</b>
        <?php }
        else { ?>
            <b style="color: #aa0000">Dane nie zostały usunięte <br/><?php echo mysqli_error($polaczenie); ?></b>
        <?php }
    }
}