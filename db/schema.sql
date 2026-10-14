-- Minimalny schemat, którego oczekuje przepisana aplikacja (increment 1).
-- To NIE jest pełny zrzut produkcyjnej bazy – to zestaw tabel/kolumn używanych
-- przez pion: logowanie -> wniosek urlopowy -> raport.
--
-- Nazwy tabel i kolumn zachowano zgodnie ze starą bazą, żeby ułatwić migrację.
-- Przy docelowym wdrożeniu należy dołączyć pełny schemat (patrz README).

-- Uwaga: w starej wersji hasła były w kolumnie `haslo_urlopy` jawnym tekstem.
-- Po migracji (bin/migrate-passwords.php) trzyma ona hash bcrypt/argon,
-- dlatego poniżej ma typ VARCHAR(255).

CREATE DATABASE IF NOT EXISTS ewnioski          CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ewidencja_szkolen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS oddelegowania     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Osoby (pracownicy).
CREATE TABLE IF NOT EXISTS ewidencja_szkolen.osoby (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    numer_ewidencyjny  VARCHAR(32)  NOT NULL,
    imie               VARCHAR(100) NOT NULL DEFAULT '',
    nazwisko           VARCHAR(100) NOT NULL DEFAULT '',
    email              VARCHAR(190) NULL,
    haslo_urlopy       VARCHAR(255) NULL,            -- hash hasła (po migracji)
    wnioski_dla_innych TINYINT(1)   NOT NULL DEFAULT 0,
    admin              CHAR(1)      NOT NULL DEFAULT '0',
    id_mpk             INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_numer (numer_ewidencyjny),
    KEY idx_email (email)
) ENGINE=InnoDB;

-- MPK (jednostki kosztowe) z danymi przełożonego.
CREATE TABLE IF NOT EXISTS ewidencja_szkolen.mpk (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    mpk       VARCHAR(64)  NULL,
    mail      VARCHAR(190) NULL,
    kierownik VARCHAR(190) NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Role/uprawnienia (dawniej oddelegowania.users.permisions).
CREATE TABLE IF NOT EXISTS oddelegowania.users (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    numer_ewidencyjny VARCHAR(32) NOT NULL,
    permisions        VARCHAR(32) NULL,
    PRIMARY KEY (id),
    KEY idx_numer (numer_ewidencyjny)
) ENGINE=InnoDB;

-- Stan urlopu i nadgodzin – ŹRÓDŁO PRAWDY o dostępnych dniach (liczone serwerowo).
CREATE TABLE IF NOT EXISTS ewnioski.stan_godzin (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    numer_ewidencyjny  VARCHAR(32)  NOT NULL,
    numer_kierownik    VARCHAR(32)  NULL,
    stan_urlopu        DECIMAL(6,2) NULL,
    brygadzista        TINYINT(1)   NOT NULL DEFAULT 0,
    data_importu       DATETIME     NULL,
    profil             VARCHAR(32)  NULL,
    PRIMARY KEY (id),
    KEY idx_numer (numer_ewidencyjny)
) ENGINE=InnoDB;

-- Dni wolne/świąteczne.
CREATE TABLE IF NOT EXISTS ewnioski.dni_wolne (
    id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    dzien DATE        NOT NULL,
    stale TINYINT(1)  NOT NULL DEFAULT 0,   -- 1 = powtarzalne co rok, 0 = jednorazowe
    PRIMARY KEY (id),
    KEY idx_dzien (dzien)
) ENGINE=InnoDB;

-- Wnioski urlopowe.
CREATE TABLE IF NOT EXISTS ewnioski.wniosek (
    id                           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_kategorii                 INT NOT NULL,                 -- LeaveType
    id_status                    INT NOT NULL DEFAULT 1,       -- LeaveStatus
    data_wpisania                DATETIME NOT NULL,
    osoba_wpisujaca              VARCHAR(190) NULL,
    imie_nazwisko                VARCHAR(190) NULL,
    poczatek_wolnego             DATE NOT NULL,
    koniec_wolnego               DATE NOT NULL,
    liczba_dni_eksport           DECIMAL(6,4) NOT NULL DEFAULT 0,
    imie_dziecka                 VARCHAR(190) NULL,
    data_urodzenia_dziecka       DATE NULL,
    numer_ewidencyjny            VARCHAR(32) NOT NULL,
    dane_mpk                     VARCHAR(32) NULL,
    numer_ewidencyjny_wpisujacy  VARCHAR(32) NULL,
    przyczyna                    VARCHAR(255) NULL,
    stopien_pokrewienstwa        VARCHAR(100) NULL,
    adres_zamieszkania           VARCHAR(255) NULL,
    liczba_godzin_enova          DECIMAL(6,2) NULL,
    data_kierownik               DATETIME NULL,  -- data auto-akceptacji przy wpisie przez przełożonego
    login_kierownik              VARCHAR(190) NULL,
    login_kierownik_data         DATETIME NULL,  -- wypełniane przez transitionStatus (akceptacja przełożonego)
    login_hr                     VARCHAR(190) NULL,
    login_hr_data                DATETIME NULL,  -- wypełniane przez transitionStatus (akceptacja HR)
    PRIMARY KEY (id),
    KEY idx_numer_status (numer_ewidencyjny, id_status),
    KEY idx_zakres (poczatek_wolnego, koniec_wolnego)
) ENGINE=InnoDB;
