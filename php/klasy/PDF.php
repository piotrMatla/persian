<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 10.12.2018
 * Time: 11:35
 */

/**
 * Klasa odpowiedzialan za generowanie PDF-ów
 */
class PDF extends Common
{
    /**
     * Generowanei wydruku urlopu
     *
     */
    function UrlopPDF()
    {
         $polaczenie = $this->MysqliConnect();
        require('/home/lora/public_html/FPDF/fpdf.php');
        $pdf = new FPDF();
        $zapytanie="SELECT w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego, w.koniec_wolnego, w.imie_dziecka, w.data_urodzenia_dziecka, k.tekst, k.id, w.data_odrobienia FROM `wniosek` w
LEFT JOIN  kategoria k on w.id_kategorii=k.id
 where w.id='".$_POST['wydruk_urlop']."'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $dane=mysqli_fetch_row($wykonaj);
        $imienazwisko=$dane[1];
        $datawpisania=date("d.m.Y",strtotime($dane[0]));
        $numer=$dane[2];
        $poczatek=$dane[3];
        $koniec=$dane[4];
        $imiedziecka=$dane[5];
        $dataurodzenia=$dane[6];
        $dataodpracowania=$dane[9];
        //$koniec=$koniec.'-'.$koniec;
        if($poczatek==$koniec)
        {
            $koniec='';
        }
        else {
            $koniec=' do '.$koniec;
    }
        if($dane[8]==5)
        {
            $tekst=$dane[7]."\n"."  Imię i nazwisko dziecka ".$imiedziecka.' data urodzenia '.$dataurodzenia.' w terminie: '.$poczatek.$koniec;
        }
        else if($dane[8]==6)
        {
            $tekst=$dane[7]. " dnia wolnego ".$poczatek.$koniec."\n Za odpracowane w terminie ".$dataodpracowania;
        }
        else{
            $tekst=$dane[7].' w terminie: '.$poczatek.$koniec;
        }
        if(isset($_POST['wydruk_przyczyna']))
        {
            if($_POST['wydruk_przyczyna']!='')
            $tekst=$tekst.' z powodu '.$_POST['wydruk_przyczyna'];
        }

        if(isset($_POST['wydruk_stopien']))
        {
            if($_POST['wydruk_stopien']!='')
            $tekst=$tekst.' na członka rodziny '.$_POST['wydruk_stopien'];
        }
        if(isset($_POST['wydruk_adres']))
        {
            if($_POST['wydruk_adres']!='')
                $tekst=$tekst.' na '.$imiedziecka.' zamieszkała(y) pod adresem '.$_POST['wydruk_adres'];
        }

        $pdf->Open();     //otwiera nowy dokument
        $pdf->AddPage('P', 'A4');    //dodaje nową stronę do dokumentu
        $pdf->SetMargins(5, 5, 5, 5);  //ustawia marginesy dla dokumentu
        $pdf->AddFont('arial', '', 'arial.php');  //dodaje Twoją czcionkę arialpl do dokumentu
        $pdf->SetFont('arial', '', 12);     //ustawia czcionkę arial
        $pdf->SETXY(20, 14);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", $imienazwisko), 0, 0, 'L');
        $pdf->SETXY(20, 20);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(20, 5, iconv("UTF-8", "ISO-8859-2", "Nazwisko i imię pracownika"), 0, 0, 'L');
        $pdf->SETX(150);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Starachowice: ".$datawpisania), 0, 1, 'L');
        $pdf->SETXY(20, 20);
        $pdf->SetFont('arial', '', 12);
        $pdf->Cell(15, 25, iconv("UTF-8", "ISO-8859-2", $numer), 0, 0, 'L');
        $pdf->SETXY(20, 35);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Nr ewidencyjny"), 0, 1, 'L');
        $pdf->SETXY(90, 70);
        $pdf->SetFont('arial', 'B', 12);     //ustawia czcionkę arialdsa
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Wniosek"), 0, 1, 'L');
        $pdf->SetFont('arial', '', 12);     //ustawia czcionkę arial
        $pdf->SETXy(20,80);
        $pdf->MultiCell(170, 10, iconv("UTF-8", "ISO-8859-2", "             Prosze o udzielenie ".$tekst),  0, 'L');
        $pdf->SetFont('arial', '', 7);
        $pdf->SETXY(130, 120);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "........................................................."), 0, 0, 'L');
        $pdf->SETXY(136, 123);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "podpis pracownika"), 0, 0, 'L');
        $pdf->SETXY(20, 130);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "........................................................."), 0, 0, 'L');
        $pdf->SETXY(21, 133);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "podpis bezpośredniego przełożonego"), 0, 0, 'L');

        mysqli_close($polaczenie);
        $pdf->SetCompression(true);  //włącza kompresję dokumentu
        $pdf->SetDisplayMode(100);  //domyślne powiększenie dokumentu w przeglądarce
        $pdf->Close();
        $pdf->Output('urlop.pdf', 'I');  //zamyka i generuje dokument
    }
    /**
     * Generowanei wydruku zwolnienia
     *
     */
    function ZwolnieniePDF()
    {
        $polaczenie = $this->MysqliConnect();
        require('/home/lora/public_html/FPDF/fpdf.php');
        $pdf = new FPDF();
        $zapytanie="SELECT z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.przedzial_czasowy, z.poczatek_pracy, z.id_kategorii 
FROM zwolnienie z
LEFT JOIN  kategoria k on z.id_kategorii=k.id
 where z.id='".$_POST['wydruk_zwolnienie']."'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $dane=mysqli_fetch_row($wykonaj);
        $imienazwisko=$dane[1];
        $datawpisania=date("d.m.Y",strtotime($dane[0]));
        $numer=$dane[2];
        $liczbagodzin=$dane[3];
        $datazwolnienia=$dane[4];
        $dataodpracowania=$dane[5];
        $przedzialczasowy=$dane[6];
        $poczatekpracy=$dane[7];

        if($dane[8]==8)
        {
            $tekst="  Proszę o zwolnienie mnie z $liczbagodzin godzin w dniu $datazwolnienia. Za odpracowane godziny w terminie $dataodpracowania";
        }
        else if($dane[8]==9){
            $tekst="  Proszę o zmianę czasu pracy w dniu $przedzialczasowy od godziny $poczatekpracy. Godziny wynikajace z przekroczenia czasu dobowego prosze zaliczyć jako odpracowane z następnej doby.";
        }

        $pdf->Open();     //otwiera nowy dokument
        $pdf->AddPage('P', 'A4');    //dodaje nową stronę do dokumentu
        $pdf->SetMargins(5, 5, 5, 5);  //ustawia marginesy dla dokumentu
        $pdf->AddFont('arial', '', 'arial.php');  //dodaje Twoją czcionkę arialpl do dokumentu
        $pdf->SetFont('arial', '', 12);     //ustawia czcionkę arial
        $pdf->SETXY(20, 14);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", $imienazwisko), 0, 0, 'L');
        $pdf->SETXY(20, 20);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(20, 5, iconv("UTF-8", "ISO-8859-2", "Nazwisko i imię pracownika"), 0, 0, 'L');
        $pdf->SETX(150);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Starachowice: ".$datawpisania), 0, 1, 'L');
        $pdf->SETXY(20, 20);
        $pdf->SetFont('arial', '', 12);
        $pdf->Cell(15, 25, iconv("UTF-8", "ISO-8859-2", $numer), 0, 0, 'L');
        $pdf->SETXY(20, 35);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Nr ewidencyjny"), 0, 1, 'L');
        $pdf->SETXY(90, 70);
        $pdf->SetFont('arial', 'B', 12);     //ustawia czcionkę arialdsa
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "Wniosek"), 0, 1, 'L');
        $pdf->SetFont('arial', '', 12);     //ustawia czcionkę arial
        $pdf->SETXy(20,80);
        $pdf->MultiCell(170, 10, iconv("UTF-8", "ISO-8859-2", "             ".$tekst),  0, 'L');
        $pdf->SetFont('arial', '', 7);
        $pdf->SETXY(130, 120);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "........................................................."), 0, 0, 'L');
        $pdf->SETXY(136, 123);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "podpis pracownika"), 0, 0, 'L');
        $pdf->SETXY(20, 130);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "........................................................."), 0, 0, 'L');
        $pdf->SETXY(21, 133);
        $pdf->Cell(15, 5, iconv("UTF-8", "ISO-8859-2", "podpis bezpośredniego przełożonego"), 0, 0, 'L');
        mysqli_close($polaczenie);
        $pdf->SetCompression(true);  //włącza kompresję dokumentu
        $pdf->SetDisplayMode(100);  //domyślne powiększenie dokumentu w przeglądarce
        $pdf->Close();
        $pdf->Output('wniosek.pdf', 'I');  //zamyka i generuje dokument
    }



}