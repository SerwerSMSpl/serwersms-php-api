<?
class SerwerSMS {
	//----------------------------------------------------------------------------------------------------------------------------------------//
	// uwaga, zaleca się utworzenie osobnego loginu i hasła do komunikacji przez WebAPI.
	// Nowego użytkownika HTTPS XML API można utworzyć z poziomu Panelu Klienta -> Wysyłka wiad. -> HTTPS XML API -> Użytkownicy HTTPS XML API
	//----------------------------------------------------------------------------------------------------------------------------------------//
    public static $DaneKonta =array
    (
        'login' => 'TwojLogin',
        'haslo' => 'TwojeHaslo'
    );

    public static $API_URL = 'https://api1.serwersms.pl/zdalnie/';

    public static function wyslij_sms($Parametry) {
        return SerwerSMS::Zapytanie("wyslij_sms", $Parametry);
    }

    public static function sprawdz_sms($Parametry) {
        return SerwerSMS::Zapytanie("sprawdz_sms", $Parametry);
    }

    public static function ilosc_sms($Parametry) {
        return SerwerSMS::Zapytanie("ilosc_sms", $Parametry);
    }

    public static function sprawdz_odpowiedzi($Parametry) {
        return SerwerSMS::Zapytanie("sprawdz_odpowiedzi", $Parametry);
    }
    
    public static function pliki($Parametry) {
        return SerwerSMS::Zapytanie("pliki",$Parametry);
    }
    
    public static function premium_api($Parametry) {
        return SerwerSMS::Zapytanie("premium_api",$Parametry);
    }
    
    public static function usun_zaplanowane($Parametry) {
        return SerwerSMS::Zapytanie("usun_zaplanowane",$Parametry);
    }
    
    public static function pobierz_mms($Parametry){
        return SerwerSMS::Zapytanie("pobierz_mms",$Parametry);
    }
    
    public static function nazwa_nadawcy($Parametry){
        return SerwerSMS::Zapytanie("nazwa_nadawcy",$Parametry);
    }
    
    public static function hlr($Parametry){
        return SerwerSMS::Zapytanie("hlr",$Parametry);
    }
    
    public static function kontakty($Parametry){
        return SerwerSMS::Zapytanie("kontakty",$Parametry);
    }
    
    public static function mms_z_dysku($plik){
        if(is_uploaded_file($plik['tmp_name'])){
            
            $f = file_get_contents($plik['tmp_name']);
            
            return SerwerSMS::pliki(array(plik_mms => $f));

        } else {
            return false;
        }
    }

    private static function Zapytanie($akcja, $params) {

        $requestUrl = SerwerSMS::$API_URL;
		$params["akcja"] = $akcja;
        $postParams = array_merge(SerwerSMS::$DaneKonta, $params);

        $curl = curl_init($requestUrl);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postParams));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($curl,CURLOPT_TIMEOUT,60); 
        $answer = curl_exec($curl);
		if (curl_errno($curl)) {
			die('<pre style="color:red">'.curl_error($curl).':'.curl_errno($curl).'</pre>');
			exit();
		}
        curl_close($curl);

        return $answer;
    }
}

function xml_attribute($object, $attribute)
{
	if(isset($object[$attribute]))
	return (string) $object[$attribute];
}


function PrzetworzXML($akcja,$xml_file) {
	$dom = new domDocument;
	$dom->loadXML($xml_file);
	$xml = simplexml_import_dom($dom);
	
	if (isset($xml->Blad)) {	
		$numer = $_POST['numer'];
		$przyczyna = $xml->Blad;
		echo 'Błąd ogólny: '.$przyczyna;
	}

	if($akcja=="wyslij_sms") {
		if(isset($xml->Odbiorcy->Skolejkowane)){	
			foreach($xml->Odbiorcy->Skolejkowane->SMS as $sms) {
				echo '
				Zapis wysłanych do bazy - smsid: '.xml_attribute($sms, 'id').'; numer: '.xml_attribute($sms, 'numer').'; godzina_skolejkowania: '.xml_attribute($sms, 'godzina_skolejkowania');
			}
		} 
		if (isset($xml->Odbiorcy->Niewyslane)) {
			foreach($xml->Odbiorcy->Niewyslane->SMS as $sms) {
				echo '
				Zapis niewysłanych do bazy - smsid: '.xml_attribute($sms, 'id').'; numer: '.xml_attribute($sms, 'numer').'; przyczyna: '.xml_attribute($sms, 'przyczyna');
			}
		}
	}
	
	if($akcja=="sprawdz_sms") {
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				echo '
				Sprawdzanie statusów - smsid: '.xml_attribute($sms, 'id').'; numer: '.xml_attribute($sms, 'numer').'; stan: '.xml_attribute($sms, 'stan').'; przyczyna: '.xml_attribute($sms, 'przyczyna');
			}
		} 
	}

	if($akcja=="ilosc_sms") {
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				echo '
				Sprawdzanie limitów - typ: '.xml_attribute($sms, 'typ').'; limit: '.$sms;
			}
		} 
	}
	
	if($akcja=="sprawdz_odpowiedzi") {
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				echo '
				Wiadomość przychodząca - id: '.xml_attribute($sms, 'id').'; numer: '.xml_attribute($sms, 'numer').'; data: '.xml_attribute($sms, 'data').'; tresc: '.xml_attribute($sms, 'tresc').'; na numer: '.xml_attribute($sms, 'na_numer');
			}
		}
                if(isset($xml->MMS)){
                    foreach($xml->MMS as $mms){
                        echo'
                        Wiadomość MMS - id: '.xml_attribute($mms, 'id').'; numer: '.xml_attribute($mms, 'numer').'; data: '.xml_attribute($mms, 'data').'; temat: '.xml_attribute($mms, 'temat');
                        if(isset($xml->MMS->Zalacznik)){
                            foreach($xml->MMS->Zalacznik as $zalacznik){
                                echo '
                                Załącznik - id: '.xml_attribute($zalacznik, 'id').'; nazwa: '.xml_attribute($zalacznik,'nazwa').'; contenttype: '.xml_attribute($zalacznik,'contenttype').'; zawartość: '.$zalacznik;
                            }
                        }
                    }
                }
	}
        
        if($akcja=="pliki") {
            if(isset($xml->Plik)){
                foreach($xml->Plik as $plik){
                    echo '
                    Plik - id: '.xml_attribute($plik, 'id').'; nazwa: '.$plik->Nazwa.'; rozmiar: '.$plik->Rozmiar.'; typ: '.$plik->Typ.'; data: '.$plik->Data;

                }
            }
        }
        
        if($akcja=="premium_api"){
            if(isset($xml->SMS) and $xml->SMS == "OK"){
                echo '
                    Odpowiedź wysłana - id: '.xml_attribute($xml->SMS,'id');
                
            }elseif(isset($xml->SMS)){
                foreach($xml->SMS as $sms){
                    echo '
                    Wiadomość: '.xml_attribute($sms, 'id').'; na numer: '.xml_attribute($sms, 'na_numer').'; z numeru: '.xml_attribute($sms, 'z_numeru').'; data: '.xml_attribute($sms, 'data').'; limit: '.xml_attribute($sms, 'limit').'; tekst: '.$sms;
                }
            }
        }
        
        if($akcja=="usun_zaplanowane"){
            if(isset($xml->ZAPLANOWANE)){
                foreach($xml->ZAPLANOWANE as $zaplanowane){
                    if($zaplanowane == "OK"){
                        echo '
                            Usunięto sms - smsid:'.xml_attribute($zaplanowane,'smsid');;
                    } 
                    if($zaplanowane == "ERR"){
                        echo '
                            Nie znaleziono wiadomości - smsid:'.xml_attribute($zaplanowane,'smsid');
                    }
                }
            }
        }
        
        if($akcja=="pobierz_mms"){
            if(isset($xml->MMS)){
                foreach($xml->MMS as $mms){
                    echo'
                    Wiadomość MMS - id: '.xml_attribute($mms, 'id').'; numer: '.xml_attribute($mms, 'numer').'; data: '.xml_attribute($mms, 'data');
                    if(isset($mms->Zalacznik)){
                        foreach($mms->Zalacznik as $zalacznik){
                            echo '
                            Załącznik - id: '.xml_attribute($zalacznik, 'id').'; nazwa: '.xml_attribute($zalacznik,'nazwa').'; contenttype: '.xml_attribute($zalacznik,'contenttype').'; zawartość: '.$zalacznik;
                        }
                    }
                }
            }
        }
        
        if($akcja=="nazwa_nadawcy"){
            if(isset($xml->NADAWCA)){
                foreach($xml->NADAWCA as $nadawca){
                    echo '
                    Nadawca - nazwa: '.xml_attribute($nadawca,'nazwa').'; status: '.$nadawca;
                }
            }
        }
        
        if($akcja=="hlr"){
            if(isset($xml->NUMER)){
                echo'
                Numer: '.xml_attribute($xml->NUMER,'numer').'; status: '.$xml->NUMER->status.'; imsi: '.$xml->NUMER->imsi.'; sieć macierzysta: '.$xml->NUMER->siec_macierzysta.'; przenoszony: '.$xml->NUMER->przenoszony.'; sieć obecna: '.$xml->NUMER->siec_obecna;
            }
        }
        
        if($akcja=="kontakty"){
            if(isset($xml->GRUPA->KONTAKT)){
                if(isset($xml->GRUPA->NAZWA)){
                        echo '
                        Nazwa grupy: '.$xml->GRUPA->NAZWA.'; ID grupy: '.xml_attribute($xml->GRUPA,'id').'; liczba kontaktów: '.xml_attribute($xml->GRUPA,'ilosc');
                    }
                foreach($xml->GRUPA->KONTAKT as $kontakt){
                    if(isset($kontakt)){
                        echo'
                        ID kontaktu: '.xml_attribute($kontakt,'id').'; Telefon: '.$kontakt->TELEFON.'; E-mail: '.$kontakt->EMAIL.'; Firma: '.$kontakt->FIRMA.'; Imie: '.$kontakt->IMIE.'; Nazwisko: '.$kontakt->NAZWISKO;
                    }
                }
            } elseif (isset($xml->GRUPA->NAZWA)) {
                foreach($xml as $grupy){
                    if(isset($grupy)){
                        echo '
                        Nazwa grupy: '.$grupy->NAZWA.'; ID grupy: '.xml_attribute($grupy,'id').'; liczba kontaktów: '.xml_attribute($grupy,'ilosc');
                    }
                }
            } elseif (isset($xml->GRUPA)){
                echo '
                ID grupy: '.xml_attribute($xml->GRUPA,'id').'; Stan: '.$xml->GRUPA;
                
            } elseif (isset($xml->KONTAKT)){
                echo '
                ID kontaktu: '.xml_attribute($xml->KONTAKT,'id').'; Stan: '.$xml->KONTAKT;
            }
        }

}



//header('Content-type: text/xml; charset=utf-8');
//header('Content-type: text/plain; charset=utf-8');

//-------------------- wysyłka wiadomości -----------------------------------//
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Test wiadomosci ECO", test => 0)); //ECO
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Test wiadomosci FULL", nadawca => "INFORMACJA", test => 0)); //FULL
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => iconv("UTF-8","ISO-8859-2","Test wiadomości głosowej"), glosowy => 1, test => 0)); //VOICE (syntezator), tekst w kodowaniu ISO-8859-2
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", plikwav => "8157049208", glosowy => 1, test => 0)); //VOICE (plik wav)
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Temat MMSa do 40 znakow", mms => 1, plikmms => "708e4e2d1z,0d9f3f6dsd", test => 0)); //MMS

//-------------- sprawdzanie raportów doręczenia ---------------//
//$xml = SerwerSMS::sprawdz_sms(array(smsid => "ec41779ddf,215ab260df,8882bf9332"));
//PrzetworzXML("sprawdz_sms",$xml);

//-------------- sprawdzanie dostępnej ilości wiadomości ---------------//
//$xml = SerwerSMS::ilosc_sms(array());
//PrzetworzXML("ilosc_sms",$xml);

//-------------- pobieranie wiadomości przychodzących ---------------//
//$xml = SerwerSMS::sprawdz_odpowiedzi(array()); //Wszystkie
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 1)); //odpowiedzi SMS ECO
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 2)); //Numer Dostępowy
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 3)); //Numer Dostępowy Indywidualny
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 4)); // Premium SMS
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 5)); // Odbiór MMS
//PrzetworzXML("sprawdz_odpowiedzi",$xml);


//Wiadomość spersonalizowana
//$xml = SerwerSMS::wyslij_sms(array(spersonalizowane => "500600700:Wiadomosc spersonalizowana 1]|[600700800:Wiadomosc spersonalizowana 2]", test => 0)); // SMS spersonalizowany
//Własne identyfikatory wiadomości
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Wiadomość testowa", usmsid => "123abc1, 123abc2", test => 0)); // Własne identyfikatory wiadomości
//PrzetworzXML("wyslij_sms",$xml);

//Pliki
//$xml = SerwerSMS::pliki(array(url_mms => $_FILES['mms']['tmp-name'])); // Wgrywanie pliku MMS
//$xml = SerwerSMS::pliki(array(lista => "mms")); // Listowanie plików MMS
//$xml = SerwerSMS::pliki(array(url_voice => "http://www.serwer.pl/kat/plik.wav")); // Wgrywanie pliku WAV
//$xml = SerwerSMS::pliki(array(lista => "voice")); // Listowanie plików VOICE
//PrzetworzXML("pliki",$xml);

//Premium API
//$xml = SerwerSMS::premium_api(array(operacja => "lista", test => 0)); // Lista wiadomości PREMIUM
//$xml = SerwerSMS::premium_api(array(operacja => "wyslij_sms", idsms => "21544", numer => "500600700", bramka => "71160")); // Wysyłanie odpowiedzi PREMIUM
//PrzetworzXML("premium_api",$xml);

//Usuń zaplanowane
//$xml = SerwerSMS::usun_zaplanowane(array(smsid => "89df6g875sf,025701861e")); // Usuwanie zaplanowanych wysyłek
//PrzetworzXML("usun_zaplanowane",$xml);

//Pobierz MMS
//$xml = SerwerSMS::pobierz_mms(array(numer => "500600700")); // Pobieranie wiadomości MMS
//PrzetworzXML("pobierz_mms",$xml);

//Nazwa nadawcy
//$xml = SerwerSMS::nazwa_nadawcy(array(operacja => "dodanie", nazwa => "SerwerSMS")); // Dodanie nazwy nadawcy
//$xml = SerwerSMS::nazwa_nadawcy(array(operacja => "lista")); // Listowanie nazw nadawcy
//PrzetworzXML("nazwa_nadawcy",$xml);

//HLR
//$xml = SerwerSMS::hlr(array(numer => "600530544"));
//PrzetworzXML("hlr",$xml);

//Kontakty
//$xml = SerwerSMS::kontakty(array(operacja => "lista_grup")); // lista grup
//$xml = SerwerSMS::kontakty(array(operacja => "lista_kontaktow", grupa => "nieprzypisane")); // lista kontaktów dla grupy
//$xml = SerwerSMS::kontakty(array(operacja => "dodaj_grupe", grupa => "do_usuniecia")); // dodawanie nowej grupy
//$xml = SerwerSMS::kontakty(array(operacja => "dodaj_kontakt", dane => "17256600:500600700:adres@email.com:Imie:Nazwisko:Firma")); // dodawanie nowego kontaktu do grupy
//$xml = SerwerSMS::kontakty(array(operacja => "usun_grupe", grupa => "00000000")); // usuwanie grupy
//$xml = SerwerSMS::kontakty(array(operacja => "usun_kontakt", kontakt => "00000000")); // usuwanie kontaktu z grupy
//PrzetworzXML("kontakty",$xml);

// Plik MMS z dysku
/*
$formularz = <<< PL
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="mms" />
        <input type="submit" value="Wyślij plik" />
    </form>
PL;

if(isset($_FILES['mms'])){
    $xml = SerwerSMS::mms_z_dysku($_FILES['mms']);
    PrzetworzXML("pliki",$xml);
} else {
    echo $formularz;
}
*/
?>
