<?php
/**
 * Akceptacja formularza nadgodzin z linku w mailu
 */
require_once __DIR__ . '/bootstrap.php';
$a = new Common();
$h = new HTML();
$polaczenie = $a->MysqliConnect();
$h->NaglowekHtml('Akceptacja nadgodzin');
?>
<body class="auth-page">
<main class="auth-shell">
    <section class="auth-card">
        <?php
        $h->Logo();
        $formularz = base64_decode((string)($_GET['formularz'] ?? ''));
        if (is_numeric($formularz)) {
            $zapytanie = "UPDATE godziny_nadliczbowe_godziny SET akceptacja_kierownik='tak', data_akceptacji_kierownik=now() WHERE numer_formularza='" . $a->Esc($polaczenie, $formularz) . "'";
            mysqli_query($polaczenie, $zapytanie);
            if (mysqli_affected_rows($polaczenie) > 0) {
                $a->Alert('Dane zostały zaakceptowane', '', 'success');
            } else {
                $a->Alert('Dane nie zostały zaakceptowane', htmlspecialchars(mysqli_error($polaczenie)), 'error');
            }
        } else {
            $a->Alert('Błąd: Numer formularza jest niepoprawny.', '', 'error');
        }
        mysqli_close($polaczenie);
        ?>
    </section>
</main>
</body>
</html>
