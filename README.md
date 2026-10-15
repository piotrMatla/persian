# eWnioski

Program do wniosków urlopowych, zwolnień z godzin, pracy zdalnej i rozliczania nadgodzin.
Wersja 2.00 działa na PHP 8.2–8.5 (testowana na PHP 8.5) i korzysta z tej samej bazy danych co wersja 1.x – schemat bazy się nie zmienił.

## Wdrożenie

1. Skopiuj katalog projektu na serwer (np. w miejsce dotychczasowego `/home/lora/public_html/eWnioski`).
2. Zainstaluj biblioteki: `composer install --no-dev` (powstanie katalog `vendor/`).
   Jeśli serwer nie ma dostępu do internetu, uruchom to polecenie na innym komputerze i skopiuj cały katalog razem z `vendor/`.
3. Uzupełnij `conf/config.php` – dane do MySQL (wcześniej wpisane na sztywno w `Common::MysqliConnect`), ścieżkę do pliku `confe.ini`
   i do wspólnej klasy `Polaczenie` (RCP/Saturn). Każdą wartość można też podać zmienną środowiskową, np. `EWNIOSKI_DB_HASLO`.
4. `conf/confe.ini` działa jak dotychczas (foldery logów, plików tymczasowych i dokumentów, adresy mailowe).

### Wymagane rozszerzenia PHP

- `mysqli`, `mbstring`, `iconv`, `session`, `json`
- `zip`, `xml`, `xmlwriter`, `dom`, `gd`, `fileinfo` – dla eksportów do Excela (PhpSpreadsheet)
- `odbc` i `sqlsrv` – tylko dla funkcji korzystających z RCP i Saturna (logowanie przepustką, eksporty do SaturnNET/Enovy, skrypt `skrypty/PobierzDaneRCP.php`)

### Pliki spoza projektu

- Klasa `Polaczenie` (`/home/lora/public_html/PolaczenieWise/Polaczenie.php`) z połączeniami do RCP i Saturna nadal jest wczytywana z zewnątrz
  i też musi działać na PHP 8. Jeśli pliku nie ma, program działa dalej, a funkcje wymagające RCP/Saturna pokazują komunikat o braku połączenia.
- PHPExcel 1.8, PEAR `Spreadsheet_Excel_Writer` i stary FPDF z `/home/lora/public_html/...` nie są już używane (nie działają na PHP 8) –
  zastąpiły je PhpSpreadsheet i FPDF z Composera. Czcionka do wydruków PDF (Liberation Sans z polskimi znakami) jest w `php/fonts`.

## Docker (PHP 8.5 + Apache)

```sh
cp .env.example .env      # dane do bazy, adres programu, SMTP
docker compose up -d --build
```

Program będzie pod `http://serwer:8080/` (port: `EWNIOSKI_PORT`), stare adresy `/eWnioski/php/...` też działają.

- **Baza** – kontener łączy się z dotychczasową bazą MySQL (`EWNIOSKI_DB_*` w `.env`). Jeśli MySQL działa na tym samym serwerze,
  ustaw `EWNIOSKI_DB_HOST=host.docker.internal`; MySQL musi wtedy nasłuchiwać nie tylko na `127.0.0.1` (`bind-address`),
  a użytkownik bazy musi mieć dostęp z sieci Dockera (np. `'user'@'172.%'`).
- **Dane** – katalog `./dane` (logi, pliki tymczasowe importu, `pliki/` i `pliki_kierownicy/` z dokumentami do pobrania).
  Zamiast `confe.ini` kontener używa `conf/confe.docker.ini`, który wskazuje na te foldery i bierze adresy mailowe z `.env`.
- **Maile** – wysyła je `msmtp` przez serwer SMTP z `.env` (`SMTP_HOST` itd.). Bez `SMTP_HOST` maile nie wychodzą.
- **RCP i Saturn** – zbuduj obraz z `INSTALL_MSSQL=1` (rozszerzenia `odbc`, `sqlsrv` i sterownik Microsoft ODBC 18) i odkomentuj
  w `docker-compose.yml` montowanie katalogu `PolaczenieWise`. Jeśli klasa `Polaczenie` używa DSN-ów z `/etc/odbc.ini`, zamontuj też ten plik.
- **Logi PHP i Apache** – `docker compose logs -f ewnioski`.
- **Cron** – na serwerze, np.:

  ```
  0 7 * * 1-5  docker compose -f /sciezka/do/eWnioski/docker-compose.yml exec -T ewnioski php php/mail.php
  */10 * * * * docker compose -f /sciezka/do/eWnioski/docker-compose.yml exec -T ewnioski php skrypty/PobierzDaneRCP.php
  ```

Aktualizacja programu: `git pull && docker compose up -d --build`.

## Struktura

- `php/bootstrap.php` – wspólny start każdego skryptu (autoloader, Composer, ustawienia mysqli)
- `php/klasy/Common.php` – wspólne funkcje: połączenie z bazą, obsługa błędów, logi, maile, pobieranie plików, eksport XLSX, komunikaty
- `php/klasy/HTML.php` – układ stron (menu boczne, logowanie, strona główna, ekrany dotykowe)
- `css/app.css`, `js/app.js` – wygląd (tryb jasny/ciemny) i obsługa menu, ulubionych i okien

## Cron

- `php php/mail.php` – przypomnienia o wnioskach do akceptacji (pon–pt)
- `php skrypty/PobierzDaneRCP.php` – pobieranie odbić z RCP
