# Data validatie klaar

## Probleem

De transactiefunctie accepteerde onvoldoende gecontroleerde invoer.

Het grootste probleem was dat negatieve bedragen verwerkt konden worden. Daardoor kon er bijvoorbeeld een transactie met `-47.68` ontstaan. Dit is onveilig, omdat een negatief bedrag het saldo verkeerd kan aanpassen.

Ook moest de applicatie beter omgaan met invoer zoals tekst, rare tekens, lege velden, te lange invoer en bedragen met te veel decimalen.

## Aanpassing

Ik heb data-validatie toegevoegd aan het overmaakformulier.

Aangepast:

- Nieuw bestand gemaakt: `includes/validation.php`.
- Bedragen worden server-side gecontroleerd.
- Negatieve bedragen worden geweigerd.
- Bedrag `0` wordt geweigerd.
- Tekst in het bedragveld wordt geweigerd.
- Bedragen met meer dan 2 decimalen worden geweigerd.
- Bedragen boven €10.000,00 worden geweigerd.
- Ontvanger wordt gecontroleerd op toegestane tekens.
- Omschrijving wordt gecontroleerd op lengte en ongeldige control characters.
- De database heeft een CHECK constraint gekregen: `amount > 0`.
- Oude ongeldige transacties met `amount <= 0` worden gecorrigeerd.

## Gebruikte validatie

Voor bedragen wordt deze controle gebruikt:

```php
if (!preg_match('/^\d+(\.\d{1,2})?$/', $rawAmount)) {
    return [
        'valid' => false,
        'amount' => null,
        'error' => 'Vul een geldig bedrag in met maximaal 2 decimalen.'
    ];
}

$amount = (float)$rawAmount;

if ($amount <= 0) {
    return [
        'valid' => false,
        'amount' => null,
        'error' => 'Het bedrag moet groter zijn dan 0.'
    ];
}