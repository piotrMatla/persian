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
     */
    function UrlopPDF()
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT w.data_wpisania, w.imie_nazwisko, w.numer_ewidencyjny, w.poczatek_wolnego, w.koniec_wolnego, w.imie_dziecka, w.data_urodzenia_dziecka, k.tekst, k.id, w.data_odrobienia FROM `wniosek` w
LEFT JOIN  kategoria k on w.id_kategorii=k.id
 where w.id='" . (int)$_POST['wydruk_urlop'] . "'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $dane = mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        if ($dane === null) {
            die('Nie znaleziono wniosku.');
        }
        $poczatek = $dane[3];
        $koniec = $poczatek == $dane[4] ? '' : ' do ' . $dane[4];
        if ($dane[8] == 5) {
            $tekst = $dane[7] . "\n" . "  Imię i nazwisko dziecka " . $dane[5] . ' data urodzenia ' . $dane[6] . ' w terminie: ' . $poczatek . $koniec;
        } else if ($dane[8] == 6) {
            $tekst = $dane[7] . " dnia wolnego " . $poczatek . $koniec . "\n Za odpracowane w terminie " . $dane[9];
        } else {
            $tekst = $dane[7] . ' w terminie: ' . $poczatek . $koniec;
        }
        if (($_POST['wydruk_przyczyna'] ?? '') != '') {
            $tekst = $tekst . ' z powodu ' . $_POST['wydruk_przyczyna'];
        }
        if (($_POST['wydruk_stopien'] ?? '') != '') {
            $tekst = $tekst . ' na członka rodziny ' . $_POST['wydruk_stopien'];
        }
        if (($_POST['wydruk_adres'] ?? '') != '') {
            $tekst = $tekst . ' na ' . $dane[5] . ' zamieszkała(y) pod adresem ' . $_POST['wydruk_adres'];
        }

        $this->WydrukWniosku($dane[1], $dane[0], $dane[2], "             Prosze o udzielenie " . $tekst, 'urlop.pdf');
    }

    /**
     * Generowanei wydruku zwolnienia
     */
    function ZwolnieniePDF()
    {
        $polaczenie = $this->MysqliConnect();
        $zapytanie = "SELECT z.data_wpisania, z.imie_nazwisko, z.numer_ewidencyjny, z.liczba_godzin, z.data_zwolnienia, z.data_odpracowania, z.przedzial_czasowy, z.poczatek_pracy, z.id_kategorii
FROM zwolnienie z
LEFT JOIN  kategoria k on z.id_kategorii=k.id
 where z.id='" . (int)$_POST['wydruk_zwolnienie'] . "'";
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        $dane = mysqli_fetch_row($wykonaj);
        mysqli_close($polaczenie);
        if ($dane === null) {
            die('Nie znaleziono wniosku.');
        }
        [, , , $liczbagodzin, $datazwolnienia, $dataodpracowania, $przedzialczasowy, $poczatekpracy] = $dane;

        $tekst = '';
        if ($dane[8] == 8) {
            $tekst = "  Proszę o zwolnienie mnie z $liczbagodzin godzin w dniu $datazwolnienia. Za odpracowane godziny w terminie $dataodpracowania";
        } else if ($dane[8] == 9) {
            $tekst = "  Proszę o zmianę czasu pracy w dniu $przedzialczasowy od godziny $poczatekpracy. Godziny wynikajace z przekroczenia czasu dobowego prosze zaliczyć jako odpracowane z następnej doby.";
        }

        $this->WydrukWniosku($dane[1], $dane[0], $dane[2], "             " . $tekst, 'wniosek.pdf');
    }

    /**
     * Tekst w kodowaniu czcionki PDF (ISO-8859-2)
     */
    private function Tekst($tekst): string
    {
        $wynik = @iconv("UTF-8", "ISO-8859-2", (string)$tekst);
        if ($wynik === false) {
            // znaki spoza ISO-8859-2 (np. cudzysłowy z Worda) zamieniamy na najbliższe odpowiedniki
            $wynik = @iconv("UTF-8", "ISO-8859-2//TRANSLIT", (string)$tekst);
        }
        return $wynik === false ? '' : $wynik;
    }

    /**
     * Wydruk wniosku z miejscem na podpis pracownika i przełożonego
     * @param string $imienazwisko Imię i nazwisko pracownika
     * @param string $dataWpisania Data wpisania wniosku
     * @param string $numer Numer ewidencyjny
     * @param string $tresc Treść wniosku
     * @param string $nazwaPliku Nazwa pliku PDF
     */
    private function WydrukWniosku($imienazwisko, $dataWpisania, $numer, string $tresc, string $nazwaPliku): never
    {
        if (!class_exists('FPDF')) {
            die('Brak biblioteki FPDF. Uruchom "composer install" w katalogu programu.');
        }
        $pdf = new FPDF();
        $pdf->AddPage('P', 'A4');    //dodaje nową stronę do dokumentu
        $pdf->SetMargins(5, 5, 5);  //ustawia marginesy dla dokumentu
        $pdf->AddFont('arial', '', 'arial.json', EWNIOSKI_ROOT . '/php/fonts');  // Arial z polskimi znakami (ISO-8859-2)
        $pdf->SetFont('arial', '', 12);
        $pdf->SetXY(20, 14);
        $pdf->Cell(15, 5, $this->Tekst($imienazwisko), 0, 0, 'L');
        $pdf->SetXY(20, 20);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(20, 5, $this->Tekst("Nazwisko i imię pracownika"), 0, 0, 'L');
        $pdf->SetX(150);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, $this->Tekst("Starachowice: " . date("d.m.Y", strtotime((string)$dataWpisania))), 0, 1, 'L');
        $pdf->SetXY(20, 20);
        $pdf->SetFont('arial', '', 12);
        $pdf->Cell(15, 25, $this->Tekst($numer), 0, 0, 'L');
        $pdf->SetXY(20, 35);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell(15, 5, $this->Tekst("Nr ewidencyjny"), 0, 1, 'L');
        $pdf->SetXY(90, 70);
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(15, 5, $this->Tekst("Wniosek"), 0, 1, 'L');
        $pdf->SetFont('arial', '', 12);
        $pdf->SetXY(20, 80);
        $pdf->MultiCell(170, 10, $this->Tekst($tresc), 0, 'L');
        $pdf->SetFont('arial', '', 7);
        $pdf->SetXY(130, 120);
        $pdf->Cell(15, 5, $this->Tekst("........................................................."), 0, 0, 'L');
        $pdf->SetXY(136, 123);
        $pdf->Cell(15, 5, $this->Tekst("podpis pracownika"), 0, 0, 'L');
        $pdf->SetXY(20, 130);
        $pdf->Cell(15, 5, $this->Tekst("........................................................."), 0, 0, 'L');
        $pdf->SetXY(21, 133);
        $pdf->Cell(15, 5, $this->Tekst("podpis bezpośredniego przełożonego"), 0, 0, 'L');

        $pdf->SetCompression(true);  //włącza kompresję dokumentu
        $pdf->SetDisplayMode(100);  //domyślne powiększenie dokumentu w przeglądarce
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $pdf->Output('I', $nazwaPliku);
        exit;
    }
}
