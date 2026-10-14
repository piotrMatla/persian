<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 10.12.2018
 * Time: 09:15
 */

function __autoload($x) {
    require_once("klasy/$x.php");
}

$a = new Common;
$h = new HTML;
$m = new Modyfikuj;
$p = new PDF;
$a->UstawieniaStandardowe();
$a->CzyZalogowany();

$h->PoczatekStrony();
if(isset($_POST['rozpocznij_prace_zdalna']))
{
    $m->ZmianaPracaZdalna($_POST['rozpocznij_prace_zdalna']);
}
if(isset($_GET['urlop']))
{
    $m->FormularzUrlop();//Formularz wpisania wniosku urlopowego
}
if(isset($_GET['wpisz']))
{
    $m->WpiszUrlop();//Formularz wpisania wniosku urlopowego
}
if(isset($_GET['zwolnienie']))
{
    $m->FormularzZwolnienie();// Wrzucenie pliku na serwer i jego analiza
}
if(isset($_GET['wpiszzwolnienie']))
{
    $m->WpiszZwolnienie();//Formularz wpisania wniosku urlopowego
}
if(isset($_GET['zdalna']))
{
    $m->FormularzZdalna();// Wrzucenie pliku na serwer i jego analiza
}
if(isset($_GET['rozpocznij_zdalna']))
{
    $m->FormularzRozpocznijZdalna();// Wrzucenie pliku na serwer i jego analiza
}

$h->KoniecStrony();