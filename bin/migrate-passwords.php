<?php

declare(strict_types=1);

/**
 * Jednorazowa migracja haseł jawnych -> hash.
 *
 * W starej wersji kolumna `ewidencja_szkolen.osoby.haslo_urlopy` trzymała
 * hasło jawnym tekstem. Ten skrypt zamienia każde takie hasło na hash bcrypt/argon.
 * Hasła, które już wyglądają na zahaszowane, pomija.
 *
 * Użycie:
 *   php bin/migrate-passwords.php --dry-run   # tylko pokaż, ile zmieni
 *   php bin/migrate-passwords.php             # wykonaj migrację
 */

use EWnioski\Kernel\Container;
use EWnioski\Security\PasswordHasher;

/** @var Container $container */
$container = require dirname(__DIR__) . '/bootstrap.php';

$dryRun = in_array('--dry-run', $argv, true);
$pdo = $container->database()->connection();
$hasher = new PasswordHasher();

$rows = $pdo->query(
    "SELECT id, haslo_urlopy FROM ewidencja_szkolen.osoby
     WHERE haslo_urlopy IS NOT NULL AND haslo_urlopy <> ''"
)->fetchAll();

$update = $pdo->prepare('UPDATE ewidencja_szkolen.osoby SET haslo_urlopy = :h WHERE id = :id');

$migrated = 0;
$skipped = 0;
foreach ($rows as $row) {
    $current = (string) $row['haslo_urlopy'];
    if (!$hasher->needsRehash($current)) {
        $skipped++;
        continue;
    }
    $migrated++;
    if (!$dryRun) {
        $update->execute(['h' => $hasher->hash($current), 'id' => (int) $row['id']]);
    }
}

printf(
    "%s: do przehaszowania %d, pominięto (już hash) %d, razem %d.%s",
    $dryRun ? 'DRY-RUN' : 'WYKONANO',
    $migrated,
    $skipped,
    count($rows),
    PHP_EOL,
);
