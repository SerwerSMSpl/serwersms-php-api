serwersms-php-api
=================
SerwerSMS.pl umożliwia wysyłanie wiadomości przy pomocy Panelu Klienta oraz dostępnych tam funkcji jak również przy pomocy tzw. zdalnej obsługi. Dzięki drugiej z wymienionych metod możliwe jest wysyłanie oraz sprawdzanie poprawności wysłanych wiadomości jak również dostęp do innych funkcji bez konieczności logowania się do Panelu Klienta.

Komunikacja z SerwerSMS.pl odbywa się poprzez wywołanie adresu URL metodą GET lub POST z odpowiednimi parametrami. Zalecane jest połączenie szyfrowane SSL (https). Jako odpowiedź zwracany jest dokument w formacie XML informujący o wyniku wywołanej akcji. 

Maksymalna wielkość pojedynczego zgłoszenia do wysyłki wiadomości to 100.000 numerów. Zalecane jest przesyłanie mniejszych porcji danych np. 1000-500 numerów w jednym zgłoszeniu. W przypadku gdy w pojedynczym zgłoszeniu zostanie przesłanych więcej numerów lub wiadomości spersonalizowanych (numer oraz wiadomość) wygenerowany zostanie błąd ogólny a wiadomości nie zostaną wysłane.

Usługa zdalnej obsługi przez HTTPS XML API umożliwia również wysyłanie informacji o raportach doręczeń oraz odpowiedziach SMS wprost na wskazany adres URL Abonenta. Aby SerwerSMS.pl wysłał automatycznie informacje o raportach doręczeń do Abonenta, należy w Panelu Klienta ustawić odpowiednie opcje w zakładce Ustawienia interfejsów (HTTPS XML API lub ustawienia w odpowiedniej sekcji np. ND/NDI). Więcej informacji na ten temat znajduje się w dokumentacji: http://dev.serwersms.pl

Zalecane jest, aby komunikacja przez HTTPS XML API odbywała się z loginów utworzonych specjalnie do połączenia przez API. Konto użytkownika API można utworzyć w Panelu Klienta → Ustawienia interfejsów → HTTPS XML API → Użytkownicy.

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
