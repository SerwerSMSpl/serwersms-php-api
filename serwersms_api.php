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
    
    public static function nazwa_nadawcy($Parametry){
        return SerwerSMS::Zapytanie("nazwa_nadawcy",$Parametry);
    }
    
    public static function hlr($Parametry){
        return SerwerSMS::Zapytanie("hlr",$Parametry);
    }
    
    public static function lookup($Parametry){
        return SerwerSMS::Zapytanie("lookup",$Parametry);
    }
    
    public static function kontakty($Parametry){
        return SerwerSMS::Zapytanie("kontakty",$Parametry);
    }
    
    public static function quiz($Parametry){
        return SerwerSMS::Zapytanie("quiz",$Parametry);
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
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);		
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


function PrzetworzXML($akcja,$xml_file,$format) {
	$dom = new domDocument;
	$dom->loadXML($xml_file);
	$xml = simplexml_import_dom($dom);
	
	$ar=array();

	if (isset($xml->Blad)) {	
		$numer = $_POST['numer'];
		$ar["ERROR"]=(string) $xml->Blad[0];
		return $ar;
	}

	if($akcja=="wyslij_sms") {
		$ar["Wiadomosc"]=(string) $xml->Wiadomosc[0];
		if(isset($xml->Odbiorcy->Skolejkowane)){	
			foreach($xml->Odbiorcy->Skolejkowane->SMS as $sms) {
				$ar["Skolejkowane"][]=array("smsid"=>xml_attribute($sms, 'id'),"numer"=>xml_attribute($sms, 'numer'),"godzina_skolejkowania"=>xml_attribute($sms, 'godzina_skolejkowania'));
			}
		} 
		if (isset($xml->Odbiorcy->Niewyslane)) {
			foreach($xml->Odbiorcy->Niewyslane->SMS as $sms) {
				$ar["Niewyslane"][]=array("smsid"=>xml_attribute($sms, 'id'),"numer"=>xml_attribute($sms, 'numer'),"przyczyna"=>xml_attribute($sms, 'przyczyna'));
			}
		}
		return $ar;
	}
	

	if($akcja=="sprawdz_sms") {
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				$ar["SMS"][]=array("smsid"=>xml_attribute($sms, 'id'),"numer"=>xml_attribute($sms, 'numer'),"stan"=>xml_attribute($sms, 'stan'),"przyczyna"=>xml_attribute($sms, 'przyczyna'));
			}
		} 
		return $ar;
	}

	if($akcja=="ilosc_sms") {
		$array =  (array) $xml;
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				$t=xml_attribute($sms, 'typ');
				$ar[$t]=(string) $sms[0];
			}
		} 
		return $ar;
	}
	
	if($akcja=="sprawdz_odpowiedzi") { 
		$lp=0;
		if(isset($xml->SMS)){	
			foreach($xml->SMS as $sms) {
				$ar["SMS"][$lp]=array("id"=>xml_attribute($sms, 'id'),"numer"=>xml_attribute($sms, 'numer'),"data"=>xml_attribute($sms, 'data'),"tresc"=>xml_attribute($sms, 'tresc'),"na_numer"=>xml_attribute($sms, 'na_numer'));
				$lp++;
			}
		}
		if(isset($xml->MMS)){
			foreach($xml->MMS as $mms){
				$ar["SMS"][$lp]=array("id"=>xml_attribute($mms, 'id'),"numer"=>xml_attribute($mms, 'numer'),"data"=>xml_attribute($mms, 'data'),"temat"=>xml_attribute($mms, 'temat'));
				if(isset($xml->MMS->Zalacznik)){
					foreach($xml->MMS->Zalacznik as $zalacznik){
						$ar["SMS"][$lp]["Zalaczniki"][]=array("id"=>xml_attribute($mms, 'id'),"zid"=>xml_attribute($zalacznik, 'id'),"nazwa"=>xml_attribute($zalacznik, 'nazwa'),"contenttype"=>xml_attribute($zalacznik, 'contenttype'),"zalacznik"=>(string) $zalacznik[0]);
					}
				}
				$lp++;
			}
		}
		return $ar;
	}
        
	if($akcja=="pliki") {
		if(isset($xml->Plik)){
			foreach($xml->Plik as $plik){
				$ar["PLIK"][]=array("id"=>xml_attribute($plik, 'id'),"nazwa"=>(string) $plik->Nazwa[0],"rozmiar"=>(string) $plik->Rozmiar[0],"typ"=>(string) $plik->Typ[0],"data"=>(string) $plik->Data[0]);
			}
		}
		return $ar;
	}
        
	if($akcja=="premium_api"){
		if(isset($xml->SMS) and $xml->SMS == "OK"){
			$ar["SMS"][]=array("id"=>xml_attribute((string) $xml->SMS[0],'id'));
		} elseif(isset($xml->SMS)){
			foreach($xml->SMS as $sms){
				$ar["SMS"][]=array("id"=>xml_attribute($sms, 'id'),"na_numer"=>xml_attribute($sms, 'na_numer'),"z_numeru"=>xml_attribute($sms, 'z_numeru'),"data"=>xml_attribute($sms, 'data'),"limit"=>xml_attribute($sms, 'limit'),"wiadomosc"=>(string) $sms[0]);
			}
		}
		return $ar;
	}
        
	if($akcja=="usun_zaplanowane"){
		if(isset($xml->ZAPLANOWANE)){
			foreach($xml->ZAPLANOWANE as $zaplanowane){
				if($zaplanowane == "OK"){
					$ar["SMS"][]=array("smsid"=>xml_attribute($zaplanowane,'smsid'),"stan"=>"OK");
				} 
				if($zaplanowane == "ERR"){
					$ar["SMS"][]=array("smsid"=>xml_attribute($zaplanowane,'smsid'),"stan"=>"ERR");
				}
			}
		}
		return $ar;
	}
        
        
	if($akcja=="nazwa_nadawcy"){
		if(isset($xml->NADAWCA)){
			foreach($xml->NADAWCA as $nadawca){
				$ar["NAZWA"][]=array("nazwa"=>xml_attribute($nadawca, 'nazwa'),"status"=>(string) $nadawca[0]);
			}
		}
		return $ar;
	}
        
	if($akcja=="hlr"){
		if(isset($xml->NUMER)){
			$ar["HLR"][]=array("numer"=>xml_attribute($xml->NUMER,'numer'),"status"=>(string) $xml->NUMER->status[0],"imsi"=>(string) $xml->NUMER->imsi[0],"siec_macierzysta"=>(string) $xml->NUMER->siec_macierzysta[0],"przenoszony"=>(string) $xml->NUMER->przenoszony[0],"siec_obecna"=>(string) $xml->NUMER->siec_obecna[0]);
		}
		return $ar;
	}
        
	if($akcja=="lookup"){
		if(isset($xml->NUMER)){
			$ar["LOOKUP"][]=array("numer"=>xml_attribute($xml->NUMER,'numer'),"status"=>(string) $xml->NUMER->status[0],"imsi"=>(string) $xml->NUMER->imsi[0],"siec_macierzysta"=>(string) $xml->NUMER->siec_macierzysta[0],"przenoszony"=>(string) $xml->NUMER->przenoszony[0],"siec_obecna"=>(string) $xml->NUMER->siec_obecna[0]);
		}
		return $ar;
	}
        
	if($akcja=="kontakty"){
		if(isset($xml->GRUPA->KONTAKT)){
			if(isset($xml->GRUPA->NAZWA)){
				$ar["GRUPA"][]=array("id"=>xml_attribute($xml->GRUPA,'id'),"nazwa"=>(string) $xml->GRUPA->NAZWA[0],"ilosc"=>xml_attribute($xml->GRUPA,'ilosc'));
			}
			foreach($xml->GRUPA->KONTAKT as $kontakt){
				if(isset($kontakt)){
					$ar["KONTAKT"][]=array("id"=>xml_attribute($kontakt,'id'),"numer"=>(string) $kontakt->TELEFON[0],"email"=>(string) $kontakt->EMAIL[0],"firma"=>(string) $kontakt->FIRMA[0],"imie"=>(string) $kontakt->IMIE[0],"nazwisko"=>(string) $kontakt->NAZWISKO[0]);
				}
			}
		} elseif (isset($xml->GRUPA->NAZWA)) {
			foreach($xml as $grupy){
				if(isset($grupy)){
					$ar["GRUPA"][]=array("id"=>xml_attribute($grupy,'id'),"nazwa"=>(string) $grupy->NAZWA[0],"ilosc"=>xml_attribute($grupy,'ilosc'));
				}
			}
		} elseif (isset($xml->GRUPA)){
			$ar["GRUPA"][]=array("id"=>xml_attribute($xml->GRUPA,'id'),"stan"=>(string) $xml->GRUPA[0]);
		} elseif (isset($xml->KONTAKT)){
			$ar["KONTAKT"][]=array("id"=>xml_attribute($xml->KONTAKT,'id'),"stan"=>(string) $xml->KONTAKT[0]);
		}
	}

	if($akcja=="quiz"){
		if(isset($xml->QUIZ)){
			$ar[xml_attribute($xml->QUIZ, 'id')]=array("nazwa"=>xml_attribute($xml->QUIZ, 'nazwa'));
			foreach($xml->QUIZ->POZYCJA as $poz){
				$ar[xml_attribute($xml->QUIZ, 'id')][xml_attribute($poz, 'id')]=(string) $poz[0];
			}
		}
		return $ar;
	}
        
	return $ar;
}



//header('Content-type: text/xml; charset=utf-8');
//header('Content-type: text/plain; charset=utf-8');

//-------------------- wysyłka wiadomości -----------------------------------//
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800,sdf989dsf,999", wiadomosc => "Test wiadomosci ECO", test => 0)); //ECO
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Test wiadomosci FULL", nadawca => "INFORMACJA", test => 0)); //FULL
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => iconv("UTF-8","ISO-8859-2","Test wiadomości głosowej"), glosowy => 1, test => 0)); //VOICE (syntezator), tekst w kodowaniu ISO-8859-2
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", plikwav => "8157049208", glosowy => 1, test => 0)); //VOICE (plik wav)
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Temat MMSa do 40 znakow", mms => 1, plikmms => "708e4e2d1z,0d9f3f6dsd", test => 0)); //MMS
//print_r(PrzetworzXML("wyslij_sms",$xml));

//-------------- sprawdzanie raportów doręczenia ---------------//
//$xml = SerwerSMS::sprawdz_sms();
//print_r(PrzetworzXML("sprawdz_sms",$xml));

//-------------- sprawdzanie dostępnej ilości wiadomości ---------------//
//$xml = SerwerSMS::ilosc_sms(array());
//print_r(PrzetworzXML("ilosc_sms",$xml));

//-------------- pobieranie wiadomości przychodzących ---------------//
//$xml = SerwerSMS::sprawdz_odpowiedzi(array()); //Wszystkie
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 1)); //odpowiedzi SMS ECO
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 2)); //Numer Dostępowy
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 3)); //Numer Dostępowy Indywidualny
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 4)); // Premium SMS
//$xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 5)); // Odbiór MMS
//print_r(PrzetworzXML("sprawdz_odpowiedzi",$xml));


//Wiadomość spersonalizowana
//$xml = SerwerSMS::wyslij_sms(array(spersonalizowane => "500600700:Wiadomosc spersonalizowana 1]|[600700800:Wiadomosc spersonalizowana 2]", test => 1)); // SMS spersonalizowany
//Własne identyfikatory wiadomości
//$xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Wiadomość testowa", usmsid => "123abc1, 123abc2", test => 0)); // Własne identyfikatory wiadomości
//print_r(PrzetworzXML("wyslij_sms",$xml));

//Pliki
//$xml = SerwerSMS::pliki(array(url_mms => $_FILES['mms']['tmp-name'])); // Wgrywanie pliku MMS
//$xml = SerwerSMS::pliki(array(lista => "mms")); // Listowanie plików MMS
//$xml = SerwerSMS::pliki(array(url_voice => "http://www.serwer.pl/kat/plik.wav")); // Wgrywanie pliku WAV
//$xml = SerwerSMS::pliki(array(lista => "voice")); // Listowanie plików VOICE
//print_r(PrzetworzXML("pliki",$xml));

//Premium API
//$xml = SerwerSMS::premium_api(array(operacja => "lista", test => 0)); // Lista wiadomości PREMIUM
//$xml = SerwerSMS::premium_api(array(operacja => "wyslij_sms", idsms => "111111111", numer => "500600700", bramka => "71200", wiadomosc => "testowa odpowiedz premium sms")); // Wysyłanie odpowiedzi PREMIUM
//print_r(PrzetworzXML("premium_api",$xml));

//Usuń zaplanowane
//$xml = SerwerSMS::usun_zaplanowane(array(smsid => "89df6g875sf,025701861e")); // Usuwanie zaplanowanych wysyłek
//print_r(PrzetworzXML("usun_zaplanowane",$xml));

//Nazwa nadawcy
//$xml = SerwerSMS::nazwa_nadawcy(array(operacja => "dodanie", nazwa => "SerwerSMS")); // Dodanie nazwy nadawcy
//$xml = SerwerSMS::nazwa_nadawcy(array(operacja => "lista")); // Listowanie nazw nadawcy
//print_r(PrzetworzXML("nazwa_nadawcy",$xml));

//HLR
//$xml = SerwerSMS::hlr(array(numer => "500600700"));
//print_r(PrzetworzXML("hlr",$xml));

//LOOKUP
//$xml = SerwerSMS::lookup(array(numer => "500600700"));
//print_r(PrzetworzXML("lookup",$xml));

//Premium quiz
//$xml = SerwerSMS::quiz(array(quiz => 100));
//print_r(PrzetworzXML("quiz",$xml));

//Kontakty
//$xml = SerwerSMS::kontakty(array(operacja => "lista_grup")); // lista grup
//$xml = SerwerSMS::kontakty(array(operacja => "lista_kontaktow", grupa => "nieprzypisane")); // lista kontaktów dla grupy
//$xml = SerwerSMS::kontakty(array(operacja => "lista_kontaktow", grupa => "17310254")); // lista kontaktów dla grupy
//$xml = SerwerSMS::kontakty(array(operacja => "dodaj_grupe", grupa => "do_usuniecia")); // dodawanie nowej grupy
//$xml = SerwerSMS::kontakty(array(operacja => "dodaj_kontakt", dane => "17310747:500600700:adres@email.com:Imie:Nazwisko:Firma")); // dodawanie nowego kontaktu do grupy
//$xml = SerwerSMS::kontakty(array(operacja => "usun_grupe", grupa => "17310747")); // usuwanie grupy
//$xml = SerwerSMS::kontakty(array(operacja => "usun_kontakt", kontakt => "500600700", grupa => 17310747)); // usuwanie kontaktu z grupy
//print_r(PrzetworzXML("kontakty",$xml));

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
