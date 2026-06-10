# Broken access control klaar

## Probleem

De website had problemen met toegangscontrole.

Volgens de autorisatietabel uit de opdracht geldt:

- `users.php` mag alleen door beheerders worden bekeken.
- `dashboard.php` mag alleen door ingelogde gebruikers worden bekeken.
- `transacties.php` mag alleen door ingelogde gebruikers worden bekeken.
- `logout.php` mag alleen door ingelogde gebruikers worden gebruikt.
- `index.php` mag voor iedereen bereikbaar zijn.

Het probleem was dat de server niet overal streng controleerde of de gebruiker de juiste rechten had. Hierdoor kon een normale gebruiker proberen om pagina's te openen die alleen voor beheerders bedoeld zijn.

## Aanpassing

Ik heb server-side toegangscontrole toegevoegd.

Aangepast:

- Nieuw bestand gemaakt: `includes/auth.php`.
- Functie `requireLogin()` toegevoegd voor pagina's die alleen voor ingelogde gebruikers zijn.
- Functie `requireAdmin()` toegevoegd voor pagina's die alleen voor beheerders zijn.
- Functie `requireOwnerOrAdmin()` toegevoegd voor gegevens die alleen door de eigenaar of beheerder bekeken mogen worden.
- `dashboard.php` gebruikt nu `requireLogin()`.
- `transacties.php` gebruikt nu `requireLogin()` en `requireOwnerOrAdmin()`.
- `users.php` gebruikt nu `requireAdmin()`.
- `logout.php` stuurt niet-ingelogde bezoekers terug naar `index.php`.
- De navigatie toont de gebruikerspagina alleen aan beheerders.

## Autorisatietest

| Pagina | Niet ingelogd | Gewone gebruiker | Beheerder |
|---|---|---|---|
| `index.php` | Toegang | Toegang / redirect dashboard | Toegang / redirect dashboard |
| `dashboard.php` | Geen toegang | Toegang | Toegang |
| `transacties.php` | Geen toegang | Alleen eigen transacties | Toegang |
| `users.php` | Geen toegang | Geen toegang | Toegang |
| `logout.php` | Geen toegang / redirect | Toegang | Toegang |

## Test

Ik heb getest via Docker op:

```txt
http://localhost:8000