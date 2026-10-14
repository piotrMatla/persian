<?php
/**
 * Wspólny start każdego skryptu eWniosków: autoloader klas, biblioteki z Composera i konfiguracja.
 * Zastępuje kopiowany do każdego pliku autoloader (__autoload nie istnieje od PHP 8.0).
 */

define('EWNIOSKI_ROOT', dirname(__DIR__));

if (is_file(EWNIOSKI_ROOT . '/vendor/autoload.php')) {
    require_once EWNIOSKI_ROOT . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $klasa): void {
    $plik = __DIR__ . '/klasy/' . $klasa . '.php';
    if (is_file($plik)) {
        require_once $plik;
    }
});

// Od PHP 8.1 mysqli domyślnie rzuca wyjątki. Kod sprawdza błędy sam (Common::BladZapytania),
// więc przywracamy dotychczasowe zachowanie.
mysqli_report(MYSQLI_REPORT_OFF);

date_default_timezone_set('Europe/Warsaw');
