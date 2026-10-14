<?php

class Urlopy extends Common
{
    static $DniSwiateczne = [];
    static function SprawdzCzyBrygadzista($NumerEwidencyjny) {
        $polaczenie = (new Urlopy)->MysqliConnect();

        $q1 = "SELECT brygadzista FROM stan_godzin WHERE numer_ewidencyjny='".$NumerEwidencyjny."' LIMIT 1";
        $e1 = mysqli_query($polaczenie, $q1) OR DIE(mysqli_error($polaczenie));
        $d1 = mysqli_fetch_array($e1);
        return isset($d1['brygadzista']) AND $d1['brygadzista'];
    }
    static function PobierzIloscDniRoboczych($DataPoczatkowa, $DataKoncowa) {
        $DniRobocze = 0;
        $DniWolne = ['Saturday','Sunday'];
        $Licznik = 0;

        if($DataPoczatkowa != '0000-00-00 00:00:00.000' AND $DataKoncowa != '0000-00-00 00:00:00.000') {
            $DataPoczatkowa = date('Ymd', strtotime($DataPoczatkowa));
            $DataKoncowa = date('Ymd', strtotime($DataKoncowa));
            for ($DataLicznik = $DataPoczatkowa; $DataLicznik <= $DataKoncowa;) {
                $DataObject = new DateTime($DataLicznik);
                if (!in_array($DataObject->format('l'), $DniWolne) and !in_array($DataLicznik, self::PobierzDniSwiateczne())) {
                    $DniRobocze++;
                }
                if($Licznik > 100) {
                    break;
                }
                $Licznik++;
                $DataLicznik = date('Ymd', strtotime($DataLicznik." + 1 day"));
            }
        }

        return $DniRobocze;
    }

    static function PobierzDniSwiateczne() {
        if(count(self::$DniSwiateczne) == 0) {
            $polaczenie = (new Urlopy)->MysqliConnect();
            $Query = "SELECT GROUP_CONCAT(date_format(dw.dzien, '%Y%m%d')) as dni_wolne FROM dni_wolne dw";
            $Execute = mysqli_query($polaczenie, $Query) or die(mysqli_error($polaczenie));
            self::$DniSwiateczne = explode(',',mysqli_fetch_assoc($Execute)['dni_wolne']);
        }
        return self::$DniSwiateczne;
    }
}