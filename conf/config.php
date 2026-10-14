<?php
/**
 * Konfiguracja eWniosków.
 *
 * Wartości domyślne odpowiadają tym, które wcześniej były wpisane na sztywno w kodzie,
 * więc po wgraniu na serwer program łączy się z tą samą bazą co dotychczas.
 * Każdą wartość można nadpisać zmienną środowiskową (np. w konfiguracji Apache: SetEnv EWNIOSKI_DB_HASLO ...).
 */

$env = static fn(string $nazwa, string $domyslna): string => (getenv($nazwa) !== false && getenv($nazwa) !== '') ? getenv($nazwa) : $domyslna;

return [
    // Baza MySQL / MariaDB (wcześniej Common::MysqliConnect)
    'db' => [
        'host'  => $env('EWNIOSKI_DB_HOST', 'localhost'),
        'user'  => $env('EWNIOSKI_DB_USER', 'user'),
        'haslo' => $env('EWNIOSKI_DB_HASLO', 'dummy_pass'),
        'baza'  => $env('EWNIOSKI_DB_BAZA', 'ewnioski_test'),
        'port'  => (int)$env('EWNIOSKI_DB_PORT', '3306'),
    ],

    // Plik ini z folderami i adresami mailowymi (wcześniej /home/lora/public_html/eWnioski/conf/confe.ini)
    'ini' => $env('EWNIOSKI_INI', __DIR__ . '/confe.ini'),

    // Wspólna klasa Polaczenie (połączenia ODBC/MSSQL do RCP i Saturna) - plik spoza projektu
    'polaczenie_wise' => $env('EWNIOSKI_POLACZENIE_WISE', '/home/lora/public_html/PolaczenieWise/Polaczenie.php'),

    // Adres programu używany w mailach
    'adres_programu' => $env('EWNIOSKI_ADRES', 'http://lora.pkc.motherson.com/eWnioski/php/'),

    // Strona, na którą trafia użytkownik po wylogowaniu z wersji dotykowej
    'wylogowanie_dotyk' => $env('EWNIOSKI_WYLOGOWANIE_DOTYK', 'http://lora.pkc.motherson.com/Wizualizacja/php/Einstrukcje.php'),

    // Nadawcy i kopia maili
    'mail_nadawca'       => 'eWnioski.Admin@motherson.com',
    'mail_nadawca_bledy' => 'ZapytaniaSQL@motherson.com',
    'mail_kopia'         => 'pkc.phpalerts@motherson.com',

    // MPK-i z dostępem do raportów administracyjnych HR
    'mpk_hr' => ['Ind.667099', 'Ind.667299 T.HR'],
];
