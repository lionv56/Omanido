# SQL-injecties klaar

## Probleem

De loginpagina van Omanido was kwetsbaar voor SQL-injectie.

In de oude code werden de gebruikersnaam en het wachtwoord direct in een SQL-query geplakt. Daardoor kon invoer van een gebruiker invloed hebben op de SQL-query.

Ook stond er een debugbar op de pagina die de uitgevoerde SQL-query liet zien. Dit is onveilig, omdat gebruikers dan kunnen zien hoe de database-query is opgebouwd.

## Aanpassing

Ik heb de loginpagina aangepast.

Aangepast:

- De oude kwetsbare SQL-query is verwijderd.
- De login gebruikt nu een prepared statement.
- Gebruikersinvoer wordt niet meer direct in SQL geplakt.
- De debugbar met de SQL-query is verwijderd.
- Er wordt alleen nog een algemene foutmelding getoond.
- Invoer wordt gecontroleerd op lege waarden en maximale lengte.

## Gebruikte beveiligingstechniek

De belangrijkste beveiliging is een prepared statement met PDO.

```php
$stmt = $pdo->prepare(
    "SELECT id, username, password, balance, isAdmin
     FROM `user`
     WHERE username = :username
     LIMIT 1"
);

$stmt->execute([
    ':username' => $username
]);