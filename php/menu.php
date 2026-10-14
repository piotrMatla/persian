
<div class="chromestyle" id="chromemenu">
    <ul>
        <li><a href="index.php">Strona główna</a></li>
        <?php

        // Zakładki początkowe do całego menu ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ?>
        <li><a href="#" rel="dropmenu1">Dodaj nowy wniosek o</a></li>
        <li><a href="#" rel="dropmenu2">Godziny nadliczbowe</a></li>
        <li><a href="#" rel="dropmenu3">Raporty</a></li>

        <li><a href="index.php?tym=1" rel="dropmenu4">Instrukcja</a></li>
        <li><a href="dokumenty.php?" rel="dropmenu4">Dokumenty i wnioski</a></li>
        <!-- Wylogowanie -->

        <li><a href="index.php?wyloguj">Wyloguj</a></li>
    </ul>
</div>
<!-- Listy rozwijane ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!-- Zakładka Dokument-->
<div id="dropmenu1" class="dropmenudiv">
    <a href="modyfikuj.php?urlop">Urlop</a>
    <a href="modyfikuj.php?zwolnienie">Zwolnienie z godzin/Zmiana czasu</a>
    <a href="modyfikuj.php?zdalna">Praca zdalna</a>
    <a href="modyfikuj.php?rozpocznij_zdalna">Rozpocznij/zakończ pracę zdalną</a>
</div>
<div id="dropmenu2" class="dropmenudiv">
    <a href="rozliczenie_nadgodzin.php">Rozliczenie nadgodzin</a>
</div>
<div id="dropmenu3" class="dropmenudiv">
    <a href="raporty.php?urlop">Raport urlopów</a>
    <a href="raporty.php?zwolnienia">Raport zwolnień/zmiany czasu</a>
    <a href="raporty.php?stan_urlopu">Stan urlopu i nadgodzin</a>
    <a href="raporty.php?wejscia">Stan wejść i wyjść</a>
    <a href="raporty.php?odbicia">Raport odbić</a>
    <a href="raporty.php?praca_zdalna">Raport pracy zdalnej</a>
    <a href="raporty.php?praca_zdalna_czas_pracy">Raport pracy zdalnej - czas pracy</a>
    <?php    if((($_SESSION['ewnioski_mpk']=="Ind.667099")!==false or ($_SESSION['ewnioski_mpk']=="Ind.667299 T.HR")!==false)){?>
    <!--   <a href="raporty.php?mpk">MPK-i wyjątki</a>
        <a href="raporty.php?osoby">Osoby wyjątki</a>-->
        <a href="raporty.php?wszystkie">Wszystkie osoby</a>
        <a href="raporty.php?wczytaj_urlop">Wczytaj liczbę urlopu</a>
        <a href="raporty.php?sprawdz_zastepce">Sprawdź zastępce</a>

    <?php }


        ?>

</div>

<script type="text/javascript">
    cssdropdown.startchrome("chromemenu")
</script>