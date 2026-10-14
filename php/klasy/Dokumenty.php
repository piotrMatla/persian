<?php

class Dokumenty extends Common
{
    /**
     * Wyświetlenie nagłówków i filtrów do Historii generowania zwolnień
     */
    function PierwszyWierszTablica()
    {
        ?>

        <br><br>
        <table width="100%" cellspacing="0" class="border_top">
        <tr>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Lp.</b>
            </td>
            <td align="center" class="padding_5_all bg_11 bottom right">
                <b>Nazwa pliku</b>
                <form id="ewnioski_nazwa_pliku" method="POST">
                    <input type="text" size="60" name="ewnioski_nazwa_pliku" value="<?php echo $_SESSION['ewnioski_nazwa_pliku']?>" onchange="document.getElementById('ewnioski_nazwa_pliku').submit();">
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
     function DanePliki()
    {
        $licznik = 1;
        $wiersz = 0;
        $confW = $this->ParseIniE("../conf/confe.ini");
        $destination = $confW['FolderPlikow'];
       $dir = opendir($destination);
       $destinationkierownicy = $confW['FolderPlikowKierownicy'];
       $dirkierownicy = opendir($destinationkierownicy);
        while (($file = readdir($dir)) !== false ) {
            if($file!='.' and $file!='..' and $file!='Thumbs.db')
                {

                     if($_SESSION['ewnioski_nazwa_pliku']=='')$wartos='.';
                    else $wartos=$_SESSION['ewnioski_nazwa_pliku'];
                    if( strpos($file,$wartos)!==false)
                        {
                      $color = $this->KolorWiersza($licznik);?>

                <tr>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $licznik; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $file; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo date("Y.m.d H:i:s.", fileatime($destination . '/'.$file)); ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo round(filesize($destination . '/'.$file)/1024000 , 2); ?> MB
                </td>

                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">

                 <a href="#" title="<?php echo $file; ?>" class="inline"
                   onclick="document.getElementById('form_zalacznik_<?php echo $licznik; ?>').submit();"><b
                            class="inline">Otwórz</b></a>
                <form method="POST" id="form_zalacznik_<?php echo $licznik; ?>" class="inline">
                    <input type="hidden" name="zalacznik" value="<?php echo $destination . '/'.$file; ?>"/>
                </form>

                </td>
                </tr>
            <?php
            $licznik++;
            }
                    $wiersz++;
}
            }
            if($_SESSION['inny_pracownik']==1)
                {
                    while (($file = readdir($dirkierownicy)) !== false ) {
            if($file!='.' and $file!='..'  and $file!='Thumbs.db')
                {


                     if($_SESSION['ewnioski_nazwa_pliku']=='')$wartos='.';
                    else $wartos=$_SESSION['ewnioski_nazwa_pliku'];
                    if( strpos($file,$wartos)!==false)
                        {
                      $color = $this->KolorWiersza($licznik);?>

                <tr>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $licznik; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo $file; ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                 Tylko kierownicy -<?php echo date("Y.m.d H:i:s.", fileatime($destinationkierownicy . '/'.$file)); ?>
                </td>
                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">
                    <?php echo round(filesize($destinationkierownicy . '/'.$file)/1024000 , 2); ?> MB
                </td>

                 <td align="center" bgcolor="<?php echo $color; ?>" class="padding_5_all bottom right">

                 <a href="#" title="<?php echo $file; ?>" class="inline"
                   onclick="document.getElementById('form_zalacznik_<?php echo $licznik; ?>').submit();"><b
                            class="inline">Otwórz</b></a>
                <form method="POST" id="form_zalacznik_<?php echo $licznik; ?>" class="inline">
                    <input type="hidden" name="zalacznik" value="<?php echo $destinationkierownicy . '/'.$file; ?>"/>
                </form>

                </td>
                </tr>
            <?php
            $licznik++;
            }
                    $wiersz++;
}
            }
                }

    }
     /**
     * Kolor wiersza w tabeli
     * @param type $licznik Pozycja z raportu
     * @return string Kolor wiersza
     */
    function KolorWiersza( $licznik)
    {
            if ($licznik % 2 == 0) {
                    $color = '#b5d4b4';
            } else {
                    $color = '#d6dbd6';
            }
        return $color;
    }
     /**
     * Otwarcie skanu faktury
     * @param type $nazwa_pliku Nazwa folderu i pliku który ma być  otwart
     */
    function OtwarcieZalacznika($nazwa_pliku)
    {

        $fullPath = $nazwa_pliku;
        var_dump($fullPath);
        $ext = pathinfo($nazwa_pliku, PATHINFO_EXTENSION);
        // Must be fresh start
        if (headers_sent())
            die('Headers Sent');
        // Required for some browsers
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');
        // File Exists?
        if (file_exists($fullPath)) {
            // Parse Info / Get Extension
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            // $ext = strtolower($path_parts["extension"]);
            // Determine Content Type
            switch ($ext) {
                case "pdf":
                    $ctype = "application/pdf";
                    break;
                case "exe":
                    $ctype = "application/octet-stream";
                    break;
                case "zip":
                    $ctype = "application/zip";
                    break;
                case "doc":
                    $ctype = "application/msword";
                    break;
                case "docx":
                    $ctype = "application/msword";
                    break;
                case "xls":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "xlsx":
                    $ctype = "application/vnd.ms-excel";
                    break;
                case "ppt":
                    $ctype = "application/vnd.ms-powerpoint";
                    break;
                case "gif":
                    $ctype = "image/gif";
                    break;
                case "png":
                    $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg":
                    $ctype = "image/jpg";
                    break;
                default:
                    $ctype = " application/octet-stream";
            }
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header("Content-Type: $ctype");
            header("Content-Disposition: attachment; filename=\"" . basename($fullPath) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $fsize);
            ob_clean();
            flush();
            readfile($fullPath);
        } else {
            die('File Not Found');
        }
    }
}