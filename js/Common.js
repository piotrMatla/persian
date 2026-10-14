function Weryfikacja()
{
    $tym=0;
    for ($i = 0; $i < 80; $i++) {
    //    if ($i !== 3) {

        if (document.getElementById('rodzaj_urlopu_' + $i) != null && document.getElementById('rodzaj_urlopu_' + $i).checked) {
            $tym = 1
        }
  //  }
    }
    if($tym==0)
    {
        alert('Nie wybrano rodzaju urlopu. Prosze wybrać rodzaj urlopu przed zapisanie wniosku');
    }
    else if(document.getElementById('zlagrupa').value=='zle')
    {
        alert('Nie masz uprawnień dla wpisywania wniosku dla tego pracownika');
    }
    else if(document.getElementById('poczatek_urlopu').value=='')
    {
        alert('Nie wybrano daty rozpoczęcia urlopu. Prosze wypełnić wszystkie pola');
    }
    else if(document.getElementById('koniec_urlopu').value=='')
    {
        alert('Nie wybrano daty końca urlopu. Prosze wypełnić wszystkie pola');
    }
    else if(document.getElementById('rodzaj_urlopu_15') != null && document.getElementById('rodzaj_urlopu_15').checked && document.getElementById('przyczyna_koniecznosci').value.trim()=='')
    {
        alert('Pole przyczyna jest wymagane dla wybranego rodzaju urlopu. Proszę wypełnić to pole.');
    }

    else{
        document.forms['form_1'].submit();
    }

}
function WeryfikacjaDotyk()
{
    $tym=0;
    for ($i = 0; $i < 30; $i++) {
            if ($i !== 3) {

        if (document.getElementById('rodzaj_urlopu_' + $i) != null && document.getElementById('rodzaj_urlopu_' + $i).checked) {
            $tym = 1
        }
          }
    }
    if($tym==0)
    {
        alert('Nie wybrano rodzaju urlopu. Prosze wybrać rodzaj urlopu przed zapisanie wniosku');
    }
    else if(document.getElementById('poczatek_urlopu').value=='')
    {
        alert('Nie wybrano daty rozpoczęcia urlopu. Prosze wypełnić wszystkie pola');
    }
    else if(document.getElementById('koniec_urlopu').value=='')
    {
        alert('Nie wybrano daty końca urlopu. Prosze wypełnić wszystkie pola');
    }
    else{
        document.forms['form_1'].submit();
    }

}
function CzyNaPewno(forma, komunikat)
{
    if(confirm(komunikat))
    {
        document.getElementById(forma).submit();
    }
}
function Pokaz($rodzaj) {
    document.getElementById('dziecko').style.display ='none';
    document.getElementById('dziecko2').style.display ='none';
    document.getElementById('dziecko3').style.display ='none';
    document.getElementById('okolicznosciowy').style.display ='none';
    document.getElementById('stawienicto').style.display ='none';
    document.getElementById('przyczyna').style.display ='none';
    document.getElementById('czlonek').style.display ='none';
    document.getElementById('stopien').style.display ='none';
    document.getElementById('adres').style.display ='none';
    if($rodzaj=='2' || $rodzaj=='6')
    {
        document.getElementById('okolicznosciowy').style.display ='table-row';
    }
    if( $rodzaj=='7')
    {
        document.getElementById('stawienicto').style.display ='table-row';
    }

    if($rodzaj=='5')
    {
        document.getElementById('dziecko').style.display ='table-row';
        document.getElementById('dziecko2').style.display ='table-row';
        document.getElementById('dziecko3').style.display ='table-row';
    }
    if($rodzaj=='15')
    {
        document.getElementById('dziecko3').style.display ='table-row';
        document.getElementById('przyczyna').style.display ='table-row';
        document.getElementById('przyczyna_label').innerHTML ='Przyczyna zwolnienia';
        document.getElementById('przyczyna_wymagane').style.display ='inline';
    }
    if($rodzaj=='16')
    {
        document.getElementById('dziecko').style.display ='table-row';
        document.getElementById('przyczyna').style.display ='table-row';
        document.getElementById('czlonek').style.display ='table-row';
        document.getElementById('stopien').style.display ='table-row';
        document.getElementById('przyczyna_label').innerHTML ='Przyczyna konieczności';
        document.getElementById('przyczyna_wymagane').style.display ='none';
    }

}
function PokazGodziny() {
    if(document.getElementById('caly_dzien').checked==true)
    {
        document.getElementById('koniec_urlopu').style.display ='table-row';
        document.getElementById('liczba_godzin').style.display ='none';
    }
    else
    {
        document.getElementById('koniec_urlopu').style.display ='none';
        document.getElementById('liczba_godzin').style.display ='table-row';
    }
}
function PokazPole($idsprawdz,$idpokaz,$idukryj) {
    if(document.getElementById($idsprawdz).checked==true)
    {
        document.getElementById($idpokaz).style.display ='table-row';
        document.getElementById($idukryj).style.display ='none';
    }
    else
    {
        document.getElementById($idpokaz).style.display ='none';
        document.getElementById($idukryj).style.display ='table-row';
    }
}
function PokazWniosek($rodzaj) {
    if(document.getElementById('godziny1') != null) document.getElementById('godziny1').style.display ='none';
    if(document.getElementById('godziny2') != null) document.getElementById('godziny2').style.display ='none';
    if(document.getElementById('godziny3') != null) document.getElementById('godziny3').style.display ='none';
    if(document.getElementById('godziny4') != null) document.getElementById('godziny4').style.display ='none';
    if(document.getElementById('godziny5') != null) document.getElementById('godziny5').style.display ='none';
    if(document.getElementById('godziny6') != null) document.getElementById('godziny6').style.display ='none';
    if(document.getElementById('zwolnienie1') != null) document.getElementById('zwolnienie1').style.display ='none';
    if(document.getElementById('zwolnienie2') != null) document.getElementById('zwolnienie2').style.display ='none';
    if(document.getElementById('zwolnienie3') != null) document.getElementById('zwolnienie3').style.display ='none';
    if($rodzaj=='8' || $rodzaj=='14')
    {
        document.getElementById('godziny1').style.display ='table-row';
        document.getElementById('godziny2').style.display ='table-row';
        document.getElementById('godziny3').style.display ='table-row';
        document.getElementById('godziny4').style.display ='table-row';
    }

    if($rodzaj=='9')
    {
        document.getElementById('zwolnienie1').style.display ='table-row';
        document.getElementById('zwolnienie2').style.display ='table-row';
        document.getElementById('zwolnienie3').style.display ='table-row';
    }

    if($rodzaj=='12' || $rodzaj=='13')
    {
        document.getElementById('godziny5').style.display ='table-row';
        document.getElementById('godziny6').style.display ='table-row';
    }
}
function IdCheckbox($rodzaj, $liczba)//zaznaczenie listy pozycji do globalnej akceptacji
{
    var $lista = "";
    var $listahtml = "";
    var $nazwa = "checkbox_";
    var $nazwapola = "";
    if($rodzaj=='wszystko')
    {
        for ($i = 1; $i <= $liczba; $i++)
        {
            if(document.getElementById($nazwa + $i).disabled==false) {
                document.getElementById($nazwa + $i).checked = true
            }
        }
    }

    $jak=0;
        for ($i = 1; $i <= $liczba; $i++)
        {
            if (!document.getElementById($nazwa  + $i))
                break;
            if (document.getElementById($nazwa + $i).checked == true)
            {
                if($jak==0)
                {
                    $lista = document.getElementById($nazwa  + $i).value;
                    $listahtml = document.getElementById($nazwa  + $i).value;
                    $jak=1;
                }
                    else{
                $lista = $lista+','+document.getElementById($nazwa  + $i).value;
                    $listahtml = $listahtml+','+document.getElementById($nazwa  + $i).value;
            }

            }
            if($i%45==0)
            {
                $listahtml=$listahtml+"<br>";
            }
        }
      //  alert(document.getElementById('id_global_kierownik'));
        if(  document.getElementById('id_global_kierownik') != null)
        {
            document.getElementById('id_global_kierownik').value= $lista;
        }
        if(  document.getElementById('id_global_hr') != null)
        {
            document.getElementById('id_global_hr').value= $lista;
        }
        document.getElementById('modyfikacje').innerHTML = $listahtml;
}
function WeryfikacjaZwolnienie()
{
    $tym=0;
    $rodzaj=0;
    for ($i = 8; $i < 15; $i++)
    {
        if(document.getElementById('rodzaj_urlopu_'+$i) != null && document.getElementById('rodzaj_urlopu_'+$i).checked)
        {
            $tym=1;
            $rodzaj=$i;
        }
    }
    if($tym==0)
    {
        alert('Nie wybrano rodzaju Wniosku. Prosze wybrać rodzaj wniosku przed zapisanie wniosku');
    }
    else if(($rodzaj==8 || $rodzaj==14) && document.getElementById('data_odpracowania').value.trim()=='')
    {
        alert('Prosze wybrać przynajmniej jeden dzień w kalendarzu "Dni w których były odpracowane" przed zapisaniem wniosku.');
    }
    else{
        document.forms['form_1'].submit();
    }

}
function WeryfikacjaPracaZdalna()
{
    $tym=0;
    for ($i = 17; $i < 19; $i++)
    {
        if(document.getElementById('rodzaj_urlopu_'+$i) != null && document.getElementById('rodzaj_urlopu_'+$i).checked)
        {
            $tym=1
        }
        if(document.getElementById('miejsce_pracy_zdalnej').value.length<7)
        {
            $tym=2
        }
    }
    if($tym==0)
    {
        alert('Nie wybrano rodzaju Wniosku. Prosze wybrać rodzaj wniosku przed zapisanie wniosku');
    }
    else if($tym==2)
    {
        alert('Nie wpisano miejsca świadczenia pracy zdalnej');
    }
    else{
        document.forms['form_1'].submit();
    }

}

function PobierzDane($id,$pole) {

    if (document.getElementById($id).value.length > 4)
    {

        var $numerewidencyjny = document.getElementById($id).value;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            d_xmlhttp = new XMLHttpRequest();
        } else
        {// code for IE6, IE5
            d_xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        d_xmlhttp.onreadystatechange = function ()
        {
            if (d_xmlhttp.readyState == 4 && d_xmlhttp.status == 200)
            {
                // document.getElementById('test').innerHTML = d_xmlhttp.responseText;
                if (d_xmlhttp.responseText == 'true')
                {
                    document.getElementById($pole).value = d_xmlhttp.responseText;
                    document.getElementById($pole+'1').value = d_xmlhttp.responseText;


                } else
                {
                    document.getElementById($pole).value = d_xmlhttp.responseText;
                    document.getElementById($pole+'1').value = d_xmlhttp.responseText;
                }
            }
        }

        // d_xmlhttp.open("GET","autocomplete/SprawdzenieFaktury.php?dane="+$nip+"*"+$faktura,true);
        d_xmlhttp.open("POST", "autocomplete/Dane.php", true);
        d_xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        d_xmlhttp.send("dane=" + $numerewidencyjny );
    } else
    {
    }
}
function PobierzUrlop($id) {

    if (document.getElementById($id).value.length > 4)
    {
        var $numerewidencyjny = document.getElementById($id).value;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            d_xmlhttp = new XMLHttpRequest();
        } else
        {// code for IE6, IE5
            d_xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        d_xmlhttp.onreadystatechange = function ()
        {
            if (d_xmlhttp.readyState == 4 && d_xmlhttp.status == 200)
            {
                // document.getElementById('test').innerHTML = d_xmlhttp.responseText;
                if (d_xmlhttp.responseText == 'true')
                {
                    $dane= d_xmlhttp.responseText;
                    var tym = $dane.split(";");
                    document.getElementById('imie_nazwisko_stan').innerHTML = tym[0];
                    document.getElementById('stan_urlopu').innerHTML = tym[1];
                   // document.getElementById('stan_urlopu_godziny').innerHTML = tym[5];
                    document.getElementById('stan_urlopu_1').value = tym[1];
                    document.getElementById('urlop_zalegly').innerHTML = tym[3];
                    document.getElementById('stan_godzin').innerHTML = tym[2];
                    document.getElementById('urlop_uz').innerHTML = tym[4];

                } else
                {
                    $dane= d_xmlhttp.responseText;
                    var tym = $dane.split(";");
                     document.getElementById('imie_nazwisko_stan').innerHTML = tym[0];
                     $dlugosc=tym[0].length;

                    document.getElementById('stan_urlopu').innerHTML = tym[1];
                   // document.getElementById('stan_urlopu_godziny').innerHTML = tym[5];
                    document.getElementById('stan_urlopu_1').value = tym[1];
                    document.getElementById('urlop_zalegly').innerHTML = tym[3];
                    document.getElementById('stan_godzin').innerHTML = tym[2];
                    document.getElementById('urlop_uz').innerHTML = tym[4];
                    if($dlugosc>80)
                    {
                        document.getElementById('zlagrupa').value='zle'
                    }
                    else
                    {
                        document.getElementById('zlagrupa').value='dobrze'
                    }
                }
            }
        }
        // d_xmlhttp.open("GET","autocomplete/SprawdzenieFaktury.php?dane="+$nip+"*"+$faktura,true);
        d_xmlhttp.open("POST", "autocomplete/StanUrlopu.php", true);
        d_xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        d_xmlhttp.send("dane=" + $numerewidencyjny );
    } else
    {
    }
}
function PobierzDaneEmail($id,$pole,$poleimie) {

    if (document.getElementById($id).value.length > 4)
    {
        var $numerewidencyjny = document.getElementById($id).value;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            d_xmlhttp = new XMLHttpRequest();
        } else
        {// code for IE6, IE5
            d_xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        d_xmlhttp.onreadystatechange = function ()
        {
            if (d_xmlhttp.readyState == 4 && d_xmlhttp.status == 200)
            {
                // document.getElementById('test').innerHTML = d_xmlhttp.responseText;
                if (d_xmlhttp.responseText == 'true')
                {
                    var tym = d_xmlhttp.responseText.split(";");
                    document.getElementById($pole).value = tym[1];
                    document.getElementById($poleimie).value =  tym[0];
                    document.getElementById($poleimie+'1').value =  tym[0];



                } else
                {
                    var tym = d_xmlhttp.responseText.split(";");
                    document.getElementById($pole).value = tym[1];
                    document.getElementById($poleimie).value =  tym[0];
                    document.getElementById($poleimie+'1').value =  tym[0];
                }
            }
        }
        // d_xmlhttp.open("GET","autocomplete/SprawdzenieFaktury.php?dane="+$nip+"*"+$faktura,true);
        d_xmlhttp.open("POST", "autocomplete/DaneMail.php", true);
        d_xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        d_xmlhttp.send("dane=" + $numerewidencyjny );
    } else
    {
    }
}
function Ajax($plik_php, $widocznosc)
{
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        d_xmlhttp = new XMLHttpRequest();
    } else
    {// code for IE6, IE5
        d_xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    d_xmlhttp.onreadystatechange = function ()
    {
        if (d_xmlhttp.readyState != 4 && d_xmlhttp.status != 200)
        {

        }
    }
    d_xmlhttp.open("POST", $plik_php, true);
    d_xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    d_xmlhttp.send("widocznosc=" + $("#" + $widocznosc).val());
}
function SprawdzZastepstwo() {
    $wartosc=document.getElementById('osoba_wpisz').value;
    $warunek=new RegExp("[^0-9,]+");
    if($warunek.test($wartosc)===false)
    {
        document.forms['zastepca'].submit();
    }
    else
    {
        alert("To pole przyjmuje tylko liczby i znak przecinku. Wprowadź prawidłowe dane.")
    }

}
function Edytuj($id,$numerosoby,$nazwiskoosoby,$numerkierownik,$nazwiskokieronwik,$emailkierownik,$inneosoby) {
    document.getElementById('id_edytuj').value = $id;
    document.getElementById('numer_wpisz').value = $numerosoby;
    document.getElementById('akceptujacy1').value = $nazwiskoosoby;
    document.getElementById('akceptujacy').value = $nazwiskoosoby;
    document.getElementById('numer_ewidencyjny').value = $numerkierownik;
    document.getElementById('imie_nazwisko1').value = $nazwiskokieronwik;
    document.getElementById('imie_nazwisko').value = $nazwiskokieronwik;
    document.getElementById('mail_nowy').value = $emailkierownik;
    if($inneosoby==1)
    {
        document.getElementById('ewnioski_raport_mpk').checked=true;
    }

}
function  SetFocus($id)
{
    document.getElementById($id).focus();
}

function SprawdzZdublowaneWpisy(id_element) {
    imie_nazwisko = $("#imie_nazwisko_"+id_element).val();
    numer_ewidencyjny = imie_nazwisko.split("---");
    data_wykonania_nadgodzin = $("#data_wykonywania_nadgodzin_"+id_element).val();

    var typ_wpisu = $("#typ_wpisu_nadgodzin").val();

    if(imie_nazwisko.length > 0 && data_wykonania_nadgodzin.length > 0) {
        $.post( "../ajax/SprawdzenieCzyOsobaJestJuzUzupelniona.php", { data_wykonania_nadgodzin: data_wykonania_nadgodzin, numer_ewidencyjny: numer_ewidencyjny[2], typ_wpisu: typ_wpisu })
            .done(function( data ) {
                if(data == 'true') {
                    alert("Wystąpił problem Pracownik o numerze ewidencyjnym: "+numer_ewidencyjny[2]+" Ma już wpisane nadgodziny dnia: "+data_wykonania_nadgodzin+"\n Czy na pewno to nie pomyłka?");
                    $("#data_wykonania_nadgodzin_"+id_element).val = "";
                    $("#imie_nazwisko_"+id_element).val = "";
                }
            });
    }
}
function Autopodpowiadanie($id, $plik_php, $dlugosc, $must_match)
{
    $(function() {
        $($id).autocomplete({
            source: $plik_php,
            minLength: $dlugosc,
            change: function(e, ui) {
                if (!ui.item && $must_match == true) {
                    $(this).val("");
                }
            }
        });
    });
}
function PrzekazIdZaznaczonychCheckboxow() {
    var id_list = "";
    $('input:checkbox').each(function(index, element){
        if(element.id != 'global_check' && $('#'+element.id).is(':checked')) {
            id_list += element.value + ',';
        }
    });
    $('#zaznaczenie_ids').val(id_list.substring(0,(id_list.length -1)));
    $('#ilosc_zaznaczonych').html((id_list.split(',').length - 1));
}
function PodpowiadaniePrzyczyna( )
{
    $plikphp='podpowiadanie_poziom_II.php'
    $idzmieniane='dodatkowa_przyczyna1';

    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        d_xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        d_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    d_xmlhttp.open("POST",$plikphp,true);
    d_xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    d_xmlhttp.onreadystatechange=function()
    {
        //alert(d_xmlhttp.readyStat);
        if (d_xmlhttp.readyState==4 && d_xmlhttp.status==200)
        {
            document.getElementById('dodatkowa_przyczyna1').innerHTML = d_xmlhttp.responseText;
        }

        if (d_xmlhttp.readyState==4 && d_xmlhttp.status==200)
        {
            document.getElementById('dodatkowa_przyczyna1').innerHTML = d_xmlhttp.responseText;
            if (typeof FiltrujPowodyPoziomII === 'function') FiltrujPowodyPoziomII();
        }
    }
    d_xmlhttp.send('');

}
function Tym()
{
    var index = document.getElementById( "dodatkowa_przyczyna1" );
    index= index.options[ index.selectedIndex ].value ;
    if(index=='Inne')
    {
        document.getElementById('poziom_drugi_inne').style.display ='table-row';
    }

}
function WeryfikacjaNadgodziny() {
    $select = document.getElementById('uzasadnienie_godzin').value;
    $innypowod=document.getElementById('inny_powod_pole').value.length;

    // --- dodane: wymuszenie wyboru typu wpisu (nadgodziny / odpracowanie godzin) ---
    var typWpisu = document.getElementById('typ_wpisu_nadgodzin').value;
    if (typWpisu == '') {
        alert('Proszę wybrać rodzaj wpisu: "Nadgodziny" lub "Odpracowanie godzin".');
        return;
    }
    // --- koniec ---

    // Walidacja: dla każdego wypełnionego wiersza (ma podanego pracownika)
    // "Data świadczenia pracy" jest zawsze wymagana.
    // "Data odbioru nadgodzin" jest wymagana TYLKO dla typu "odpracowanie_godzin";
    // dla typu "nadgodziny" jest opcjonalna (można, ale nie trzeba).
    for (var i = 0; i < 20; i++) {
        var pracownik = document.getElementById('imie_nazwisko_' + i);
        var dataWykonania = document.getElementById('data_wykonywania_nadgodzin_' + i);
        var dataOdbioru = document.getElementById('data_odbioru_nadgodzin_' + i);

        if (pracownik && pracownik.value.trim() != '') {
            if (dataWykonania && dataWykonania.value.trim() == '') {
                alert('Wiersz nr ' + (i + 1) + ': proszę wybrać "Datę świadczenia pracy w godzinach nadliczbowych".');
                return;
            }
            if (typWpisu == 'odpracowanie_godzin' && dataOdbioru && dataOdbioru.value.trim() == '') {
                alert('Wiersz nr ' + (i + 1) + ': proszę wybrać przynajmniej jeden dzień w polu "Data odbioru nadgodzin lub odpracowanych godzin" (wymagane dla odpracowania godzin).');
                return;
            }
        }
    }

    if($select=='WYBIERZ POWÓD')
    {
        alert('Nie wybrano powodu godzin nadliczbowych');
    }
    else if($select=='Koszt PKC')
    {
        $select2 = document.getElementById('dodatkowa_przyczyna1').value;
        if($select2=='' || $select2 =='Wybierz')
        {
            alert('Nie wybrano powodu kosztu godzin PKC');
        }
        else if($select2=='Inne')
        {
            if($innypowod<5)
            {
                alert('Prosze wypełnić pole z uwagami');
            }
            else{
                document.forms['form_1'].submit();
            }
        }
        else{
            document.forms['form_1'].submit();
        }
    }
    else if($select=='Koszt dostawcy')
    {
        $innypowoddostawca=document.getElementById('dodatkowa_przyczyna_text').value.length;
        if($innypowoddostawca<2)
        {
            alert('Prosze wypełnić pole z uwagami o dostawcy');
        }
        else{
            document.forms['form_1'].submit();
        }
    }
    else if($select=='Koszt klient')
    {
        $innypowoddostawca=document.getElementById('dodatkowa_przyczyna_text').value.length;
        if($innypowoddostawca<2)
        {
            alert('Prosze wypełnić pole z uwagami o kliencie');
        }
        else{
            document.forms['form_1'].submit();
        }
    }
    else if($select=='Koszt innego zakładu PKC')
    {
        $innypowoddostawca=document.getElementById('dodatkowa_przyczyna_text').value.length;
        if($innypowoddostawca<2)
        {
            alert('Prosze wypełnić pole z uwagami o innym zakładzie PKC');
        }
        else{
            document.forms['form_1'].submit();
        }
    }
}
function getDaysDifference($DataPoczatkowa, $DataKoncowa) {
    let date1 = new Date($($DataKoncowa).val());
    let date2 = new Date($($DataPoczatkowa).val());
    let Difference_In_Time = date2.getTime() - date1.getTime();
    let Difference_In_Days = Math.round(Difference_In_Time / (1000 * 3600 * 24));

    if(Difference_In_Days > 31) {
        alert("Błąd, długość pojedyńczego wniosku nie może być dłuższa niż 31 dni!");
        $($DataKoncowa).val('');
        $($DataPoczatkowa).val('');
    }
}
