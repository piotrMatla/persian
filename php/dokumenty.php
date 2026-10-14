<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 11.05.2023
 * Time: 11:06
 */
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__);
spl_autoload_register(function ($class) {
    foreach(explode(PATH_SEPARATOR,get_include_path()) as $path) {
        if(file_exists($path."/klasy/".$class.".php")) require_once($path."/klasy/".$class.".php");
    }
});
$d = new Dokumenty;
$a = new Common;
$h = new HTML;

$f = new Filtrowanie;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();
if (isset($_POST['wyczysc_filtry'])) { // Usunięcie zmiennych sesyjnych z filtró
    $f->WyczyscFiltry();
}
if(isset($_POST['zalacznik']))
{
    $d->OtwarcieZalacznika($_POST['zalacznik']);
}
$f->UstawienieSortowaniaFiltrow();
$h->PoczatekStrony();
?><tr>
    <td colspan="7" align="center"><?php
        $d->PierwszyWierszTablica();//
        $d->DanePliki();
        ?>
    </td>
    </tr><?php
$h->KoniecStronyRaporty();