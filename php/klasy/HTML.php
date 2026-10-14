<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.12.2018
 * Time: 13:10
 */

class HTML extends Common
{

    /**
     * Wyświetlenie formularza do logowania
     */
    function PoczatekStronyLogowanie()
    {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/html">
        <head>
            <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
            <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
            <link rel="stylesheet" type="text/css" href="../css/style.css"/>
            <title>eWnioski <?php echo date("Y"); ?></title>
        </head>
        <body align="center" valign="middle" class="login" onload="SetFocus('login')"   >
                       <?php
    }
     function PoczatekStronyLogowanieDotyk()
    {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/html">
        <head>
            <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
            <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
            <link rel="stylesheet" type="text/css" href="../css/style.css"/>
            <title>eWnioski <?php echo date("Y"); ?></title>
            <script type="text/javascript" src="../js/Common.js?6"></script>
               <script>


startday = new Date();
clockStart = startday.getTime();
function initStopwatch()
{
 var myTime = new Date();
        var timeNow = myTime.getTime();
        var timeDiff = timeNow - clockStart;
        this.diffSecs = timeDiff/1000;
        return(this.diffSecs);
}
function getSecs()
{
        var mySecs = initStopwatch();
        var mySecs1 = ""+mySecs;
        var $pozostalo=document.getElementById('pozostalo').value;
        $pozostalo=$pozostalo-1;
        document.getElementById('pozostalo').value=$pozostalo;
        mySecs1= mySecs1.substring(0,mySecs1.indexOf("."));
        document.getElementById('czas').innerHTML=$pozostalo;
        window.setTimeout('getSecs()',1000);
        if ($pozostalo==0) {
         window.location.href = "dotyk.php?wyloguj"
         }
}
function WyczyscLicznik()
{
  document.getElementById('pozostalo').value='60';

}
</script>
        </head>
        <body align="center" valign="middle" class="login" onload="SetFocus('przepystka');window.setTimeout('getSecs()',1)"  onclick="WyczyscLicznik()">
                       <?php
    }
    /**
     * Wyświetlenie formularza do logowania
     */
function FormularzLogowania()
{
    ?>
    <table cellspacing="0" rowspacing="0" align="center" >
        <tr>
            <td align="center" valign="middle" nowrap class="td_1">
                <font class="font5"><b>eWnioski <?php echo date('Y'); ?></b></font>
            </td>
        </tr>

        <tr>
            <td align="center" valign="middle" nowrap class="td_2">
                <table >
                    <form action="log.php" method="POST">
                        <tr>
                            <td colspan="2">
                                <b>W przypadku pierwszego logowania do programu <br>używamy swojego numeru ewidencyjnego i pustego hasła. </b><br>
                            </td>

                        </tr>
                        <tr>
                            <td>
                                <b>Numer Ewidencyjny</b>
                            </td>
                            <td>
                                <input type="text" name="login" id="login" class="border"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Hasło</b>
                            </td>
                            <td>
                                <input type="password" name="password" class="border"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Zaloguj">
                            </td>
                        </tr>
                    </form>
                    <form action="login.php" method="POST">
                        <tr>
                            <td colspan="2" align="center">
                                <input type="hidden" name="zmiana_hasla" value="zmiana_hasla">
                                <input type="submit" value="Zmiana hasła">
                            </td>
                        </tr>
                    </form>
                    <form action="login.php" method="POST">
                        <tr>
                            <td colspan="2" align="center">
                                <input type="hidden" name="przypomnienie_hasla" value="przypomnienie_hasla">
                                <input type="submit" value="Przypomnienie hasła">
                            </td>
                        </tr>
                    </form>
                </table>
            </td>
        </tr>
    </table>                      <?php
}
    /**
     * Wyświetlenie formularza do logowania na urzadzeniach dotykowych
     */
    function FormularzLogowaniaDotyk()
    {
        ?>
        <table cellspacing="0" rowspacing="0" align="center" >
            <tr>
                <td nowrap class="pkc">
                </td>
            </tr>
            <tr>
                <td align="center" valign="middle" nowrap class="td_1">
                    <font class="font5"><b>eWnioski <?php echo date('Y'); ?></b></font>
                </td>
            </tr>
  <tr>

          <td align="center"  valign="middle" id="czas1" name="czas1"  >
            <h2> Pozostało do wylogowania </h2>
        </td>
          <td align="left"  valign="middle" id="czas1" name="czas1"  >
        <h2> <div id="czas"></div></h2>
            <input type="hidden" name="pozostalo" id="pozostalo" value="60">

        </td>

    </tr>
            <tr>
                <td align="center" valign="middle" nowrap class="td_2">
                    <table >
                        <form action="log_dotyk.php" method="POST">
                            <tr>
                                <td colspan="2" align="center">
                                    <h2>Proszę zeskanować przepustkę</h2>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Numer przepustki</b>
                                </td>
                                <td>
                                    <input type="password" name="przepystka" id="przepystka" class="border"  autocomplete="off"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Zaloguj">
                                </td>
                            </tr>
                        </form>
                    </table>
                </td>
            </tr>
        </table>                      <?php
    }
    /**
     * Wyświetlenie formularza do logowania
     */
function PoczatekStronyUtworzenie($imie,$nazwisko, $numerewidencyjny,$komunikat)
{
    ?>
    <!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
        <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
        <link rel="stylesheet" type="text/css" href="../css/style.css"/>
        <title>eWnioski <?php echo date("Y"); ?></title>
    </head>
<body align="center" valign="middle" class="login" onload="SetFocus('login')">
    <table cellspacing="0" rowspacing="0" align="center" height="100%">
        <tr>
            <td nowrap class="pkc">
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"  class="td_1">
                <font class="font5"><b>Login nie ma jeszcze </br> utworzonego hasła</b></font>
            </td>
        </tr>
<tr>
    <td align="center">
        <h1>Tworzysz hasło dla: </br> <?php echo $imie.' '.$nazwisko.' - '.$numerewidencyjny;?></br><font color ="red">Proszę wpisać prawidłowy adres email firmowy!!!</font color></h1>
    </td>

</tr>
        <tr>
            <td align="center">
                <h2 > <font color="red"><?php echo $komunikat?></font></h2>
            </td>
        </tr>

        <tr>
            <td align="center" valign="middle"  class="td_2">
<?php

$email='SHADOW';
?>
                <table>
                <form action="log.php" method="POST">
                    <tr>
                        <td>
                            <b>E-mail</b>
                        </td>
                        <td>
                            <input type="email" name="email" size="30" id="email" value="<?php echo $email;?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Hasło</b>
                        </td>
                        <td>
                            <input type="password" size="30" name="utworz_haslo" id="utworz_haslo">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Powtórz hasło</b>
                        </td>
                        <td>
                            <input type="password" size="30" name="powtorz_haslo" id="powtorz_haslo">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
<input type="hidden" name="login" value="<?php echo $numerewidencyjny?>">
                    <input type="submit" value="Utwórz hasło">
                        </td>
                    </tr>
                </form>
                </table>
            </td>
        </tr>
    </table>                      <?php
}

    /**
     * Domknięcie tagów końca strony logowania
     */
    function KoniecStronyLogowanie()
    {
        ?>
        <table class="td_1" align="center">
            <tr class="td_1">
                <td class="div" align="center">
                    <div class="mysql">
                    </div>
                    <?php $this->Opracowal(); ?>
                </td>
            </tr>
        </table>
        </body>
        </html>
        <?php
    }

    /**
     * Informacja o dacie programu i programiście
     */
    function Opracowal()
    {
        ?>
        <font class="font4"><b>Opracował Jakub Duda 2019 r.</b></font>
        <?php
    }

    /**
     * Komunikat o nieprawidłowym loginie
     */
    function NieprawidlowyLogin()
    {
        if($_GET['fail']==1)
            $komunikat=' Wpisane złe dane logowania';
        if($_GET['fail']==2)
            $komunikat='Nie znaleziono takiego numeru ewidencyjnego w bazie';
        if($_GET['fail']==3)
            $komunikat='Nie znaleziono aktywnej karty z takim numerem';
        if($_GET['fail']==4)
            $komunikat='Użytkownik tej karty nie ma przypisanego przełozonego. Zgłoś się do działu HR';
        if($_GET['fail']==5)
            $komunikat='Użytkownik już nie pracuje w PKC';
        ?>
        <table class="bg_red warning" align="center">
            <tr>
                <td align="center">
                    <font class="font_warning">
                       <?php echo $komunikat;?>
                    </font>
                </td>
            </tr>
        </table>
        <?php
    }
    function ZlaPrzegladarka()
    {
        ?>
        <br/>
        <br/>
        <div align='center'><h1><b>Nieprawidłowa przeglądarka!</b></h1></div><div align='center'><h1><b>Przeglądarka Internet Explorer, Microsoft Edge i Safari nie są wspierane przez tą aplikację</b></h1></div>
        <div align="center"><img src="../img/633.png" /></div>
        <div align='center'><h1><b>Proszę użyć jednej z poniższych przeglądarek:</b></h1></div>
        <table align="center" cellspacing="30">
            <tr>
                <td align="center">
                    <a href="https://www.mozilla.org/pl/firefox/new/" target="_blank">
                        <img src="../img/firefox.png"><br/>
                        <h3>Mozilla Firefox</h3>
                    </a>
                </td>
                <td align="center">
                    <a href="https://www.google.pl/chrome/browser/desktop/index.html" target="_blank">
                        <img src="..//img/chrome.png"><br/>
                        <h3>Google Chrome</h3>
                    </a>
                </td>
                <td align="center">
                    <a href="http://www.opera.com/pl/download" target="_blank">
                        <img src="../img/opera.png"><br/>
                        <h3>Opera</h3>
                    </a>
                </td>
            </tr>
        </table>
        <?php
    }
    function StaraPrzegladarka($wersja)
    {
        ?>
        <br/>
        <br/>
        <div align='center'><h1><b>Stara wersja przegladarki!</b></h1></div><div align='center'><h1><b>Program wymaga wersji Firefox conajmniej 57 natomaist ty masz <?php echo $wersja?> </b></h1></div>
        <div align="center"><img src="../img/firefox.png" /></div>
        <div align='center'><h1><b>Proszę zaktualizować przeglądarke:</b></h1></div>
        <?php
    }

    /**
     * Formularz zmiany hasła
     */
    function FormularzZmianyhasla()
    {
        ?>


        <table border="0" align="center" cellspan="2">
            <tr>
                <hr/>
                <td align="left" class="padding_10_all" colspan="2">
                    <form method="POST">
                        <div><h3>Zmiana hasła</h3></div>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Login</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="text" name="zmiana_login" class="border"/>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Stare</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="password" name="zmiana_stare_haslo" class="border"/>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Nowe hasło</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="password" name="zmiana_nowe_haslo" class="border"/>
                </td>
            </tr>
            <tr>
                <td align="left" class="padding_10_all">
                    <b>Powtórz nowe hasło</b>
                </td>
                <td align="left" class="padding_10_all">
                    <input type="password" name="zmiana_nowe_haslo_powtorz" class="border"/>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" name="zmiana" value="Zmień hasło"/>
                    </form>
                </td>
        </table>
        <?php
    }

    /**
     * Początek strony, nagłówki, menu
     */
    function PoczatekStrony()
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" type="text/css" href="../css/style.css?rand=5"/>
            <link type='text/css' href='../css/basic.css' rel='stylesheet' media='screen'/>
            <link rel="stylesheet" type="text/css" href="../css/jquery-ui.css"/>
            <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8"/>
            <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
            <script type="text/javascript" src="../css/chrome.js"></script>
            <script type="text/javascript" src="../js/jquery.js"></script>
            <script type="text/javascript" src="../js/ajax.js"></script>
            <script type="text/javascript" src="../js/Common.js?rand=<?php echo rand(1, 100000000); ?>"></script>
            <script type="text/javascript" src="../js/jquery.simplemodal.js"></script>
            <script type="text/javascript" src="../js/jquery_1.10.1.js"></script>
            <script type="text/javascript" src="../js/jquery-ui-1.10.3.custom.js"></script>

              <script type="text/javascript" src="../css/scw.js?2"></script>
            <!--  <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
<script type="text/javascript" src="../js/jquery-1.2.1.pack.js"></script>
            <script>
                function AutopodpowiadanieSymfonia(id) {
                    $("#" + id).autocomplete("SprawdzenieSymfonia.php?klient", {
                        width: 600,
                        matchContains: false,
                        mustMatch: false,
                        minChars: 1,
                        multiple: false,
                        // highlight: true,
                        multipleSeparator: ",",
                        selectFirst: false
                    });
                }s


            </script>-->

        </head>
        <body class="body"   onload="$('#submenu').hide();">
        <title>eWnioski <?php echo date('Y'); ?></title>
        <table class="table"  align="center" cellspacing="0" cellpadding="0" >
     <tr>
            <td align="center" valign="middle" class="middle_menu bottom font" colspan="7">
                &nbsp;
            </td>
        </tr>

        <tr>
            <td class="bottom menu_1" colspan="3">
            </td>
            <td  class="bottom menu_1" align="center">&nbsp;
              <img src="../img/pkcmale.png?1" />
            </td>
            <td class="bottom menu_1" colspan="3">&nbsp;
            </td>
        </tr>

        <tr>
            <td align="center" valign="middle" class="middle_menu bottom font" colspan="7">&nbsp;
                <?php
                $this->PokazDaneUzytkownika();
                ?>
            </td>
        </tr>
        <tr>
            <td class="men" colspan="7">
                <?php
                include 'menu.php';
                ?>
            </td>
        </tr>
        <tr heigh="10%">
            <td align="center"  valign="middle" id="kom" colspan="7" ><?php
                if (isset($_SESSION['ezla_komunikat'])) {
                    $this->WyswietlKomunikat();
                }
                ?>
            </td>
        </tr>

        <?php
    }
    /**
     * Początek strony, nagłówki, menu
     */
function PoczatekStronyDotyk()
{
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/style.css?rand=1"/>
        <link type='text/css' href='../css/basic.css' rel='stylesheet' media='screen'/>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8"/>
        <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
        <script type="text/javascript" src="../css/chrome.js"></script>
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" src="../js/ajax.js"></script>
        <script type="text/javascript" src="../js/Common.js?rand=<?php echo rand(1, 100000000); ?>"></script>
        <script type="text/javascript" src="../js/jquery.simplemodal.js"></script>
        <script type="text/javascript" src="../css/scwdotyk.js"></script>
        <link rel="stylesheet" href="../css/jsKeyboard.css" type="text/css" media="screen"/>
        <script>


startday = new Date();
clockStart = startday.getTime();
function initStopwatch()
{
 var myTime = new Date();
        var timeNow = myTime.getTime();
        var timeDiff = timeNow - clockStart;
        this.diffSecs = timeDiff/1000;
        return(this.diffSecs);
}
function getSecs()
{
        var mySecs = initStopwatch();
        var mySecs1 = ""+mySecs;
        var $pozostalo=document.getElementById('pozostalo').value;
        $pozostalo=$pozostalo-1;
        document.getElementById('pozostalo').value=$pozostalo;
        mySecs1= mySecs1.substring(0,mySecs1.indexOf("."));
        document.getElementById('czas').innerHTML=$pozostalo;
        window.setTimeout('getSecs()',1000);
        if ($pozostalo==0) {
         window.location.href = "dotyk.php?wyloguj"
         }
}
function WyczyscLicznik()
{
  document.getElementById('pozostalo').value='60';

}
</script>
    </head>
<body class="body"  onLoad="window.setTimeout('getSecs()',1)" onclick="WyczyscLicznik()">
    <title>eWnioski <?php echo date('Y'); ?></title>
<table class="table"  align="center" cellspacing="0" cellpadding="0" >
    <tr>
        <td align="center" valign="middle" class="middle_menu bottom font" colspan="6">
            &nbsp;
        </td>
    </tr>

    <tr align="center">
        <td class="bottom menu_1" colspan="2">
        </td>

        <td class="bottom menu_1" colspan="2">&nbsp;
          <img src="../img/pkcmale.png" />
        </td>

        <td class="bottom menu_1" colspan="2">
        </td>

    </tr>

    <tr>
        <td align="center" valign="middle" class="middle_menu bottom font" colspan="6">&nbsp;
            <?php
            $czas=60;
            if(isset($_GET['czas']))$czas=10;
            $this->PokazDaneUzytkownika();
            ?>
        </td>
    </tr>
    <tr>
    <td class="mendotyklewa">
        <a href="dotyk.php?dodaj_urlop"><img src="../img/dodajurlop.png"/></a>
    </td>
    <td class="mendotyk">
        <a href="dotyk.php?dodaj_zwolnienie"><img src="../img/dodajzwolnienie.png"/></a>
    </td>
    <td class="mendotyk">
        <a href="dotyk.php?raport_urlopow"><img src="../img/raporturlopow.png" /></a>
    </td>
    <td class="mendotyk">
        <a href="dotyk.php?raport_zwolnien"><img src="../img/raportzwolnien.png" /></a>
    </td>
    <td class="mendotyk">
        <a href="dotyk.php?stan_godzin"><img src="../img/stannadgodzin.png" /></a>
    </td>
  <td class="mendotyk">
        <a href="dotyk.php?wyloguj"><img src="../img/wyloguj.png" /></a>
        </td>
</tr>
    <tr>
        <td align="center"  valign="middle" id="czas1" name="czas1" colspan="2" >
        </td>
          <td align="center"  valign="middle" id="czas1" name="czas1" colspan="1" >
            <h2> Pozostało do wylogowania </h2>
        </td>
          <td align="left"  valign="middle" id="czas1" name="czas1" colspan="" >
        <h2> <div id="czas"></div></h2>
            <input type="hidden" name="pozostalo" id="pozostalo" value="<?php echo $czas?>">

        </td>
          <td align="center"  valign="middle" id="czas1" name="czas1" colspan="2" >

        </td>

    </tr>




    <?php
}

    /**
     * Koniec strony głównej
     */
    function KoniecStrony()
    {
        ?>
        <tr>
            <td align="center" valign="middle" class="low_menu_4 up font" colspan="7">
                <div class="hidden" id="kontrola_wersji"
                     style="color: #000000;"><?php echo str_replace("\n", "<br/>", file_get_contents("../wersja/wersja.txt")); ?></div>
            </td>
        </tr>
        </table>
        <br/>
        <?php
        $this->StopkaJezyki();
        ?>
        </body>
        </html><?php
    }

    /**
     * Koniec strony głównej
     */
function KoniecStronyDotyk()
{
    ?>
</table>
    </tr>
    <tr>
        <td align="center" valign="middle" class="low_menu_4 up font" colspan="7">
            <div class="hidden" id="kontrola_wersji"
                 style="color: #000000;"><?php echo str_replace("\n", "<br/>", file_get_contents("../wersja/wersja.txt")); ?></div>
        </td>
    </tr>
</table>
    <br/>
<?php
$this->StopkaJezyki();
?>
</body>
</html><?php
}
    /**
     * Koniec strony głównej
     */
    function KoniecStronyRaporty()
    {
        ?>

        <tr>
            <td align="center" valign="middle" class="low_menu_4 up font" colspan="27">
                <div class="hidden" id="kontrola_wersji"
                     style="color: #000000;"><?php echo str_replace("\n", "<br/>", file_get_contents("../wersja/wersja.txt")); ?></div>
            </td>
        </tr>
        </table>
        <br/>
        <?php
        $this->StopkaJezyki();
        ?>
        </body>
        </html><?php
    }

    /**
     * Logo Document Flow
     */
    function LogoW()
    {
        ?>
        <tr>
            <td align="center" valign="middle" class="text font2 bottom" colspan="7">
                <?php
                if (isset($_SESSION['ewnioski_komunikat'])) {
                    $this->WyswietlKomunikat();
                }
                ?>
                <h2>eWnioski <?php echo date('Y'); ?> Strona główna programu</h2>
                <img src="../img/pkc.png?rand=2" class="border"/>
            </td>
        </tr>
        <?php
    }
     /**
     * Logo Document Flow
     */
    function LogoWDotyk()
    {
        ?>
        <tr>
            <td align="center" valign="middle" class="text font2 bottom" colspan="7">
                <?php
                if (isset($_SESSION['ewnioski_komunikat'])) {
                    $this->WyswietlKomunikat();
                }
                ?>
                <h1>UWAGA !!!</h1>
                <h1>Wprowadzony przez ciebie wniosek nie oznacza jego akceptacji. <br> Skontaktuj się z przełożonym w celu potwierdzenia udzielenia urlopu.</h1>
                <h3>W przypadku braku akceptacji przełożonego zostanie wprowadzona <br> </h3><h2><font color="red">nieobecność nieusprawiedliwiona.</font></h2>
                <img src="../img/pkc.png" class="border"/>
            </td>
        </tr>
        <?php
    }
    function Instrukcja()
    {

?>
<tr  height="90%">
    <td align="center" valign="middle" class="text2 font2 bottom" colspan="7" >
        <iframe
            src="../doc/instrukcja_ewnioski.pdf#toolbar=0&amp;statusbar=0&amp;messages=0&amp;navpanes=0&amp;zoom=200,0,1000&amp;page=0jheight=900"
            scrolling="no" width="90%" height="900">
    </iframe>
    </td>
</tr>
        <?php
    }

function FormularzPrzypomnieniaHasla()
{
    ?>


    <table border="0" align="center" cellspan="2">
        <tr>
            <hr/>
            <td align="center" class="padding_10_all" colspan="2">
                <form method="POST">
                    <div><h3>Przypomnienie hasła</h3></div>
            </td>
        </tr>
        <tr>
            <td align="left" class="padding_10_all">
                <b>Login(numer ewidencyjny) podany przy rejestracji</b>
            </td>
            <td align="left" class="padding_10_all">
                <input type="text" name="login_przypomnienie" size="30"  class="border"/>
            </td>
        </tr>
        <tr>
            <td align="left" class="padding_10_all">
                <b>E-mail podany przy rejestracji</b>
            </td>
            <td align="left" class="padding_10_all">
                <input type="email" name="email_przypomnienie" size="30" class="border"/>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" name="przypomnij" value="Przypomnij hasło"/>
                </form>
            </td>
    </table>
    <?php
}
}
