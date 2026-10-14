<?php
date_default_timezone_set('Europe/Warsaw');
function __autoload($x) {
    require_once("klasy/$x.php");
}
$a = new Common();
$polaczenie = $a->MysqliConnect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
<head>
    <link rel="stylesheet" type="text/css" href="../css/style.css" />
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <meta http-equiv="Page-Enter" content="RevealTrans(Duration=1,Transition=23)"/>
    <script type="text/javascript" src="../css/chrome.js"></script>
    <link type="text/css" href="../css/calendar.css" rel="stylesheet" />
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/autocomplete.js"></script>
    <script type="text/javascript" src="../css/scw.js"></script>
    <title>Oddelegowania pracowników</title>
</head>
<body class="body">
<?php
if(is_numeric(base64_decode($_GET['formularz']))) {
    $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_kierownik='tak', data_akceptacji_kierownik=now() WHERE numer_formularza='".base64_decode($_GET['formularz'])."'";
    $wykonaj = mysqli_query($polaczenie, $zapytanie);
    if(mysqli_affected_rows($polaczenie) > 0) { ?>
        <div align="center" class="green border">Dane zostały zaakcepowane</div>
    <?php }
    else { ?>
        <div align="center" class="red border">Dane nie zostały zaakcepowane<br/><?php echo mysqli_error($polaczenie); ?></div>
    <?php }
}
else { ?>
    <div align="center" class="red border">Błąd: Numer formularza jest niepoprawny.</div>
<?php } ?>
</body>
</html>