<?php
function __autoload($x) {
    require_once("klasy/$x.php");
}
$a = new Nadgodziny;
$a->UstawieniaStandardowe();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
<head>
    <link rel="stylesheet" type="text/css" href="../css/style.css" />
    <link rel="stylesheet" type="text/css" href="../css/jquery-ui.css" />
    <script type="text/javascript" src="../css/chrome.js"></script>
    <script type="text/javascript" src="../js/jquery_1.10.1.js"></script>
    <script type="text/javascript" src="../js/jquery-ui-1.10.3.custom.js"></script>
    <script type="text/javascript" src="../css/scw.js"></script>
    <script type="text/javascript" src="../js/Common.js"></script>
    <title>Edycja nadgodzin</title>
</head>
<body class="body">
<?php
$a->FormularzPoprawyDanych($_GET['id']);
if(isset($_POST['data_wykonywania_nadgodzin'])) {
    $ilosc_godzin_decimal = (int)$_POST['godziny_h'] + round((int)$_POST['godziny_m'] / 60, 4);
    $a->ZapiszZmienioneDane($_POST['id'], $ilosc_godzin_decimal, $_POST['data_wykonywania_nadgodzin']);
}
?>
</body>
</html>