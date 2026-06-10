<?php

function normalizeMoneyAmount(string $rawAmount): array
{
    $rawAmount = trim($rawAmount);
    $rawAmount = str_replace(',', '.', $rawAmount);

    if ($rawAmount === '') {
        return [
            'valid' => false,
            'amount' => null,
            'error' => 'Vul een bedrag in.'
        ];
    }

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

    if ($amount > 10000) {
        return [
            'valid' => false,
            'amount' => null,
            'error' => 'Het bedrag mag maximaal €10.000,00 zijn.'
        ];
    }

    return [
        'valid' => true,
        'amount' => number_format($amount, 2, '.', ''),
        'error' => ''
    ];
}

function validateReceiverName(string $receiver): array
{
    $receiver = trim($receiver);

    if ($receiver === '') {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'Vul een ontvanger in.'
        ];
    }

    if (strlen($receiver) > 50) {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'De naam van de ontvanger is te lang.'
        ];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $receiver)) {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'De ontvanger mag alleen letters, cijfers en underscores bevatten.'
        ];
    }

    return [
        'valid' => true,
        'value' => $receiver,
        'error' => ''
    ];
}

function validateTransferDescription(string $description): array
{
    $description = trim($description);

    if ($description === '') {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'Vul een omschrijving in.'
        ];
    }

    if (strlen($description) < 2) {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'De omschrijving moet minimaal 2 tekens lang zijn.'
        ];
    }

    if (strlen($description) > 500) {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'De omschrijving is te lang.'
        ];
    }

    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $description)) {
        return [
            'valid' => false,
            'value' => '',
            'error' => 'De omschrijving bevat ongeldige tekens.'
        ];
    }

    return [
        'valid' => true,
        'value' => $description,
        'error' => ''
    ];
}