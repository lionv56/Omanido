# Gevoelige data die zichtbaar is klaar

## Probleem

Op de transactiepagina stond de klant-ID zichtbaar in de URL, bijvoorbeeld:

http://localhost:8000/transacties.php?id=5

Dit is onveilig, omdat een ingelogde gebruiker de ID in de URL kon aanpassen om mogelijk gegevens van andere gebruikers te bekijken.

Ook stonden er links in de applicatie die direct naar `transacties.php?id=...` gingen. Daardoor werd de interne gebruiker-ID zichtbaar gemaakt in de browser.

## Aanpassing

Ik heb de applicatie aangepast zodat normale gebruikers hun eigen transactiepagina openen zonder ID in de URL.

Aangepast:

- `header.php` linkt nu naar `transacties.php` zonder user-ID.
- `dashboard.php` linkt nu naar `transacties.php` zonder user-ID.
- `transacties.php` gebruikt voor normale gebruikers de user-ID uit de sessie.
- Normale gebruikers kunnen geen andere gebruiker bekijken door de URL aan te passen.
- Alleen beheerders mogen via `transacties.php?id=...` een andere gebruiker bekijken.
- `users.php` is afgeschermd zodat alleen beheerders de gebruikerslijst kunnen zien.
- Gegevens die op de pagina worden getoond worden veilig ge-escaped met `htmlspecialchars`.

## Waarom dit veiliger is

De server vertrouwt niet meer op de ID uit de URL voor gewone gebruikers.

De pagina bepaalt nu met de sessie welke gebruiker is ingelogd:

```php
$currentUserId = (int)$_SESSION['user']['id'];
$viewUserId = $currentUserId;