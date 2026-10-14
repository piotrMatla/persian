<?php
/**
 * Created by PhpStorm.
 * User: kduda
 * Date: 07.01.2019
 * Time: 12:04
 */

class Excel extends Common
{
    /**
     * Eksport pliku excel do Saturna
     */
    function EksportSaturn()
    {
        set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
        require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';
        //utworzenie nowego arkusza
        $workbook = new Spreadsheet_Excel_Writer();
        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $workbook->send('NieobUzup.xls');
        $worksheet = &$workbook->addWorksheet('NieobUzup');
        $workbook->setVersion(8);
        $worksheet->setInputEncoding('UTF-8');

        $decimal_format = &$workbook->addFormat();
        $decimal_format->setNumFormat(0.00);
        $worksheet->setColumn(0, 1, 10);
        $worksheet->setColumn(2, 2, 50);
        $worksheet->setColumn(3, 8, 20);
        $worksheet->setColumn(9, 9, 30);
        $size = &$workbook->addFormat();
        $size->setRight(250);
        $string_format = &$workbook->addFormat();
        $string_format->setNumFormat('');
        $upper_left_side_brick = &$workbook->addFormat();
        $upper_left_side_brick->setBold(900);
        $upper_left_side_brick->setSize(11);
        $upper_left_side_brick->setPattern(1);
        $upper_left_side_brick->setFgColor('green');

        $rzad = 0;
        $worksheet->write($rzad, 0, 'PESEL.', $upper_left_side_brick);
        $worksheet->write($rzad, 1, 'SymbolAbsencji', $upper_left_side_brick);
        $worksheet->write($rzad, 2, 'dataod', $upper_left_side_brick);
        $worksheet->write($rzad, 3, 'datado', $upper_left_side_brick);
        $worksheet->write($rzad, 4, 'DataRozlicenia', $upper_left_side_brick);
        $worksheet->write($rzad, 5, 'dni', $upper_left_side_brick);
        $worksheet->write($rzad, 6, 'DniRob', $upper_left_side_brick);
        $worksheet->write($rzad, 7, 'GodzRob', $upper_left_side_brick);
        $worksheet->write($rzad, 8, 'NaZadanie', $upper_left_side_brick);
        $i = 1;
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel'], 'Eksport MPK');
        $rzad++;
        while ($dane = mysqli_fetch_array($wykonaj)) {

            if ($dane['id_status'] == 3) {
                $zapytanieutlop = "SELECT p.PESEL
FROM [SaturnNET].[dbo].[vPracownicy]  p
where p.Numer='" . $dane['numer_ewidencyjny'] . "'";
                $e1 = odbc_exec($hr_polaczenie, $zapytanieutlop) OR DIE(odbc_error());
                $wynik = odbc_fetch_array($e1);
                $pesel = $wynik['PESEL'];

                $datapoczatek = new DateTime($dane['poczatek_wolnego']);
                $datakoniec = new DateTime($dane['koniec_wolnego']);
                $poczatekwolnego = date_format($datapoczatek, 'Y-m-d') . ' 00:00:00.000';
                $koniec_wolnego = date_format($datakoniec, 'Y-m-d') . ' 00:00:00.000';
                $datarozliczenia = date('Y-m-d') . ' 00:00:00.000';
                $zadanie = 0;
                if ($dane['id_kategorii'] == 4) $zadanie = 1;

                $worksheet->write($rzad, 0, $pesel);
                $worksheet->write($rzad, 1, $dane['kod']);
                $worksheet->write($rzad, 2, $poczatekwolnego);
                $worksheet->write($rzad, 3, $koniec_wolnego);
                $worksheet->write($rzad, 4, $datarozliczenia);
                $worksheet->write($rzad, 5, $dane['liczba_dni_eksport']);
                $worksheet->write($rzad, 6, $dane['liczba_dni_eksport']);
                $worksheet->write($rzad, 7, $dane['liczba_dni_eksport'] * 8);
                $worksheet->write($rzad, 8, $zadanie);
                $zapytaniezapisz = "UPDATE wniosek SET id_status='9', login_hr='" . $_SESSION['ewnioski_full_name'] . "', data_hr=now()
                      WHERE id='" . $dane['id'] . "' ";
                //$wykonajzapisz = mysqli_query($polaczenie, $zapytaniezapisz);
                $this->BladZapytania($polaczenie, $zapytaniezapisz, $_SESSION['ewnioski_login']);
                $this->ZapiszLog('Zapytanie;' . $zapytaniezapisz, $_SESSION['ewnioski_login']);
                $i++;
                $rzad++;
            }
        }
        $workbook->close();
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        exit;
    }

    function EksportSaturnXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("NieobUzup");
        $objPHPExcel->getProperties()->setSubject("NieobUzup");
        $objPHPExcel->getProperties()->setDescription("NieobUzup");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'PESEL');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'SymbolAbsencji');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'DataOd');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'DataDo');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'DataRozliczenia');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Dni');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'DniRob');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'GodzRob');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'NaZadanie');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'NumerEwidencyjny');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel'], 'Eksport MPK');
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
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('C' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('E' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('F' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('G' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('H' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('I' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('J' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
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
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_praca_zdalna_excel']);
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('NieobUzup');
        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="NieobUzup.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }

    function EksportZwolnieniaXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Zwolnienia");
        $objPHPExcel->getProperties()->setSubject("Zwolnienia");
        $objPHPExcel->getProperties()->setDescription("Zwolnienia");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Imię i nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pesel');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Liczba godzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Data zwolnienia');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Data odpracowania');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Godzina rozpoczęcia ');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Przedział czasowy zmiany godzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Rodzaj');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Status');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Dane kierownika');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Osoba akceptująca');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Data akceptacji');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Data pobrania przez HR');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Data wpisania');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel_zwolnienia']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel_zwolnienia'], 'Eksport MPK');
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Zwolnienia');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Zwolnienia.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }

    function EksportZwolnieniaWidokXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Zwolnienia");
        $objPHPExcel->getProperties()->setSubject("Zwolnienia");
        $objPHPExcel->getProperties()->setDescription("Zwolnienia");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Imię i nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pesel');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Liczba godzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Data zwolnienia');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Data odpracowania');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Godzina rozpoczęcia ');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Przedział czasowy zmiany godzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Rodzaj');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Status');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Dane kierownika');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Osoba akceptująca');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Data akceptacji');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Data pobrania przez HR');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Data wpisania');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel_widok']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel_widok'], 'Eksport MPK');
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Zwolnienia');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Zwolnienia.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }
    function EksportPracaZdalnaCzasPracyXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Czas pracy zdalnej");
        $objPHPExcel->getProperties()->setSubject("Czas pracy zdalnej");
        $objPHPExcel->getProperties()->setDescription("Czas pracy zdalnej");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Imię');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Dzień pracy');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Czas pracy');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel_praca_zdalna_czas_pracy']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel_praca_zdalna_czas_pracy'], 'Eksport MPK');
        if (mysqli_num_rows($wykonaj) > 0) {
            $licznik = 2;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $data_rozpoczenia=date_create($dane['data_rozpoczecia']);
                $data_zakonczenia=date_create($dane['data_zakonczenia']);
                $data_wznowienia=date_create($dane['data_wznowienia']);
                $data_wstrzymania=date_create($dane['data_wstrzymania']);
                $czas_pracy = $data_zakonczenia->diff($data_rozpoczenia);
                $czas_przerwy= $data_wznowienia->diff($data_wstrzymania);
                $godzina_pracy=$czas_pracy->format('%h');
                $minuta_pracy=$czas_pracy->format('%i');
                $godzina_przerwy=$czas_przerwy->format('%h');
                $minuta_przerwy=$czas_przerwy->format('%i');
                $godzina=$godzina_pracy-$godzina_przerwy;
                $minuta=$minuta_pracy-$minuta_przerwy;
                if($minuta<0)
                {
                    $godzina--;
                    $minuta=60+$minuta;
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $licznik, $dane['numer_ewidencyjny']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $licznik, $dane['nazwisko']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $licznik, $dane['imie']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $licznik, date_create($dane['data_rozpoczecia'])->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $licznik, $godzina.':'.$minuta);
                $licznik++;

            }

        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Czas pracy zdalnej');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Czas pracy zdalnej.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
    function EksportPracaZdalnaAkcjeXLSX()
    {

        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Praca zdalna");
        $objPHPExcel->getProperties()->setSubject("Praca zdalna");
        $objPHPExcel->getProperties()->setDescription("Praca zdalna");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('f')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Imię');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Data akcji');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Typ akcji');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Numer wpisującego');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel_praca_zdalna']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel_praca_zdalna'], 'Eksport praca zdalna');
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
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Praca zdalna');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Praca zdalna.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
    function EksportSaturnXLSXWniosekWRCP()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("NieobUzup");
        $objPHPExcel->getProperties()->setSubject("NieobUzup");
        $objPHPExcel->getProperties()->setDescription("NieobUzup");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'PESEL');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'SymbolAbsencji');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'DataOd');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'DataDo');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'DataRozliczenia');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Dni');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'DniRob');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'GodzRob');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'NaZadanie');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'NumerEwidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Błąd');
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel2']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel2'], 'Eksport MPK');
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
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('C' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('E' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('F' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('G' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('H' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('I' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                        $objPHPExcel->getActiveSheet()->getStyle('J' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
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
                    $objPHPExcel->getActiveSheet()->SetCellValue('k' . $licznik, $blad);
                    $licznik++;
                }
            }

        }

        // Praca zdalna
        $PobranePozycjeId = [];
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_praca_zdalna_excel']);
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


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('NieobUzup');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="NieobUzup.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }
    function EksportEnovaXLSXWniosekWRCP()
    {

        $polaczenie = $this->MysqliConnect();
        $hr_polaczenie = $this->HrPolaczenie();
        $rok = date('Y');
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("NieobUzup");
        $objPHPExcel->getProperties()->setSubject("NieobUzup");
        $objPHPExcel->getProperties()->setDescription("NieobUzup");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Class');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Pracownik:Class');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pracownik:Kod');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Definicja:Nazwa');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Okres.Od');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Okres.Do');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'OdGodziny');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'DoGodziny');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Czas');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Urlop.Przyczyna');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Pracownik:Nazwisko imie');

        $licznik = 2;
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel2_enova']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel2_enova'], 'Eksport eWniosek Enova');
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
                              $objPHPExcel->getActiveSheet()->getStyle('A' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                              $objPHPExcel->getActiveSheet()->getStyle('B' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                              $objPHPExcel->getActiveSheet()->getStyle('C' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                              $objPHPExcel->getActiveSheet()->getStyle('D' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                              $objPHPExcel->getActiveSheet()->getStyle('E' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
                              $objPHPExcel->getActiveSheet()->getStyle('F' . $licznik)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($kolor);
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
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_praca_zdalna_excel']);
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Enova');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        mysqli_close($polaczenie);
        odbc_close($hr_polaczenie);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Enova.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }
    function EksportStanGodzin()
    {
        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');

        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Stan urlopu i nadgodzin");
        $objPHPExcel->getProperties()->setSubject("Stan urlopu i nadgodzin");
        $objPHPExcel->getProperties()->setDescription("Stan urlopu i nadgodzin");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(28);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(26);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Nr ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Imię');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Mpk i grupa');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Kierownik');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'UW');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'UW - godziny');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'UW zaległy');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'UŻ');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Ilość nadgodzin nadpracowanych (limit 260)');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'NADGODZINY');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Godziny odpracowane (+)');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Godziny do odpracowania (-)');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Niedopracowania (bez wniosku)');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Stan godzin (archiwalne)');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Stan na dzień');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Data importu');
        $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Osoba importująca');

        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_excel_stan_godzin']);
        $this->BladZapytania($polaczenie, $_POST['zapytanie_excel_stan_godzin'], 'Eksport MPK');
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
                    PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $licznik, $dane['data_importu']);
                $objPHPExcel->getActiveSheet()->SetCellValue('R' . $licznik, $dane['nazwisko_import'] . ' ' . $dane['imie_import']);
                $licznik++;
            }
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Stan godzin');

        // Save Excel 2007 file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        mysqli_close($polaczenie);
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Stan urlopu i nadgodzin.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
    function NadgodzinyExcelExport($zapytanie)
    {
        $polaczenie=$this->MysqliConnect();
        $zapytanie = base64_decode($zapytanie);

        set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
        require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';

        // Creating a workbook
        $workbook = new Spreadsheet_Excel_Writer();

        // sending HTTP headers
        $workbook->send('Ewidencja_nadgodzin_' . date("Y_m_d_H_i_s") . '.xls');

        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet('Wiazki poprawiane');
        $workbook->setVersion(8);
        $worksheet->setInputEncoding('UTF-8');

        // Tworzenie formatu
        $decimal_format =& $workbook->addFormat();
        $decimal_format->setNumFormat(0.00);

        $worksheet->setInputEncoding('UTF-8');
        $worksheet->setColumn(0, 1, 10);
        $worksheet->setColumn(2, 25, 25);

        $size =& $workbook->addFormat();
        $size->setRight(250);

        $string_format =& $workbook->addFormat();
        $string_format->setNumFormat('');

        $upper_left_side_brick =& $workbook->addFormat();
        $upper_left_side_brick->setBold(900);
        $upper_left_side_brick->setSize(12);
        $upper_left_side_brick->setPattern(1);
        $upper_left_side_brick->setFgColor('green');

        $rzad = 0;
        $wykonaj = mysqli_query($polaczenie, $zapytanie);
        $this->BladZapytania($polaczenie, $zapytanie, $_SESSION['ewnioski_login']);
        if (mysqli_num_rows($wykonaj) > 0) {
            $worksheet->write($rzad, 0, 'Lp', $upper_left_side_brick);
            $worksheet->write($rzad, 1, 'Id', $upper_left_side_brick);
            $worksheet->write($rzad, 2, 'Numer_ewidencyjny', $upper_left_side_brick);
            $worksheet->write($rzad, 3, 'Nazwisko', $upper_left_side_brick);
            $worksheet->write($rzad, 4, 'Imię', $upper_left_side_brick);
            $worksheet->write($rzad, 5, 'Ilość nadgodzin', $upper_left_side_brick);
            $worksheet->write($rzad, 6, 'Data nadgodzin', $upper_left_side_brick);
            $worksheet->write($rzad, 7, 'Wprowadził', $upper_left_side_brick);
            $worksheet->write($rzad, 8, 'Akceptacja Kierownik', $upper_left_side_brick);
            $worksheet->write($rzad, 9, 'Akceptacja HR', $upper_left_side_brick);
            $worksheet->write($rzad, 10, 'Zaakceptował/a (Kierownik)', $upper_left_side_brick);
            $worksheet->write($rzad, 11, 'Zaakceptował/a HR', $upper_left_side_brick);
            $worksheet->write($rzad, 12, 'Data akceptacji Kierownik', $upper_left_side_brick);
            $worksheet->write($rzad, 13, 'Data akceptacji HR', $upper_left_side_brick);
            $worksheet->write($rzad, 14, 'Grupa', $upper_left_side_brick);
            $worksheet->write($rzad, 15, 'Data wprowadzenia', $upper_left_side_brick);
            $worksheet->write($rzad, 16, 'Uzasadnienie', $upper_left_side_brick);
            $worksheet->write($rzad, 17, 'Anulowano', $upper_left_side_brick);
            $worksheet->write($rzad, 18, 'Brygadzista notatka', $upper_left_side_brick);
            $worksheet->write($rzad, 19, 'Kierownik notatka', $upper_left_side_brick);
            $worksheet->write($rzad, 20, 'Hr notatka', $upper_left_side_brick);
            $rzad++;
            while ($dane = mysqli_fetch_array($wykonaj)) {
                $pracownik = explode(" ", trim($dane['pracownik']));
                if(!isset($pracownik[1])) $pracownik[1] = "";
                $worksheet->write($rzad, 0, $rzad);
                $worksheet->write($rzad, 1, $dane['id']);
                $worksheet->write($rzad, 2, $dane['numer_ewidencyjny']);
                $worksheet->write($rzad, 3, $pracownik[1]);
                $worksheet->write($rzad, 4, $pracownik[0]);
                $_g_val = $dane['ilosc_godzin_nadliczbowych'];
                if ($_g_val !== null) {
                    $_g_h = (int)floor((float)$_g_val);
                    $_g_m = (int)round(((float)$_g_val - $_g_h) * 60);
                    $worksheet->write($rzad, 5, $_g_h . ' h ' . str_pad($_g_m, 2, '0', STR_PAD_LEFT) . ' min');
                } else {
                    $worksheet->write($rzad, 5, $_g_val);
                }
                $worksheet->write($rzad, 6, $dane['data_wykonywania_nadgodzin']);
                $worksheet->write($rzad, 7, $dane['wprowadzil']);
                $worksheet->write($rzad, 8, $dane['akceptacja_kierownik']);
                $worksheet->write($rzad, 9, $dane['akceptacja_dzial_personalny']);
                $worksheet->write($rzad, 10, $dane['akceptacja_kierownik_user']);
                $worksheet->write($rzad, 11, $dane['akceptacja_dzial_personalny_user']);
                $worksheet->write($rzad, 12, $dane['data_akceptacji_kierownik']);
                $worksheet->write($rzad, 13, $dane['data_akceptacji_dzial_personalny']);
                $worksheet->write($rzad, 14, $dane['grupa']);
                $worksheet->write($rzad, 15, $dane['data_wprowadzenia']);
                $worksheet->write($rzad, 16, $dane['uzasadnienie']);
                $worksheet->write($rzad, 17, $dane['anulowano']);
                $worksheet->write($rzad, 18, $dane['brygadzista_notatka']);
                $worksheet->write($rzad, 19, $dane['kierownik_notatka']);
                $worksheet->write($rzad, 20, $dane['hr_notatka']);
                $rzad++;
            }
            $rzad += 3;
            $worksheet->write($rzad, 1, "Wygenerowano: " . date('Y-m-d H:i:s') . " Przez: " . $_SESSION['ewnioski_full_name']);
// Let's send the file
            $workbook->close();
            exit;
        }
    }
    function NadgodzinyExcelExportXLSX($zapytanie)
    {
        $zapytanie = base64_decode($zapytanie);
        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');

        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Ewidencja nadgodzin");
        $objPHPExcel->getProperties()->setSubject("Ewidencja nadgodzin");
        $objPHPExcel->getProperties()->setDescription("Ewidencja nadgodzin");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(25);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lp');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Id');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Numer_ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Imię');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Ilość nadgodzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Data nadgodzin');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Typ wpisu');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Wprowadził');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Data odpracowania');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Akceptacja Kierownik');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Akceptacja HR');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Zaakceptował/a (Kierownik)');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Zaakceptował/a HR');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Data akceptacji Kierownik');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Data akceptacji HR');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Grupa');
        $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Data wprowadzenia');
        $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Uzasadnienie');
        $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'Uzasadnienie poziom II');
        $objPHPExcel->getActiveSheet()->SetCellValue('U1', 'Uzasadnienie poziom III');
        $objPHPExcel->getActiveSheet()->SetCellValue('V1', 'Anulowano');
        $objPHPExcel->getActiveSheet()->SetCellValue('W1', 'Brygadzista notatka');
        $objPHPExcel->getActiveSheet()->SetCellValue('X1', 'Kierownik notatka');
        $objPHPExcel->getActiveSheet()->SetCellValue('Y1', 'Hr notatka');

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
                    PHPExcel_Cell_DataType::TYPE_STRING
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Ewidencja_nadgodzin');

        // Save Excel 2007 file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        mysqli_close($polaczenie);
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Ewidencja_nadgodzin.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
    function RaportWejscXLSX($zapytanie)
    {
        $zapytanie = $zapytanie;
        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Stan wejść");
        $objPHPExcel->getProperties()->setSubject("Stan wejść");
        $objPHPExcel->getProperties()->setDescription("Stan wejść");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lp');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Imię');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Godzina wejścia');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Godzina wyjścia');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Pobyt');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Typ pobytu');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Miejsce wejścia');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Grupa');

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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Ewidencja_nadgodzin');


        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        mysqli_close($polaczenie);
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Stan wejść.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }
    function RaportUrlopyWidoczneXLSX($zapytanie)
    {

        $polaczenie = $this->MysqliConnect();
        /** Error reporting */
        error_reporting(E_ALL);
        /* set_include_path("/home/lora/public_html/Biblioteki/PEAR/pear");
         require_once '/home/lora/public_html/Biblioteki/Excel/Writer.php';*/
        /** Include path **/
        set_include_path(get_include_path() . PATH_SEPARATOR . '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes');


        /** PHPExcel */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel.php';

        /** PHPExcel_Writer_Excel2007 */
        include '/home/lora/public_html/Biblioteki/PHPExcel_1_8/Classes/PHPExcel/Writer/Excel2007.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("PHP server");
        $objPHPExcel->getProperties()->setLastModifiedBy("PHP server");
        $objPHPExcel->getProperties()->setTitle("Raport urlopów");
        $objPHPExcel->getProperties()->setSubject("Raport urlopów");
        $objPHPExcel->getProperties()->setDescription("Raport urlopów");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lp');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Data wpisania');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Imię i nazwisko');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Numer ewidencyjny');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Nieobecność od');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Nieobecność do');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Liczba dni urlopu wnioskowanego');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Rodzaj nieobecności');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Imię dziecka');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Osoba akceptujaca');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Data akceptacji');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Zakład');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Przełożony');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Status wniosku');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Data pobrania przez HR');

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
        $wykonaj = mysqli_query($polaczenie, $_POST['zapytanie_praca_zdalna_excel']);
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

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Raport urlopów');

        // Save Excel 2007 file

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        // We'll be outputting an excel file
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        mysqli_close($polaczenie);
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="Stan urlopu.xlsx"');

        $objWriter->save('php://output');
        exit;
        // Echo done
    }


}