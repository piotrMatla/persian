<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.01.2019
 * Time: 12:04
 */

class Excel extends Common
{
    function EksportSaturnXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');

        $objPHPExcel = $this->NowyArkuszXlsx("NieobUzup", ['A' => 20, 'B' => 10, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10], [
            'PESEL',
            'SymbolAbsencji',
            'DataOd',
            'DataDo',
            'DataRozliczenia',
            'Dni',
            'DniRob',
            'GodzRob',
            'NaZadanie',
            'NumerEwidencyjny',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        $licznik = 2;
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {
                if ($dane['id_status'] == 3) {
                    $zapytanieutlop = "SELECT p.PESEL,k.pozostalo, k.pozostalogodziny
                    FROM [SaturnNET].[dbo].[vPracownicy]  p
                    LEFT JOIN [SaturnNET].[dbo].[vKartotekaUrlopowa] k on p.IdPracownika=k.id
                    where p.Numer='" . $dane['numer_ewidencyjny'] . "' and (k.rok='" . $rok . "' or k.rok='2019')";
                    $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                    $wynik = odbc_fetch_array($e1);
                    $pesel = $wynik['PESEL'];
                    $pozostalo = number_format($wynik['pozostalo'], 0);
                    $pozostalogodzin = number_format($wynik['pozostalogodziny'], 0);
                    $spr = '0';
                    if ($pozostalo == 0) {
                        $dzielnik = 8;
                        $spr = '1';
                    } else {
                        $dzielnik = $pozostalogodzin / $pozostalo;
                    }
                    $datapoczatek = new DateTime($dane['poczatek_wolnego']);
                    $datakoniec = new DateTime($dane['koniec_wolnego']);
                    $poczatekwolnego = date_format($datapoczatek, 'Y-m-d') . ' 00:00:00.000';
                    $koniec_wolnego = date_format($datakoniec, 'Y-m-d') . ' 00:00:00.000';
                    $datarozliczenia = date('Y-m-d') . ' 00:00:00.000';
                    $zadanie = 0;
                    if ($dane['id_kategorii'] == 4) $zadanie = 1;
                    // if ($dane['liczba_dni_eksport'] > 0 and $dane['liczba_dni_eksport'] < 1) $dane['liczba_dni_eksport'] = 1; mkoziej rozmowa 22.05.2019
                    $eksport = $dane['liczba_dni_eksport'] * $dzielnik;
                    $objPHPExcel->setActiveSheetIndex(0);
                    if (!is_int($eksport) or $spr == '1') {
                        $this->KolorujWierszXlsx($objPHPExcel, $licznik, 'A', 'J', 'FFFF0000');
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $pesel);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['kod']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $poczatekwolnego);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $koniec_wolnego);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $datarozliczenia);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['liczba_dni_eksport']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $dane['liczba_dni_eksport']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $eksport);
                    $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $zadanie);
                    $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['numer_ewidencyjny']);

                    $zapytaniezapisz = "UPDATE wniosek SET id_status='9', login_hr='" . $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id='" . $dane['id'] . "' ";
                    $wykonajzapisz = mysqli_query($polaczenie, $zapytaniezapisz);
                    $this->BladZapytania($polaczenie, $zapytaniezapisz, $_SESSION['ewnioski_login']);
                    $this->ZapiszLog('Zapytanie;' . $zapytaniezapisz, $_SESSION['ewnioski_login']);
                    $licznik++;
                }
            }

        }

        // Praca zdalna
        $PobranePozycjeId = [];
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_praca_zdalna_excel']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport praca zdalna');
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $zapytanieutlop = "SELECT p.PESEL,k.pozostalo, k.pozostalogodziny
                    FROM [SaturnNET].[dbo].[vPracownicy]  p
                    LEFT JOIN [SaturnNET].[dbo].[vKartotekaUrlopowa] k on p.IdPracownika=k.id
                    where p.Numer='" . $dane['NumerEwidencyjny'] . "' and (k.rok='" . $rok . "' or k.rok='2019')";
                $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                $wynik = odbc_fetch_array($e1);
                $pesel = $wynik['PESEL'];

                $LiczbaDni =  Urlopy::PobierzIloscDniRoboczych($dane['DataOd'], $dane['DataDo']);
                $IloscGodzin = $LiczbaDni * 8;

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $pesel);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['SymbolAbsencji']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['DataOd']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['DataDo']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['DataRozliczenia']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $LiczbaDni);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $LiczbaDni);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $IloscGodzin);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, 0);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['NumerEwidencyjny']);
                $licznik++;
                $PobranePozycjeId[] = $dane['id'];
            }
        }

        if(count($PobranePozycjeId) > 0) {
            $zapytaniezapisz = "UPDATE zwolnienie SET id_status='9', login_hr='" . $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id IN('".implode("','", $PobranePozycjeId)."') ";
            mysqli_query($polaczenie, $zapytaniezapisz) OR DIE(mysqli_error($polaczenie));
        }

        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'NieobUzup.xlsx', 'NieobUzup');
    }

    function EksportZwolnieniaXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();

        $objPHPExcel = $this->NowyArkuszXlsx("Zwolnienia", ['A' => 30, 'B' => 20, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20, 'J' => 30, 'K' => 30, 'L' => 30, 'M' => 25, 'N' => 25, 'O' => 25], [
            'Imię i nazwisko',
            'Numer ewidencyjny',
            'Pesel',
            'Liczba godzin',
            'Data zwolnienia',
            'Data odpracowania',
            'Godzina rozpoczęcia ',
            'Przedział czasowy zmiany godzin',
            'Rodzaj',
            'Status',
            'Dane kierownika',
            'Osoba akceptująca',
            'Data akceptacji',
            'Data pobrania przez HR',
            'Data wpisania',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel_zwolnienia']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $zapytanieutlop = "SELECT p.PESEL
                FROM [SaturnNET].[dbo].[vPracownicy]  p
                where p.Numer='" . $dane['numer_ewidencyjny'] . "' ";
                $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                $wynik = odbc_fetch_array($e1);
                $pesel = $wynik['PESEL'];
                $objPHPExcel->setActiveSheetIndex(0);
                if ($dane['status_wniosku'] == 'zaakceptowany przez HR') {
                    if ($dane['data_kierownik'] == '0000-00-00 00:00:00') $dane['data_kierownik'] = '';
                    if ($dane['data_hr'] == '0000-00-00 00:00:00') $dane['data_hr'] = '';
                    if ($dane['data_zwolnienia'] == '0000-00-00') $dane['data_zwolnienia'] = '';
                    if ($dane['poczatek_pracy'] == '00:00:00') $dane['poczatek_pracy'] = '';
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['imie_nazwisko']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['numer_ewidencyjny']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $pesel);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['liczba_godzin']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['data_zwolnienia']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['data_odpracowania']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $dane['poczatek_pracy']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $dane['przedzial_czasowy']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $dane['nazwa']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, 'pobrany prze HR');
                    $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $dane['imie'] . ' ' . $dane['nazwisko']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik, $dane['login_kierownik']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik, $dane['data_kierownik']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik, $dane['data_hr']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik, $dane['data_wpisania']);
                    $zapytaniezapisz = "UPDATE zwolnienie SET id_status='9', login_hr='" . $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                    WHERE id='" . $dane['id'] . "' AND id_kategorii!='17' AND id_kategorii!='18' ";
                    $wykonajzapisz = mysqli_query($polaczenie, $zapytaniezapisz);
                    $this->BladZapytania($polaczenie, $zapytaniezapisz, $_SESSION['ewnioski_login']);
                    $this->ZapiszLog('Zapytanie;' . $zapytaniezapisz, $_SESSION['ewnioski_login']);
                    $licznik++;
                }
            }
        }

        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Zwolnienia.xlsx', 'Zwolnienia');
    }

    function EksportZwolnieniaWidokXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();

        $objPHPExcel = $this->NowyArkuszXlsx("Zwolnienia", ['A' => 30, 'B' => 20, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20, 'J' => 30, 'K' => 30, 'L' => 30, 'M' => 25, 'N' => 25, 'O' => 25], [
            'Imię i nazwisko',
            'Numer ewidencyjny',
            'Pesel',
            'Liczba godzin',
            'Data zwolnienia',
            'Data odpracowania',
            'Godzina rozpoczęcia ',
            'Przedział czasowy zmiany godzin',
            'Rodzaj',
            'Status',
            'Dane kierownika',
            'Osoba akceptująca',
            'Data akceptacji',
            'Data pobrania przez HR',
            'Data wpisania',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel_widok']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $zapytanieutlop = "SELECT p.PESEL
FROM [SaturnNET].[dbo].[vPracownicy]  p
where p.Numer='" . $dane['numer_ewidencyjny'] . "' ";
                $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                $wynik = odbc_fetch_array($e1);
                $pesel = $wynik['PESEL'];
                $objPHPExcel->setActiveSheetIndex(0);
                if ($dane['data_kierownik'] == '0000-00-00 00:00:00') $dane['data_kierownik'] = '';
                if ($dane['data_hr'] == '0000-00-00 00:00:00') $dane['data_hr'] = '';
                if ($dane['data_zwolnienia'] == '0000-00-00') $dane['data_zwolnienia'] = '';
                if ($dane['poczatek_pracy'] == '00:00:00') $dane['poczatek_pracy'] = '';
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['imie_nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $pesel);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['liczba_godzin']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['data_zwolnienia']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['data_odpracowania']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $dane['poczatek_pracy']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $dane['przedzial_czasowy']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $dane['nazwa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['status_wniosku']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $dane['imie'] . ' ' . $dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik, $dane['login_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik, $dane['data_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik, $dane['data_hr']);
                $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik, $dane['data_wpisania']);

                $licznik++;

            }

        }

        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Zwolnienia.xlsx', 'Zwolnienia');
    }
    function EksportPracaZdalnaCzasPracyXLSX()
    {

        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Czas pracy zdalnej", ['A' => 20, 'B' => 30, 'C' => 30, 'D' => 20, 'E' => 20], [
            'Numer ewidencyjny',
            'Nazwisko',
            'Imię',
            'Dzień pracy',
            'Czas pracy',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel_praca_zdalna_czas_pracy']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                [$godzina, $minuta] = $this->CzasPracyZdalnej($dane);
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['imie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, date_create((string)$dane['data_rozpoczecia'])->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $godzina.':'.$minuta);
                $licznik++;

            }

        }

        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Czas pracy zdalnej.xlsx', 'Czas pracy zdalnej');
    }
    function EksportPracaZdalnaAkcjeXLSX()
    {

        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Praca zdalna", ['A' => 20, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 20, 'F' => 20], [
            'Numer ewidencyjny',
            'Nazwisko',
            'Imię',
            'Data akcji',
            'Typ akcji',
            'Numer wpisującego',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel_praca_zdalna']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport praca zdalna');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['nazwisko_osoby']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['imie_osoby']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['data_rozpoczecia_pracy']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['nazwa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['uzytkownik_wpisujacy']);
                $licznik++;
            }

        }
        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Praca zdalna.xlsx', 'Praca zdalna');
    }
    function EksportSaturnXLSXWniosekWRCP()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');

        $objPHPExcel = $this->NowyArkuszXlsx("NieobUzup", ['A' => 20, 'B' => 10, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'K' => 20], [
            'PESEL',
            'SymbolAbsencji',
            'DataOd',
            'DataDo',
            'DataRozliczenia',
            'Dni',
            'DniRob',
            'GodzRob',
            'NaZadanie',
            'NumerEwidencyjny',
            'Błąd',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel2']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                if ($dane['id_status'] == 9) {
                    $zapytanieutlop = "SELECT p.PESEL,k.pozostalo, k.pozostalogodziny
                    FROM [SaturnNET].[dbo].[vPracownicy]  p
                    LEFT JOIN [SaturnNET].[dbo].[vKartotekaUrlopowa] k on p.IdPracownika=k.id
                    where p.Numer='" . $dane['numer_ewidencyjny'] . "' and (k.rok='" . $rok . "' or k.rok='2019')";
                    $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                    $wynik = odbc_fetch_array($e1);
                    $pesel = $wynik['PESEL'];
                    $pozostalo = number_format($wynik['pozostalo'], 0);
                    $pozostalogodzin = number_format($wynik['pozostalogodziny'], 0);
                    $spr = '0';
                    if ($pozostalo == 0) {
                        $dzielnik = 8;
                        $spr = '1';
                    } else {
                        $dzielnik = $pozostalogodzin / $pozostalo;
                    }
                    $datapoczatek = new DateTime($dane['poczatek_wolnego']);
                    $datakoniec = new DateTime($dane['koniec_wolnego']);
                    $poczatekwolnego = date_format($datapoczatek, 'Y-m-d') . ' 00:00:00.000';
                    $koniec_wolnego = date_format($datakoniec, 'Y-m-d') . ' 00:00:00.000';
                    $datarozliczenia = date('Y-m-d') . ' 00:00:00.000';
                    $zadanie = 0;
                    if ($dane['id_kategorii'] == 4) $zadanie = 1;
                    //   if ($dane['liczba_dni_eksport'] > 0 and $dane['liczba_dni_eksport'] < 1) $dane['liczba_dni_eksport'] = 1; mkoziej rozmowa 22.05.2019

                    $eksport = $dane['liczba_dni_eksport'] * $dzielnik;
                    $objPHPExcel->setActiveSheetIndex(0);
                    $blad = '';
                    $miesiacaktualny = date('m', strtotime($datarozliczenia));
                    $miesiacurlopu = date('m', strtotime($koniec_wolnego));

                    if ($pesel == '') {
                        $blad .= 'Brak PESEL ';
                        $kolor = '00a000';
                    }
                    if (!is_int($eksport)) {
                        $blad .= 'Nieprawidłowa ilość godzin ';
                        $kolor = '1f86ff ';
                    }
                    if ($miesiacaktualny != $miesiacurlopu) {
                        $blad .= 'Sprawdź miesiąc rozliczenia ';
                        $kolor = 'ff1400';
                    }

                    if ($blad!='') {
                        $this->KolorujWierszXlsx($objPHPExcel, $licznik, 'A', 'J', $kolor);
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $pesel);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['kod']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $poczatekwolnego);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $koniec_wolnego);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $datarozliczenia);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['liczba_dni_eksport']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $dane['liczba_dni_eksport']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $eksport);
                    $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $zadanie);
                    $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['numer_ewidencyjny']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $blad);
                    $licznik++;
                }
            }

        }

        // Praca zdalna
        $PobranePozycjeId = [];
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_praca_zdalna_excel']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport praca zdalna');
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $zapytanieutlop = "SELECT p.PESEL,k.pozostalo, k.pozostalogodziny
                    FROM [SaturnNET].[dbo].[vPracownicy]  p
                    LEFT JOIN [SaturnNET].[dbo].[vKartotekaUrlopowa] k on p.IdPracownika=k.id
                    where p.Numer='" . $dane['NumerEwidencyjny'] . "' and (k.rok='" . $rok . "' or k.rok='2019')";
                $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                $wynik = odbc_fetch_array($e1);
                $pesel = $wynik['PESEL'];

                $LiczbaDni =  Urlopy::PobierzIloscDniRoboczych($dane['DataOd'], $dane['DataDo']);
                $IloscGodzin = $LiczbaDni * 8;

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $pesel);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['SymbolAbsencji']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['DataOd']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['DataDo']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['DataRozliczenia']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $LiczbaDni);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $LiczbaDni);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $IloscGodzin);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, 0);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['NumerEwidencyjny']);
                $licznik++;
                $PobranePozycjeId[] = $dane['id'];
            }
        }

        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'NieobUzup.xlsx', 'NieobUzup');
    }
    function EksportEnovaXLSXWniosekWRCP()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');

        $objPHPExcel = $this->NowyArkuszXlsx("NieobUzup", ['A' => 25, 'B' => 20, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 15], [
            'Class',
            'Pracownik:Class',
            'Pracownik:Kod',
            'Definicja:Nazwa',
            'Okres.Od',
            'Okres.Do',
            'OdGodziny',
            'DoGodziny',
            'Czas',
            'Urlop.Przyczyna',
            'Pracownik:Nazwisko imie',
        ]);
        $licznik = 2;
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel2_enova']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport eWniosek Enova');
        if (mysqli_num_rows($wykonaj) > 0) {

            while ($dane = mysqli_fetch_array($wykonaj)) {
                if ($dane['typ_umowy'] !== null) {
                    if ($dane['id_status'] == 9) {

                        $spr = '0';
                        $datapoczatek = new DateTime($dane['poczatek_wolnego']);
                        $datakoniec = new DateTime($dane['koniec_wolnego']);
                        $poczatekwolnego = date_format($datapoczatek, 'Y-m-d');
                        $koniec_wolnego = date_format($datakoniec, 'Y-m-d');
                        $datarozliczenia = date('Y-m-d');
                        $zadanie ="";
                        $pracownikNazwiskoImie ="";
                        if ($dane['id_kategorii'] == 4) $zadanie = "NaŻądanie";
                        //   if ($dane['liczba_dni_eksport'] > 0 and $dane['liczba_dni_eksport'] < 1) $dane['liczba_dni_eksport'] = 1; mkoziej rozmowa 22.05.2019

                        $objPHPExcel->setActiveSheetIndex(0);
                        $blad = '';
                        $miesiacaktualny = date('m', strtotime($datarozliczenia));
                        $miesiacurlopu = date('m', strtotime($koniec_wolnego));

                        /*  if ($miesiacaktualny != $miesiacurlopu) {
                              $blad .= 'Sprawdź miesiąc rozliczenia ';
                              $kolor = 'ff1400';
                          }

                          if ($blad!='') {
                              $this->KolorujWierszXlsx($objPHPExcel, $licznik, 'A', 'F', $kolor);
                                 }*/
                        $iloscgodzinenova = '';
                        $odgodziny = '';
                        $dogodziny = '';
                        if ($dane['liczba_godzin_enova'] != 0) {
                            $iloscgodzinenova = $dane['liczba_godzin_enova'];
                            $iloscgodzinenova=abs($iloscgodzinenova);
                            $tymgodziny = $iloscgodzinenova;
                            $tym = explode('.', $iloscgodzinenova);
                            $iloscgodzinenova = $iloscgodzinenova . ':00';
                            $minuty = '30';
                            if (isset($tym[1])) {
                                $minuty = '00';
                                $tymgodziny = $tym[0] + 1;
                                $iloscgodzinenova = $tym[0] . ':30';
                            }

                            $odgodziny = '6:30';
                            $dogodziny = 6 + $tymgodziny . ':' . $minuty;
                            if ($dane['kod_enova'] == 'Urlop opiekuńczy (art 188 kp, dni)') $dane['kod_enova'] = 'Urlop opiekuńczy (art 188 kp, godz.)';
                            if ($dane['kod_enova'] == 'Zwolnienie od pracy - siła wyższa (dni)') $dane['kod_enova'] = 'Zwolnienie od pracy - siła wyższa(godz.)';
                        }

                        if (strlen($dane['numer_ewidencyjny']) == 5) $dane['numer_ewidencyjny'] = '0' . $dane['numer_ewidencyjny'];
                        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, 'NieobecnośćPracownika');
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, 'PracownikFirmy');
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['numer_ewidencyjny']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['kod_enova']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $poczatekwolnego);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $koniec_wolnego);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $odgodziny);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $dogodziny);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $iloscgodzinenova);
                        $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $zadanie);
                        $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $pracownikNazwiskoImie);

                        $licznik++;
                    }
                }
            }

        }

        // Praca zdalna
        $PobranePozycjeId = [];
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_praca_zdalna_excel']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport praca zdalna');
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $LiczbaDni =  Urlopy::PobierzIloscDniRoboczych($dane['DataOd'], $dane['DataDo']);

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, 'NieobecnośćPracownika');
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, 'PracownikFirmy');
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['NumerEwidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['SymbolAbsencji']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['poczatek_pracy_zdalnej']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['koniec_pracy_zdalnej']);
                $licznik++;
                $PobranePozycjeId[] = $dane['id'];
            }
        }

        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Enova.xlsx', 'Enova');
    }
    function EksportStanGodzin()
    {
        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Stan urlopu i nadgodzin", ['A' => 18, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 28, 'F' => 10, 'G' => 14, 'H' => 14, 'I' => 10, 'J' => 30, 'K' => 16, 'L' => 22, 'M' => 24, 'N' => 26, 'O' => 18, 'P' => 14, 'Q' => 20, 'R' => 25], [
            'Nr ewidencyjny',
            'Nazwisko',
            'Imię',
            'Mpk i grupa',
            'Kierownik',
            'UW',
            'UW - godziny',
            'UW zaległy',
            'UŻ',
            'Ilość nadgodzin nadpracowanych (limit 260)',
            'NADGODZINY',
            'Godziny odpracowane (+)',
            'Godziny do odpracowania (-)',
            'Niedopracowania (bez wniosku)',
            'Stan godzin (archiwalne)',
            'Stan na dzień',
            'Data importu',
            'Osoba importująca',
        ]);
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_excel_stan_godzin']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {

                $objPHPExcel->setActiveSheetIndex(0);

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['imie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['mpk']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['nazwisko_kierownik'] . ' ' . $dane['imie_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['stan_urlopu']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $dane['stan_urlopu_godziny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $dane['urlop_zalegly']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $dane['uz']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['rcp']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $dane['ilosc_nadgodzin']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik, $dane['ilosc_nadgodzin_plus']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik, $dane['godziny_do_odpracowania']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik, $dane['niedopracowania']);
                $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik, $dane['stan_godzin']);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('P' . $licznik,
                    ($dane['stan_na_dzien'] == '0000-00-00' ? '' : (string)$dane['stan_na_dzien']),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $licznik, $dane['data_importu']);
                $objPHPExcel->getActiveSheet()->SetCellValue('R' . $licznik, $dane['nazwisko_import'] . ' ' . $dane['imie_import']);
                $licznik++;
            }
        }

        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Stan urlopu i nadgodzin.xlsx', 'Stan godzin');
    }
    function NadgodzinyExcelExportXLSX($zapytanie)
    {
        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Ewidencja nadgodzin", ['A' => 10, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 25, 'F' => 25, 'G' => 25, 'H' => 25, 'I' => 25, 'J' => 30, 'K' => 25, 'L' => 25, 'M' => 25, 'N' => 25, 'O' => 25, 'P' => 25, 'Q' => 25, 'R' => 25, 'S' => 25, 'T' => 25, 'U' => 25, 'V' => 25, 'W' => 25, 'X' => 25, 'Y' => 25], [
            'Lp',
            'Id',
            'Numer_ewidencyjny',
            'Nazwisko',
            'Imię',
            'Ilość nadgodzin',
            'Data nadgodzin',
            'Typ wpisu',
            'Wprowadził',
            'Data odpracowania',
            'Akceptacja Kierownik',
            'Akceptacja HR',
            'Zaakceptował/a (Kierownik)',
            'Zaakceptował/a HR',
            'Data akceptacji Kierownik',
            'Data akceptacji HR',
            'Grupa',
            'Data wprowadzenia',
            'Uzasadnienie',
            'Uzasadnienie poziom II',
            'Uzasadnienie poziom III',
            'Anulowano',
            'Brygadzista notatka',
            'Kierownik notatka',
            'Hr notatka',
        ]);
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $rzad = 1;
        $this->BladZapytania($polaczenie, $zapytanie, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {

                $objPHPExcel->setActiveSheetIndex(0);
                $pracownik = explode(" ", trim($dane['pracownik']));
                if(!isset($pracownik[1])) $pracownik[1] = "";

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $rzad);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik,  $dane['id']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik,  $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik,  $pracownik[1]);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik,  $pracownik[0]);

                $_g_val = $dane['ilosc_godzin_nadliczbowych'];
                if ($_g_val !== null) {
                    $_g_h = (int)floor((float)$_g_val);
                    $_g_m = (int)round(((float)$_g_val - $_g_h) * 60);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $_g_h . ' h ' . str_pad($_g_m, 2, '0', STR_PAD_LEFT) . ' min');
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $_g_val);
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik,  $dane['data_wykonywania_nadgodzin']);

                // Typ wpisu - taka sama etykieta jak w raporcie na ekranie
                $_typ_wpisu = ($dane['typ_wpisu'] == 'odpracowanie_godzin') ? 'Odpracowanie godzin' : 'Nadgodziny';
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $_typ_wpisu);

                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik,  $dane['wprowadzil']);

                // Data odpracowania - lista dat rozdzielona przecinkami; zapis jako tekst,
                // żeby Excel nie zamienił pojedynczej daty na liczbę seryjną
                $_data_odpracowania = str_replace(',', ', ', (string)$dane['data_odpracowania']);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit(
                    'J' . $licznik,
                    $_data_odpracowania,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik,  $dane['akceptacja_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik,  $dane['akceptacja_dzial_personalny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik,  $dane['akceptacja_kierownik_user']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik,  $dane['akceptacja_dzial_personalny_user']);
                $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik,  $dane['data_akceptacji_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('P' . $licznik,  $dane['data_akceptacji_dzial_personalny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $licznik,  $dane['grupa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('R' . $licznik,  $dane['data_wprowadzenia']);
                $objPHPExcel->getActiveSheet()->SetCellValue('S' . $licznik,  $dane['uzasadnienie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('T' . $licznik,  $dane['uzasadnienie_poziom_2']);
                $objPHPExcel->getActiveSheet()->SetCellValue('U' . $licznik,  $dane['dodatkowe_uzasadnienie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('V' . $licznik,  $dane['anulowano']);
                $objPHPExcel->getActiveSheet()->SetCellValue('W' . $licznik,  $dane['brygadzista_notatka']);
                $objPHPExcel->getActiveSheet()->SetCellValue('X' . $licznik,  $dane['kierownik_notatka']);
                $objPHPExcel->getActiveSheet()->SetCellValue('Y' . $licznik,  $dane['hr_notatka']);

                $licznik++;
                $rzad++;
            }
        }

        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Ewidencja_nadgodzin.xlsx', 'Ewidencja_nadgodzin');
    }
    function RaportWejscXLSX($zapytanie)
    {
        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Stan wejść", ['A' => 10, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 25, 'F' => 25, 'G' => 25, 'H' => 25, 'I' => 25, 'J' => 25], [
            'Lp',
            'Numer ewidencyjny',
            'Imię',
            'Nazwisko',
            'Godzina wejścia',
            'Godzina wyjścia',
            'Pobyt',
            'Typ pobytu',
            'Miejsce wejścia',
            'Grupa',
        ]);
        $wykonaj = mysqli_query($polaczenie,$zapytanie);
        $rzad=1;
        $this->BladZapytania($polaczenie, $zapytanie, 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $licz=0;
                $objPHPExcel->setActiveSheetIndex(0);
                if($dane['data_wejscia']==null)
                {
                    $dane['data_wejscia']='';
                    $licz=1;
                }
                if($dane['data_wyjscia']==null)
                {
                    $dane['data_wyjscia']='';
                    $licz=1;
                }
                $dane['data_wejscia'] = datetime::createfromformat('Y-m-d H:i:s',  $dane['data_wejscia']);
                $dane['data_wyjscia'] = datetime::createfromformat('Y-m-d H:i:s',  $dane['data_wyjscia']);
                if($licz==0 )                 {

                    $diff = $dane['data_wejscia']->diff($dane['data_wyjscia']);
                    $czas=(int)$diff->format('%H');
                    if($czas<8)
                    {
                        //  $color = $this->KolorWierszaCzerwony($licznik);
                        $licz=1;
                    }
                    $czaspracy=$dane['data_wejscia']->diff($dane['data_wyjscia']);
                    $czaspracy=$czaspracy->format('%H:%i');

                }
                else
                {
                    //  $color = $this->KolorWierszaCzerwony($licznik);
                }
                if($dane['data_wyjscia']==null) $wyjscie= 'Brak odbicia';
                else $wyjscie= date_format($dane['data_wyjscia'], 'Y-m-d H:i:s');
                if($dane['data_wejscia']==null) $wejscie= 'Brak odbicia';
                else $wejscie= date_format($dane['data_wejscia'], 'Y-m-d H:i:s');
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $rzad);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik,  $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik,  $dane['imie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik,  $dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik,  $wejscie);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik,  $wyjscie);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik,  $czaspracy);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik,  $dane['typ_odbicia']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik, $dane['nazwa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['mpk']);
                $licznik++;
                $rzad++;
            }

        }

        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Stan wejść.xlsx', 'Ewidencja_nadgodzin');
    }
    function RaportUrlopyWidoczneXLSX($zapytanie)
    {

        $polaczenie = $this->MysqliConnect();

        $objPHPExcel = $this->NowyArkuszXlsx("Raport urlopów", ['A' => 10, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 30, 'G' => 30, 'H' => 30, 'I' => 30, 'J' => 30, 'K' => 30, 'L' => 30, 'M' => 30, 'N' => 30, 'O' => 30], [
            'Lp',
            'Data wpisania',
            'Imię i nazwisko',
            'Numer ewidencyjny',
            'Nieobecność od',
            'Nieobecność do',
            'Liczba dni urlopu wnioskowanego',
            'Rodzaj nieobecności',
            'Imię dziecka',
            'Osoba akceptujaca',
            'Data akceptacji',
            'Zakład',
            'Przełożony',
            'Status wniosku',
            'Data pobrania przez HR',
        ]);
        $wykonaj = mysqli_query($polaczenie,$zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, 'Eksport MPK');
        $licznik = 2;
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {

                $objPHPExcel->setActiveSheetIndex(0);

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik,  $licznik-1);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik,  $dane['data_wpisania']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik,  $dane['imie_nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik,  $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik,  $dane['poczatek_wolnego']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik,  $dane['koniec_wolnego']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik,  abs($dane['liczba_dni_eksport']));
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik,  $dane['nazwakategorii']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $licznik,  $dane['imie_dziecka']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik,  $dane['login_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik,  $dane['imie'].' '.$dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik,  $dane['nazwa_profilu']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik,  $dane['login_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik,  $dane['nazwa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik,  $dane['data_hr']);
                $licznik++;
            }

        }

        // Praca zdalna
        $zapytanieEksportu = $this->PobierzZapytanie($_POST['zapytanie_praca_zdalna_excel']);
        $wykonaj = mysqli_query($polaczenie, $zapytanieEksportu);
        $this->BladZapytania($polaczenie, $zapytanieEksportu, 'Eksport praca zdalna');
        if (mysqli_num_rows($wykonaj) > 0) {
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $LiczbaDni =  Urlopy::PobierzIloscDniRoboczych($dane['DataOd'], $dane['DataDo']);
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik,  $licznik-1);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['data_wpisania']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['imie_nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, $dane['NumerEwidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $dane['poczatek_pracy_zdalnej']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $licznik, $dane['koniec_pracy_zdalnej']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $licznik, $LiczbaDni);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $licznik, $dane['SymbolAbsencji']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $licznik, $dane['login_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $licznik, $dane['data_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $licznik, $dane['zaklad']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $licznik, $dane['login_kierownik']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $licznik, $dane['nazwa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('O' . $licznik, $dane['data_hr']);
                $licznik++;
            }
        }

        mysqli_close($polaczenie);
        $this->WyslijArkuszXlsx($objPHPExcel, 'Stan urlopu.xlsx', 'Raport urlopów');
    }

}