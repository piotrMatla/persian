<?php

/**
 * Dokumenty i wzory wniosków do pobrania
 */
class Dokumenty extends Common
{
    /**
     * Wyświetlenie nagłówków tabeli z plikami i filtru nazwy
     */
    function PierwszyWierszTablica()
    {
        ?>
        <table width="100%" cellspacing="0" class="border_top">
        <tr>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Lp.</b>
            </td>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Nazwa pliku</b>
                <form id="ewnioski_nazwa_pliku" method="POST">
                    <input type="text" size="60" name="ewnioski_nazwa_pliku" value="<?php echo htmlspecialchars((string)$_SESSION['ewnioski_nazwa_pliku']); ?>" onchange="document.getElementById('ewnioski_nazwa_pliku').submit();">
                </form>
            </td>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Data wrzucenia na serwer</b>
            </td>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Rozmiar pliku</b>
            </td>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Podgląd pliku</b>
            </td>
        </tr>
        <?php
    }

    /**
     * Foldery z dokumentami dostępne dla zalogowanego użytkownika
     * @return array [ścieżka => opis przed datą]
     */
    private function FolderyDokumentow(): array
    {
        $confW = $this->ParseIniE();
        $foldery = [];
        if (!empty($confW['FolderPlikow'])) $foldery[$confW['FolderPlikow']] = '';
        if (($_SESSION['inny_pracownik'] ?? 0) == 1 and !empty($confW['FolderPlikowKierownicy'])) {
            $foldery[$confW['FolderPlikowKierownicy']] = 'Tylko kierownicy -';
        }
        return $foldery;
    }

    /**
     * Wiersze tabeli z plikami ze wszystkich dostępnych folderów
     */
    function DanePliki()
    {
        $licznik = 1;
        $filtr = (string)$_SESSION['ewnioski_nazwa_pliku'] == '' ? '.' : (string)$_SESSION['ewnioski_nazwa_pliku'];
        foreach ($this->FolderyDokumentow() as $folder => $opis) {
            $pliki = is_dir($folder) ? scandir($folder) : false;
            if ($pliki === false) {
                ?><tr><td colspan="5" class="padding_5_all text_red">Brak dostępu do folderu <?php echo htmlspecialchars($folder); ?></td></tr><?php
                continue;
            }
            foreach ($pliki as $file) {
                if ($file == '.' or $file == '..' or $file == 'Thumbs.db' or strpos($file, $filtr) === false) continue;
                $sciezka = $folder . '/' . $file;
                $color = $this->KolorWiersza($licznik); ?>
                <tr>
                    <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $licznik; ?>
                    </td>
                    <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo htmlspecialchars($file); ?>
                    </td>
                    <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo $opis . date("Y.m.d H:i:s.", (int)fileatime($sciezka)); ?>
                    </td>
                    <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <?php echo round((int)filesize($sciezka) / 1024000, 2); ?> MB
                    </td>
                    <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                        <form method="POST" class="inline">
                            <input type="hidden" name="zalacznik" value="<?php echo htmlspecialchars($sciezka); ?>"/>
                            <button type="submit" title="<?php echo htmlspecialchars($file); ?>">Otwórz</button>
                        </form>
                    </td>
                </tr>
                <?php
                $licznik++;
            }
        }
        ?></table><?php
    }

    /**
     * Otwarcie pliku z folderu z dokumentami
     * @param string $nazwa_pliku Pełna ścieżka do pliku
     */
    function OtwarcieZalacznika($nazwa_pliku)
    {
        $sciezka = realpath((string)$nazwa_pliku);
        foreach (array_keys($this->FolderyDokumentow()) as $folder) {
            $folder = realpath($folder);
            if ($sciezka !== false and $folder !== false and str_starts_with($sciezka, $folder . DIRECTORY_SEPARATOR)) {
                $this->WyslijPlik($sciezka);
            }
        }
        die('File Not Found');
    }
}
