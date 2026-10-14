<?php

/**
 * Dostęp do ustawień z conf/config.php
 */
class Konfiguracja
{
    private static ?array $ustawienia = null;

    /**
     * Pobranie wartości z konfiguracji
     * @param string $klucz Nazwa ustawienia, np. 'db' albo 'adres_programu'
     * @param mixed $domyslna Wartość zwracana, gdy ustawienia brak
     */
    public static function Pobierz(string $klucz, mixed $domyslna = null): mixed
    {
        if (self::$ustawienia === null) {
            self::$ustawienia = require EWNIOSKI_ROOT . '/conf/config.php';
        }
        return self::$ustawienia[$klucz] ?? $domyslna;
    }

    /**
     * Wczytanie zewnętrznej klasy Polaczenie (połączenia do RCP i Saturna).
     * Jeśli pliku nie ma, autoloader użyje zastępczej klasy z klasy/Polaczenie.php.
     */
    public static function WczytajPolaczenieWise(): void
    {
        $plik = self::Pobierz('polaczenie_wise');
        if (!class_exists('Polaczenie', false) && $plik && is_file($plik)) {
            require_once $plik;
        }
    }
}
