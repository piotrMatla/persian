<?php
require_once dirname(__DIR__) . '/php/bootstrap.php';
$a = new Nadgodziny();
$a->UstawieniaStandardowe();
$a->ZmienStatusKierownik($_POST['id'], $_POST['status']);
?>