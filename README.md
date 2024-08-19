#serwersms-php-api

SerwerSMS.pl umożliwia wysyłanie wiadomości przy pomocy Panelu Klienta oraz dostępnych tam funkcji jak również przy pomocy tzw. zdalnej obsługi. Dzięki drugiej z wymienionych metod możliwe jest wysyłanie oraz sprawdzanie poprawności wysłanych wiadomości jak również dostęp do innych funkcji bez konieczności logowania się do Panelu Klienta.

Komunikacja z SerwerSMS.pl odbywa się poprzez wywołanie adresu URL metodą GET lub POST z odpowiednimi parametrami. Zalecane jest połączenie szyfrowane SSL (https). Jako odpowiedź zwracany jest dokument w formacie XML informujący o wyniku wywołanej akcji. 

Maksymalna wielkość pojedynczego zgłoszenia do wysyłki wiadomości to 100.000 numerów. Zalecane jest przesyłanie mniejszych porcji danych np. 1000-500 numerów w jednym zgłoszeniu. W przypadku gdy w pojedynczym zgłoszeniu zostanie przesłanych więcej numerów lub wiadomości spersonalizowanych (numer oraz wiadomość) wygenerowany zostanie błąd ogólny a wiadomości nie zostaną wysłane.

Usługa zdalnej obsługi przez HTTPS XML API umożliwia również wysyłanie informacji o raportach doręczeń oraz odpowiedziach SMS wprost na wskazany adres URL Abonenta. Aby SerwerSMS.pl wysłał automatycznie informacje o raportach doręczeń do Abonenta, należy w Panelu Klienta ustawić odpowiednie opcje w zakładce Ustawienia interfejsów (HTTPS XML API lub ustawienia w odpowiedniej sekcji np. ND/NDI). Więcej informacji na ten temat znajduje się w dokumentacji: http://dev.serwersms.pl

Zalecane jest, aby komunikacja przez HTTPS XML API odbywała się z loginów utworzonych specjalnie do połączenia przez API. Konto użytkownika API można utworzyć w Panelu Klienta → Ustawienia interfejsów → HTTP API → Użytkownicy API.

Należy również pamiętać o formacie podawanych numerów telefonów. Każdy numer powinien być w formacie międzynarodowym np. w przypadku numerów polskich sieci komórkowych jest to +48500600700. Analogicznie jeśli numer jest z sieci innego kraju należy poprzedzić go numerem kierunkowym. Numery Polskie nie posiadające prefiksu +48 będą automatycznie korygowane, natomiast numery zagraniczne muszą posiadać pełny prefiks międzynarodowy poprzedzony znakiem „+” (plus). Ponadto należy zwrócić uwagę na długość adresu przesyłanego metodą GET gdyż w przypadku przesyłania większej ilości danych może nastąpić przekroczenie dozwolonych 255 znaków. W takim przypadku prosimy o przesyłanie danych metodą POST.

Wychodząc naprzeciw oczekiwaniom naszych obecnych oraz przyszłych Klientów, udostępniamy możliwość sprawdzania i testowania usługi zdalnej obsługi przez HTTPS XML API dla osób nie posiadających jeszcze kont w SerwerSMS.pl. Aby skorzystać z konta testowego należy logować się na następujące dane:

Login: demo
Hasło: demo

Adres, na który należy wysyłać zapytania do HTTPS XML API to:

https://api1.serwersms.pl/zdalnie/index.php

Zapytania które w przypadku normalnego konta wysyłają wiadomości, w tym przypadku jedynie generują zwrot w postaci dokumentu XML (identycznie jak w przypadku parametru „test=1”). Informacje zwrotne są identyczne jak w przypadku standardowego wysyłania wiadomości . Aby dokładnie sprawdzić raporty doręczenia oraz odczytywanie wiadomości zwrotnych wysłane zostały dwa SMS-y oraz jedna odpowiedź SMS. W zwrocie otrzymano następujące dokumenty XML:

Wysłany SMS 1:

    <?xml version="1.0" encoding="UTF-8"?>
    <SerwerSMS login="demo">
     <Wiadomosc>To jest wiadomosc testowa z serwersms.pl</Wiadomosc>
     <Odbiorcy>
      <Skolejkowane>
      <SMS id="5f5d1b1d97" numer="+48500600700" godzina_skolejkowania="2008-08-08 12:42:19"/>
     </Skolejkowane>
     </Odbiorcy>
    </SerwerSMS>

Wysłany SMS 2:

    <?xml version="1.0" encoding="UTF-8"?>
    <SerwerSMS login="demo">
     <Wiadomosc>I jeszcze jedna wiadomosc testowa z serwersms.pl</Wiadomosc>
     <Odbiorcy>
      <Skolejkowane>
       <SMS id="1614f32c34" numer="+48783820099" godzina_skolejkowania="2008-08-08 12:43:23"/>
      </Skolejkowane>
     </Odbiorcy>
    </SerwerSMS>

Odpowiedź na SMS ECO:

    <?xml version="1.0" encoding="UTF-8"?>
    <SerwerSMS login="demo">
     <SMS id="ECO45345" numer="+48783820099" data="2008-08-08 12:44:17" tresc="Dziekuje za ta informacje. Pozdrawiam"/>
    </SerwerSMS>

Na podstawie powyższych informacji można z powodzeniem przetestować oraz wdrożyć zdalną obsługę do własnego oprogramowania przez co sam proces integracji po skorzystaniu z oferty SerwerSMS.pl będzie krótszy i pewniejszy.


#Relizacja zapytań za pomocą przykładowego skryptu php api:

    header('Content-type: text/plain; charset=utf-8');
    
    //-------------------- wysyłka wiadomości -----------------------------------//
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800,sdf989dsf,999", wiadomosc => "Test wiadomosci ECO", test => 0)); //ECO
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Test wiadomosci FULL", nadawca => "INFORMACJA", test => 0)); //FULL
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => iconv("UTF-8","ISO-8859-2","Test wiadomości głosowej"), glosowy => 1, test => 0)); //VOICE (syntezator), tekst w kodowaniu ISO-8859-2
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", plikwav => "8157049208", glosowy => 1, test => 0)); //VOICE (plik wav)
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Temat MMSa do 40 znakow", mms => 1, plikmms => "708e4e2d1z,0d9f3f6dsd", test => 0)); //MMS
    print_r(PrzetworzXML("wyslij_sms",$xml));
    
    //-------------- sprawdzanie raportów doręczenia ---------------//
    $xml = SerwerSMS::sprawdz_sms();
    print_r(PrzetworzXML("sprawdz_sms",$xml));
    
    //-------------- sprawdzanie dostępnej ilości wiadomości ---------------//
    $xml = SerwerSMS::ilosc_sms(array());
    print_r(PrzetworzXML("ilosc_sms",$xml));
    
    //-------------- pobieranie wiadomości przychodzących ---------------//
    $xml = SerwerSMS::sprawdz_odpowiedzi(array()); //Wszystkie
    $xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 1)); //odpowiedzi SMS ECO
    $xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 2)); //Numer Dostępowy
    $xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 3)); //Numer Dostępowy Indywidualny
    $xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 4)); // Premium SMS
    $xml = SerwerSMS::sprawdz_odpowiedzi(array(typ => 5)); // Odbiór MMS
    print_r(PrzetworzXML("sprawdz_odpowiedzi",$xml));
    
    //Wiadomość spersonalizowana
    $xml = SerwerSMS::wyslij_sms(array(spersonalizowane => "500600700:Wiadomosc spersonalizowana 1]|[600700800:Wiadomosc spersonalizowana 2]", test => 1)); // SMS spersonalizowany
    
    //Własne identyfikatory wiadomości
    $xml = SerwerSMS::wyslij_sms(array(numer => "500600700,600700800", wiadomosc => "Wiadomość testowa", usmsid => "123abc1, 123abc2", test => 0)); // Własne identyfikatory wiadomości
    print_r(PrzetworzXML("wyslij_sms",$xml));
    
    //Pliki
    $xml = SerwerSMS::pliki(array(url_mms => $_FILES['mms']['tmp-name'])); // Wgrywanie pliku MMS
    $xml = SerwerSMS::pliki(array(lista => "mms")); // Listowanie plików MMS
    $xml = SerwerSMS::pliki(array(url_voice => "http://www.serwer.pl/kat/plik.wav")); // Wgrywanie pliku WAV
    $xml = SerwerSMS::pliki(array(lista => "voice")); // Listowanie plików VOICE
    print_r(PrzetworzXML("pliki",$xml));
    
    //Premium API
    $xml = SerwerSMS::premium_api(array(operacja => "lista", test => 0)); // Lista wiadomości PREMIUM
    $xml = SerwerSMS::premium_api(array(operacja => "wyslij_sms", idsms => "111111111", numer => "500600700", bramka => "71200", wiadomosc => "testowa odpowiedz premium sms")); // Wysyłanie odpowiedzi PREMIUM
    print_r(PrzetworzXML("premium_api",$xml));
    
    //Usuń zaplanowane
    $xml = SerwerSMS::usun_zaplanowane(array(smsid => "89df6g875sf,025701861e")); // Usuwanie zaplanowanych wysyłek
    print_r(PrzetworzXML("usun_zaplanowane",$xml));
    
    //Nazwa nadawcy
    $xml = SerwerSMS::nazwa_nadawcy(array(operacja => "dodanie", nazwa => "SerwerSMS")); // Dodanie nazwy nadawcy
    $xml = SerwerSMS::nazwa_nadawcy(array(operacja => "lista")); // Listowanie nazw nadawcy
    print_r(PrzetworzXML("nazwa_nadawcy",$xml));
    
    //HLR
    $xml = SerwerSMS::hlr(array(numer => "500600700"));
    print_r(PrzetworzXML("hlr",$xml));
    
    //LOOKUP
    $xml = SerwerSMS::lookup(array(numer => "500600700"));
    print_r(PrzetworzXML("lookup",$xml));
    
    //Premium quiz
    $xml = SerwerSMS::quiz(array(quiz => 100));
    print_r(PrzetworzXML("quiz",$xml));
    
    //Kontakty
    $xml = SerwerSMS::kontakty(array(operacja => "lista_grup")); // lista grup
    $xml = SerwerSMS::kontakty(array(operacja => "lista_kontaktow", grupa => "nieprzypisane")); // lista kontaktów dla grupy
    $xml = SerwerSMS::kontakty(array(operacja => "lista_kontaktow", grupa => "17310254")); // lista kontaktów dla grupy
    $xml = SerwerSMS::kontakty(array(operacja => "dodaj_grupe", grupa => "do_usuniecia")); // dodawanie nowej grupy
    $xml = SerwerSMS::kontakty(array(operacja => "dodaj_kontakt", dane => "17310747:500600700:adres@email.com:Imie:Nazwisko:Firma")); // dodawanie nowego kontaktu do grupy
    $xml = SerwerSMS::kontakty(array(operacja => "usun_grupe", grupa => "17310747")); // usuwanie grupy
    $xml = SerwerSMS::kontakty(array(operacja => "usun_kontakt", kontakt => "500600700", grupa => 17310747)); // usuwanie kontaktu z grupy
    print_r(PrzetworzXML("kontakty",$xml));
