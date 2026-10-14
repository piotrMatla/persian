<?php

/**
 *  Podstawowe funkcje programu
 * \author Jakub Duda
 * \version 1.4
 * \date 07-2017
 */
class Common extends Filtrowanie {
    /**
     * Wszytanie pliku konfiguracyjnego
     * @var string  Scieżka pliku konfiguracyjnego
     */
    public $iniPathE = "/home/lora/public_html/eWnioski/conf/confe.ini";
    /**
     *  Ustawienei sesji i strefy czasowej
     */
    function UstawieniaStandardowe() {
        set_time_limit(21600);
        ini_set("memory_limit","3072M");
        header("Content-Type:   text/html; charset=utf-8");
        date_default_timezone_set('Europe/Warsaw');
        if(!isset($_SESSION)) session_start();
    }

    /**
     * Sprawdzenie czy użytkownik jest zalogowany
     */
    function CzyZalogowany() {

        if (isset($_SESSION['ewnioski_login'])) {
            if ($_SERVER["PHP_SELF"] != '/eWnioski/php/index.php' AND ( isset($_POST['login']) )) {
                header('Location: index.php');
                exit;
            }
        } else {
            if ($_SERVER["PHP_SELF"] != '/eWnioski/php/login.php') {
                header('Location: login.php');
                exit;
            }
        }
    }
    /**
     * Sprawdzenie czy użytkownik jest zalogowany
     */
    function CzyZalogowanyDotyk() {

        if (isset($_SESSION['ewnioski_login'])) {
            if ($_SERVER["PHP_SELF"] != '/eWnioski/php/index.php' AND ( isset($_POST['login']) )) {
                header('Location: index.php');
                exit;
            }
        } else {
            if ($_SERVER["PHP_SELF"] != '/eWnioski/php/login_dotyk.php') {
                header('Location: login_dotyk.php');
                exit;
            }
        }
    }

    /**
     * Zmiana hasła
     */
    function ZmianaHasla() {
        if ($_POST['zmiana_login'] == '' OR $_POST['zmiana_stare_haslo'] == '' OR $_POST['zmiana_nowe_haslo'] == '' OR $_POST['zmiana_nowe_haslo_powtorz'] == '') {
            ?>
            <table align="center">
                <tr>
                    <td align="center">
                        <font color="#aa0000"><h3>Wystąpił problem ze zmianą hasła</h3></font>
                        <?php
                        if ($_POST['zmiana_login'] == '')
                            echo '<b>Pole login nie zostało uzupełnione</b><br/>';
                        ?>
                        <?php
                        if ($_POST['zmiana_stare_haslo'] == '')
                            echo '<b>Pole stare hasło nie zostało uzupełnione</b><br/>';
                        ?>
                        <?php
                        if ($_POST['zmiana_nowe_haslo'] == '')
                            echo '<b>Pole nowe hasło nie zostało uzupełnione</b><br/>';
                        ?>
                        <?php
                        if ($_POST['zmiana_nowe_haslo_powtorz'] == '')
                            echo '<b>Pole powtórz nowe hasło nie zostało uzupełnione</b><br/>'; ?>
                    </td>
                </tr>
            </table>
            <?php
        }
        else {
            $polaczenie = $this->MysqliConnect();
            $zapytanie = "SELECT * FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . trim($_POST['zmiana_login']) . "'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_POST['zmiana_login']);
            $wiersz = mysqli_fetch_array($wykonaj);
            if ($wiersz['numer_ewidencyjny'] == trim($_POST['zmiana_login']) AND $wiersz['haslo_urlopy'] == trim($_POST['zmiana_stare_haslo'])
                AND trim($_POST['zmiana_nowe_haslo']) == trim($_POST['zmiana_nowe_haslo_powtorz'])) {
                $zapytanie = "UPDATE ewidencja_szkolen.osoby SET haslo_urlopy='" . $_POST['zmiana_nowe_haslo'] . "' WHERE id='" . $wiersz['id'] . "'";
                $wykonaj = mysqli_query($polaczenie, $zapytanie);
                $this->BladZapytania($polaczenie, $zapytanie, $_POST['zmiana_login']);
                $this->ZapiszLog('Zapytanie; ' . $zapytanie, $_POST['zmiana_login']);
                $wykonaj = mysqli_query($polaczenie, $zapytanie);
                ?>
                <h3><font color="#00aa00">Hasło zostało zmienione.</font></h3>
                <?php
            } else {
                ?>
                <font color="#aa0000"><h3>Wystapiły problemy ze zmianą hasła</h3></font><br/>
                <b>Podany login nie zgadza się z hasłem.</b>
                <?php
            }
        }
    }

    /**
     *  Tworzenie połączenia do bazy danych
     * @return string Dane do połączenia z bazą poprzez mysqli
     */
    function MysqliConnect() {
        $db = mysqli_connect('localhost', 'user', 'dummy_pass', 'ewnioski_test') or die('Brak połączenia z serwerem MySQL.');
        mysqli_query($db, "SET CHARSET utf8") OR DIE(mysqli_error($db));
        mysqli_query($db, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'") OR DIE(mysqli_error($db));
        return $db;
    }

    /**
     *  Komunikat o błędzie zapytania do bazy danych MySQL
     * @param int $polaczenie Id połączenia do bazy
     * @param string $zapytanie Zapytanie do bazy
     * @param string $login Login użytkownika który wykonał zapytanie
     * @return string Komunikat o błędzie połączenia z bazą
     */
    function BladZapytania($polaczenie, $zapytanie, $login) {
        if (mysqli_error($polaczenie)) {
            $confW = $this->ParseIniE("../conf/confe.ini");
            $this->WyslijMailBladSQL($confW['MailProgramista'], $zapytanie, $login, mysqli_error($polaczenie));
            ?>
            <font color="#aa0000"><h3>Błąd z zapytaniem  </h3><br/><?php echo $zapytanie; ?><br/> informacja zostałą już wysłana do działu IT. Jak problem będzie się powtarzał skontaktuj się z działem IT.
                <?php echo mysqli_error($polaczenie); ?></font>
            <?php
            DIE;
        }
    }
    /**
     *  Komunikat o błędzie zapytania do bazy danych MySQL
     * @param int $polaczenie Id połączenia do bazy
     * @param string $zapytanie Zapytanie do bazy
     * @param string $login Login użytkownika który wykonał zapytanie
     * @return string Komunikat o błędzie połączenia z bazą
     */
    function BladZapytaniaMSSQL($polaczenie, $zapytanie, $login) {
        $blad='';
        $code='';
        $wiadomosc='';

        if( $polaczenie === false ) {
            $blad .= "Błąd połączenia <br>";
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    if(isset($error[ 'SQLSTATE'])) {
                        $blad = $blad . $error['SQLSTATE'] . '<br>';
                        $code = $code . $error['code'] . '<br>';
                        $wiadomosc = $wiadomosc . $error['message'] . '<br>';
                    }
                    else $blad=$error;
                }
            }

        }

        $stmt = sqlsrv_query( $polaczenie, $zapytanie );
        if( $stmt === false ) {
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    if(isset($error[ 'SQLSTATE'])) {
                        $blad .= $error['SQLSTATE'];
                        $code .= $error['code'];
                        $wiadomosc .= $error['message'];
                    }
                    else $blad=$error;
                }
            }
            $blad=$blad.'<br>'.$code.'<br>'.$wiadomosc;
                $confW = $this->ParseIniE("../conf/confe.ini");
                $this->WyslijMailBladSQL($confW['MailProgramista'], $zapytanie, $login,$blad);
                ?>
                <font color="#aa0000"><h3>Błąd z zapytaniem  </h3><br/><?php echo $zapytanie; ?><br/> informacja zostałą już wysłana do działu IT. Jak problem będzie się powtarzał skontaktuj się z działem IT.
                    <?php echo $blad; ?></font>
                <?php
                DIE;
        }
    }

    /**
     *  Stopka strony
     */
    function StopkaJezyki() {
        ?>
        <center>
            <div align="center" class="stopka" >
                <img src="../img/apache.gif"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <img src="../img/mysql_icon.gif"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <img src="../img/css.gif"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <img src="../img/java_script.png"/>
            </div>
        </center>
        <?php
    }

    /**
     *  Informacje o użytkowniku i wersji programu
     */
    function PokazDaneUzytkownika() {
        if (isset($_SESSION['ewnioski_full_name'])) {
            echo   'Zalogowany:  <b>', $_SESSION['ewnioski_full_name'], '</b>&nbsp; &nbsp;&nbsp;&nbsp;';
        }
        if (isset($_SESSION['ewnioski_mail'])) {
            echo   'E-mail:  <b>', $_SESSION['ewnioski_mail'], '</b>&nbsp; &nbsp;&nbsp;&nbsp;';
        }
        if (isset($_SESSION['ewnioski_kierownik_imie'])) {
            echo  'Przełożony:  <b>', $_SESSION['ewnioski_kierownik_imie'], '</b>&nbsp;&nbsp;';
        }
        if (isset($_SESSION['ewnioski_data'])) {
            echo  'Zalogowany:  <b>', $_SESSION['ewnioski_data'], '</b>';
        }
        if (isset($_SESSION['ewnioski_czas'])) {
            echo ' <b>', $_SESSION['ewnioski_czas'], '</b>&nbsp;&nbsp;';
        } ?>
        &nbsp;&nbsp;&nbsp; <a onclick="$('#kontrola_wersji').modal();" href="#">
            <b>   Wersja programu: <font color="#00aa00"><?php
                    $wersja = @explode("\n", file_get_contents("../wersja/wersja.txt"));
                    $wersja = @explode(":", $wersja[0]);
                    if (isset($wersja[1]))
                        echo trim($wersja[1]);
                    else
                        echo "bd."
                    ?></font></b></a> <?php
        if ($_SESSION['ewnioski_zmiana_profilu']==1) {
            ?>
            <b> &nbsp; &nbsp;&nbsp; Zakład:</b>
            <form method="POST" class="inline">
                <select name="ewnioski_profil" onchange="this.parentNode.submit();" class="inline">
                    <?php
                    $polaczenie = $this->MysqliConnect();
                    $zapytanie = "SELECT * FROM profil";
                    $wykonaj = mysqli_query($polaczenie, $zapytanie);
                    $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
                    while ($dane = mysqli_fetch_array($wykonaj)) {
                        ?>
                        <option <?php if ($dane['id'] == $_SESSION['ewnioski_profil']) echo "SELECTED"; ?> value="<?php echo $dane['id']; ?>"><?php echo $dane['nazwa']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </form>
            <?php
        }

    }

    /**
     *  Funkcja zapisjąca dane do logu
     * @param string $dane Wartosc która zostanie wpisana do logu
     * @param string $uzytkownik Login uzytkownika który wpisuje dane do logu
     */
    function ZapiszLog($dane, $uzytkownik) {
        $czas = date('H:i');
        $data = date('Y-m-d');
        $plik = date('W');
        $confW = $this->ParseIniE("../conf/confe.ini");
        $sciezka = $confW['FolderLogs'];
        $ip = $this->PokazIp();
        $plik = fopen($sciezka . $plik . '.txt', "a");
        fputs($plik, $dane . ';' . ' IP: ' . $ip . '; Uzytkownik ' . $uzytkownik . '; Data ' . $data . ' ' . $czas . "\r\n");
    }
    /**
     *  Funkcja zapisjąca dane do logu
     * @param string $dane Wartosc która zostanie wpisana do logu
     * @param string $uzytkownik Login uzytkownika który wpisuje dane do logu
     */
    function ZapiszLogImport($dane, $uzytkownik) {
        $czas = date('H:i');
        $data = date('Y-m-d');
        $plik = date('W');
        $confW = $this->ParseIniE("../conf/confe.ini");
        $sciezka = $confW['FolderLogs'];
        $ip = $this->PokazIp();
        $plik = fopen($sciezka . 'Import-'.$plik . '.txt', "a");
        fputs($plik, $dane . ';' . ' IP: ' . $ip . '; Uzytkownik ' . $uzytkownik . '; Data ' . $data . ' ' . $czas . "\r\n");
    }

    /**
     *  Pokazanie adresu IP klienta
     * @return string Adres IP klienta
     */
    function PokazIp() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if(isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
            else return 'Brak IP';
        }
    }

    /**
     *  Wysłanie prostego maila
     * @param string $adresat Adres mailowy na który ma być wysłany mail
     * @param string $tresc Treść maila
     * @param string $login Osoba u której wystąpił błąd w zapytaniu
     *  @param string $blad Rodzaj błedy przy zapytaniu
     */
    function WyslijMailBladSQL($adresat, $tresc, $login, $blad) {
        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
                                <h2>eWnioski</h2><br/>
                                Mail został wygenerowany: " . date('Y-m-d H:i:s') . "<br/><br/>
                                Przez użytkownika: " . $login . "<br/>
                                <b>
                                <hr/>
                                <h2>Błąd z następującym zapytaniem <br/></h2>
                                $tresc <br/><br/>
                                $blad
                                </center>
                                </body></html>";
        $temat = 'eWnioski blad wykonania zapytania';
        $tresc = <<<KONIEC
                                $page
KONIEC;
        $naglowki = <<<KONIEC
From: ZapytaniaSQL@motherson.com
Content-Type: text/html
KONIEC;
        mail($adresat, $temat, $tresc, $naglowki);
    }

    /**
     * Wysanie maila
     * @param type $adresat Do kogo ma bywysany mail
     * @param type $temat Temat maila
     * @param type $page Treść maila
     */
    function Wyslij($adresat, $temat, $page) {

        $naglowki = <<<KONIEC
From: eWnioski.Admin@motherson.com
Content-Type: text/html                         
KONIEC;
        $tresc = <<<KONIEC
$page
KONIEC;

        $confW = $this->ParseIniE("../conf/confe.ini");
      //  $adresat=$adresat.','.$confW['MailProgramista'];
        //$tresc=$tresc.$adresat;
        //$adresat=$confW['MailProgramista'];
        $adresat.=',pkc.phpalerts@motherson.com';
        mail($adresat, $temat, $tresc, $naglowki);
    }

    /**
     *  Wyświetlenie komunikatu po zmianach w bazie danych
     */
    function WyswietlKomunikat() {
        $komunikat = $_SESSION['ewnioski_komunikat'];
        echo " $komunikat";
        unset($_SESSION['ewnioski_komunikat']);
    }

    /**
     *  Pobranie pliku konfiguracyjnego
     * @return string Zmienna zawierajaća dane konfiguracyjne
     */
    function ParseIniE() {
        $ini_path = $this->iniPathE;
        if (file_exists($ini_path)) {
            return parse_ini_file($ini_path);
        } else {
            return parse_ini_file($ini_path);
            echo "Nie odnaleziono pliku: " . $ini_path;
            exit;
        }
    }

    /**
     * Wylogowanie z programu u usunięcie zmiennych sesysjnych
     */
    function BrakujaceDane($variable) {
        global $dane;
        if ($variable == '' OR $variable == NULL) {
            ?>
            <font color="#aa0000">
                <b>bd.</b>
            </font>
            <?php
        } else {
            echo $variable;
        }
    }

    /**
     * Wylgowanie się z programu, usunięcie zmiennych sesyjnych
     */
    function Wyloguj() {
        $this->WyczyscFiltry();
        $this->WyczyscFiltryLogowanie();
        header('Location: login.php');
        exit;
    }
    function WylogujDotyk() {
        $this->WyczyscFiltry();
        $this->WyczyscFiltryLogowanie();
        header('Location: http://lora.pkc.motherson.com/Wizualizacja/php/Einstrukcje.php');
        exit;
    }


    /**
     *
     * @param type $dane
     * @return type
     */
    function PodmienPolskieLitery($dane) {
        $array1 = array('ą', 'ś', 'ż', 'ź', 'ć', 'ń', 'ł', 'ó', 'ę', ' ','Ą', 'Ś', 'Ż', 'Ź', 'Ć', 'Ń', 'Ł', 'Ó', 'Ę',);
        $array2 = array('a', 's', 'z', 'z', 'c', 'n', 'l', 'o', 'e', '','A', 'S', 'Z', 'Z', 'C', 'N', 'L', 'O', 'E',);
        for ($i = 0; $i < count($array1); $i++) {
            $dane = str_replace($array1[$i], $array2[$i], $dane);
        }
        return $dane;
    }

    /**
     * Ustawienie sortowania raportu
     * @param $nazwa_sesji Nazwa zmiennej sesyjnej
     * @param $nazwa_pola_tabela Pole po którym ma być sortowanie
     * @param $kierunek_sortowania Kierunek sortowania
     */

    function Sortowanie($nazwa_sesji, $nazwa_pola_tabela, $kierunek_sortowania)
    {
        $_SESSION[$nazwa_sesji] = trim($nazwa_pola_tabela);
        if($nazwa_pola_tabela == $_SESSION[$nazwa_sesji] AND $_SESSION[$kierunek_sortowania] == 'ASC') $_SESSION[$kierunek_sortowania] = 'DESC';
        elseif($nazwa_pola_tabela == $_SESSION[$nazwa_sesji] AND $_SESSION[$kierunek_sortowania] == 'DESC') $_SESSION[$kierunek_sortowania] = 'ASC';
    }

    /**
     * Sprawdzanie czy dany MPK jest na liscei wyjątków
     * @param $mpk Nume MPK który ma być sprawdzony
     * @param $login Login osoby kóry wykonuje zapytanie
     * @return string Zwrócony wynik z tabeli
     */
   /*
    * Nie uzywane po zmianie zasady działany przełożonego
    function SprawdzMPK($mpk,$login)
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT * FROM mpk_wyjatki WHERE mpk=\"$mpk\"  ORDER BY ID DESC";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $login);
        $wiersz = mysqli_fetch_array($wykonaj);
        if($wiersz!==null)
        {
           $dane=$wiersz['akceptacja'].';'.$wiersz['inna_osoba'].';'.$wiersz['mail'].';'.$wiersz['numer_ewidencyjny'];
           return $dane;
        }
        mysqli_close($polaczenie);

    }/*
    /**
     * Sprawdzanie czy dany numer ewidencyjny jest na liscei wyjątków
     * @param $numer Nume ewidencyjny osoby która ma być sprawdzony
     * @param $login Login osoby kóry wykonuje zapytanie
     * @return string Zwrócony wynik z tabeli
     */
    function SprawdzOsobe($numer,$login)
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT o.imie, o.nazwisko, o.email, s.numer_kierownik  
                        FROM stan_godzin s
                        LEFT JOIN ewidencja_szkolen.osoby o on s.numer_kierownik=o.numer_ewidencyjny 
                        WHERE s.numer_ewidencyjny='" . trim($numer) . "'  ORDER BY s.ID DESC LIMIT 1";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $login);
        $wiersz = mysqli_fetch_array($wykonaj);
        if($wiersz!==null)
        {
            $dane=$wiersz['imie'].' '.$wiersz['nazwisko'] .';'.$wiersz['email'].';'.$wiersz['numer_kierownik'];
            return $dane;
        }
        mysqli_close($polaczenie);

    }
    /**
     * Otwarcei pliku z przykładami
     */
    function OtwarciePliku()
    {
        $nazwa_pliku = $_POST['przyklad'];
        $dir = '../doc/';
        $fullPath = $dir . $nazwa_pliku;
        if (file_exists($fullPath)) {
            echo "Plik $fullPath istnieje";
        } else {
            echo "Plik $fullPath nie istnieje";
        }
        $ext = pathinfo($dir . $nazwa_pliku, PATHINFO_EXTENSION);
        // Must be fresh start
        if (headers_sent())
            die('Headers Sent');

        // Required for some browsers
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        // File Exists?
        if (file_exists($fullPath)) {

            // Parse Info / Get Extension
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            // $ext = strtolower($path_parts["extension"]);
            // Determine Content Type
            switch ($ext) {
                case "pdf":
                    $ctype = "application/pdf";
                    break;
                case "exe":
                    $ctype = "application/octet-stream";
                    break;
                case "zip":
                    $ctype = "application/zip";
                    break;
                case "doc":
                    $ctype = "application/msword";
                    break;
                case "docx":
                    $ctype = "application/msword";
                    break;
                case "xls":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "xlsx":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "ppt":
                    $ctype = "application/vnd.ms-powerpoint";
                    break;
                case "gif":
                    $ctype = "image/gif";
                    break;
                case "png":
                    $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg":
                    $ctype = "image/jpg";
                    break;
                default:
                    $ctype = "application/force-download";
            }

            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header("Content-Type: $ctype");
            header("Content-Disposition: attachment; filename=\"" . basename($fullPath) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $fsize);
            ob_clean();
            flush();
            readfile($fullPath);
        } else {
            die('File Not Found');
        }
    }
    /**
     * Przypomnienie hasła
     */
    function PrzypomnienieHasla() {
            $polaczenie = $this->MysqliConnect();
            $zapytanie = "SELECT email,numer_ewidencyjny, haslo_urlopy FROM ewidencja_szkolen.osoby WHERE numer_ewidencyjny='" . trim($_POST['login_przypomnienie']) . "'";
            $wykonaj = mysqli_query($polaczenie, $zapytanie);
            $this->BladZapytania($polaczenie, $zapytanie, $_POST['login_przypomnienie']);
            $wiersz = mysqli_fetch_array($wykonaj);
            ?> <table align="center">
            <tr>
                <td align="center">
                <?php
            if ($wiersz['numer_ewidencyjny'] == trim($_POST['login_przypomnienie']) AND strtoupper($wiersz['email']) == strtoupper(trim($_POST['email_przypomnienie']))) {
$this->WyslijHaslo($_POST['email_przypomnienie'], $wiersz['haslo_urlopy'])
                ?>
                <h1><font color="#00aa00">Hasło zostało wysłane na maila: <?php echo $_POST['email_przypomnienie']?>.</font></h1>
                <?php
            }
            elseif (  $wiersz['email'] == trim($_POST['email_przypomnienie']))
            {
                ?>
                <font color="#aa0000"><br><h1>Dla następującego numeru ewidencyjnego: <?php echo $_POST['login_przypomnienie']?></br> Do tego numeru ewidencyjnego jest przypisany inny adres email.<br> Wprowadzony adres to:  <?php echo $_POST['email_przypomnienie']?></h1></font><br/>
                <?php
            }
            else {
                ?>
                <font color="#aa0000"><h1>Nie znaleziono takiego numer ewidencyjnego w bazie: <?php echo $_POST['login_przypomnienie']?></h1></font><br/>
                <?php
            }
        ?>

                </td>
            </tr>
        </table><?php
        mysqli_close($polaczenie);

    }
    function WyslijHaslo($email,$haslo)
    {
        $confW = $this->ParseIniE("../conf/confe.ini");
        //$email=$email.','.$confW['MailProgramista'];
        $page = "<html><head><meta http-equiv='Content-Type' content='application/xhtml+xml; charset=UTF-8' /></head>
                                <body align='center'>
                                <center>
                                <h2>eWnioski przypomnienie hasła</h2><br/>
                                Mail został wygenerowany: " . date('Y-m-d H:i:s') . "<br/><br/>
                                Twoje hasło do eWniosków to: " . $haslo . "<br/>
                              <br/>
                                </center>
                                </body></html>";
        $temat = 'eWnioski przypomnienie hasla';
        $tresc = <<<KONIEC
                                $page
KONIEC;
        $naglowki = <<<KONIEC
From: eWnioski.Admin@motherson.com
Content-Type: text/html
KONIEC;
        mail($email, $temat, $tresc, $naglowki);
    }
    /**
     *  Komunikat o błędzie zapytania do bazy danych MySQL
     * @param int $polaczenie Id połączenia do bazy
     * @param string $zapytanie Zapytanie do bazy
     * @param string $login Login użytkownika który wykonał zapytanie
     * @return string Komunikat o błędzie połączenia z bazą
     */
    function BladZapytaniaODBC($polaczenie, $zapytanie, $login) {
        if (odbc_error($polaczenie)!='') {
            $confW = $this->ParseIniE("../conf/confe.ini");
            $blad=json_encode(odbc_error($polaczenie));
            $this->WyslijMailBladSQL($confW['MailProgramista'], $zapytanie, $login,$blad);
            ?>
            <font color="#aa0000"><h3>Błąd z następującym zapytaniem. Skontaktuj sie z administratorem.</h3><br/><?php echo $zapytanie; ?><br/>
                <?php echo $blad; ?></font>
            <?php
            DIE;
        }
    }
    function BrakDanych($text) {
        if(strlen(trim($text)) > 0) return $text;
        else return "<font color='#aa0000'><b>b.d.</b></font>";
    }
    function OtwarcieTXT($dir)
    { //otwarcie zmienionego pliku DSI

        $fullPath = $dir;
        $ext = pathinfo($dir, PATHINFO_EXTENSION);
        // Must be fresh start
        if (headers_sent())
            die('Headers Sent');

        // Required for some browsers
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        // File Exists?
        if (file_exists($fullPath)) {

            // Parse Info / Get Extension
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            // $ext = strtolower($path_parts["extension"]);
            // Determine Content Type
            switch ($ext) {
                case "pdf":
                    $ctype = "application/pdf";
                    break;
                case "exe":
                    $ctype = "application/octet-stream";
                    break;
                case "zip":
                    $ctype = "application/zip";
                    break;
                case "doc":
                    $ctype = "application/msword";
                    break;
                case "docx":
                    $ctype = "application/msword";
                    break;
                case "xls":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "xlsx":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "ppt":
                    $ctype = "application/vnd.ms-powerpoint";
                    break;
                case "txt":
                    $ctype = "application/";
                    break;
                case "gif":
                    $ctype = "image/gif";
                    break;
                case "png":
                    $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg":
                    $ctype = "image/jpg";
                    break;
                default:
                    $ctype = " application/octet-stream";
            }

            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header("Content-Type: $ctype");
            header("Content-Disposition: attachment; filename=\"" . basename($fullPath) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $fsize);


            ob_clean();
            flush();
            readfile($fullPath);
        } else {
            var_dump($fullPath);
            die('File Not Found');
        }
    }

}
?>
