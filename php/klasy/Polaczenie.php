<?php

/**
 * Zastępcza klasa Polaczenie.
 *
 * Właściwa klasa leży poza projektem (ścieżka 'polaczenie_wise' w conf/config.php) i zawiera połączenia
 * ODBC/MSSQL do RCP i Saturna. Ten plik jest ładowany tylko wtedy, gdy tamtego pliku nie ma -
 * wtedy działa cała część oparta o MySQL, a funkcje korzystające z RCP/Saturna zgłaszają czytelny błąd.
 */
class Polaczenie
{
    private function BrakPolaczenia(string $nazwa): never
    {
        error_log("eWnioski: brak pliku klasy Polaczenie - połączenie $nazwa niedostępne");
        echo '<div class="alert alert-error"><div class="alert-title">Brak połączenia ' . $nazwa . '</div>'
            . 'Nie znaleziono pliku klasy Polaczenie (' . htmlspecialchars((string)Konfiguracja::Pobierz('polaczenie_wise'))
            . '). Ustaw ścieżkę \'polaczenie_wise\' w conf/config.php.</div>';
        exit;
    }

    function RCPPolaczenie()
    {
        $this->BrakPolaczenia('RCP (ODBC)');
    }

    function SQLRCPPolaczenie()
    {
        $this->BrakPolaczenia('RCP (sqlsrv)');
    }

    function HrPolaczenie()
    {
        $this->BrakPolaczenia('Saturn (ODBC)');
    }
}
