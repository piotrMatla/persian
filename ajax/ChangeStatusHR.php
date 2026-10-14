<?php
function __autoload($x)
{
    require_once("../php/klasy/$x.php");
}
$a = new Nadgodziny();
$a->UstawieniaStandardowe();

$a->ZmienStatusHr($_POST['id'], $_POST['status']);
?>