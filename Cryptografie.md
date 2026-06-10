# Cryptografie klaar

## Probleem

De applicatie sloeg wachtwoorden eerst op als platte tekst. Dit is onveilig, omdat iedereen met toegang tot de database de wachtwoorden direct kan lezen.

Voorbeelden van oude zwakke wachtwoorden waren:

- `12345678`
- `password`
- `qwerty`

Dit is gevaarlijk bij een datalek. Als de database uitlekt, kunnen aanvallers direct inloggen met de gevonden wachtwoorden.

## Aanpassing

Ik heb wachtwoordbeveiliging toegevoegd.

Aangepast:

- Wachtwoorden worden opgeslagen met `password_hash`.
- Login controleert wachtwoorden met `password_verify`.
- Oude plaintext wachtwoorden worden automatisch gemigreerd naar hashes.
- Zwakke standaardwachtwoorden zijn vervangen door sterke wachtwoorden.
- Nieuwe accounts krijgen alleen nog gehashte wachtwoorden.
- Wachtwoorden worden niet meer opgeslagen in de sessie.
- De gebruikerspagina toont geen wachtwoorden.

## Nieuwe veilige testaccounts

### Admin

Gebruiker:

```txt
Admin