# Identification and auth. failures klaar

## Probleem

De applicatie had problemen met identificatie en authenticatie.

Er waren meerdere risico's:

- Standaardgebruikers hadden zwakke wachtwoorden zoals `12345678`, `password` en `qwerty`.
- Wachtwoorden werden als platte tekst opgeslagen.
- Nieuwe gebruikers konden ook zwakke wachtwoorden kiezen.
- Er was geen goede blokkade na meerdere foutieve inlogpogingen.
- Het wachtwoord werd in de sessie opgeslagen.

Dit is onveilig, omdat aanvallers met gelekte wachtwoorden of veelgebruikte wachtwoorden makkelijk kunnen proberen in te loggen.

## Aanpassing

Ik heb de authenticatie aangepast.

Aangepast:

- Wachtwoorden worden opgeslagen met `password_hash`.
- Login controleert wachtwoorden met `password_verify`.
- Zwakke standaardwachtwoorden zijn vervangen door sterke wachtwoorden.
- Registratie accepteert alleen sterke wachtwoorden.
- Veelgebruikte wachtwoorden zoals `12345678`, `password` en `qwerty` worden geweigerd.
- Na 5 foutieve loginpogingen wordt een account tijdelijk geblokkeerd.
- Ook niet-bestaande gebruikers worden tijdelijk afgeremd via sessie-throttling.
- Bij succesvol inloggen wordt `session_regenerate_id(true)` gebruikt.
- Het wachtwoord wordt niet meer opgeslagen in `$_SESSION`.
- Logout verwijdert de sessie netter.

## Nieuwe testaccounts

Na database-reset zijn dit de nieuwe testaccounts:

### Admin

Gebruiker:

```txt
Admin