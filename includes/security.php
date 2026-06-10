<?php

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function isCommonPassword(string $password): bool
{
    $commonPasswords = [
        '123456',
        '1234567',
        '12345678',
        '123456789',
        'password',
        'wachtwoord',
        'qwerty',
        'qwerty123',
        'abc123',
        'abcdefg',
        'geheim',
        'admin',
        'admin123',
        'welkom',
        'welcome',
        'iloveyou',
        'letmein'
    ];

    return in_array(strtolower($password), $commonPasswords, true);
}

function validateStrongPassword(string $password, string $username = ''): array
{
    $errors = [];

    if (strlen($password) < 10) {
        $errors[] = "Het wachtwoord moet minimaal 10 tekens lang zijn.";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Het wachtwoord moet minimaal één kleine letter bevatten.";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Het wachtwoord moet minimaal één hoofdletter bevatten.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Het wachtwoord moet minimaal één cijfer bevatten.";
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Het wachtwoord moet minimaal één speciaal teken bevatten.";
    }

    if (isCommonPassword($password)) {
        $errors[] = "Dit wachtwoord is te makkelijk te raden.";
    }

    if ($username !== '' && stripos($password, $username) !== false) {
        $errors[] = "Het wachtwoord mag je gebruikersnaam niet bevatten.";
    }

    return $errors;
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function isPasswordHash(string $storedPassword): bool
{
    $info = password_get_info($storedPassword);
    return $info['algo'] !== 0;
}

function verifyStoredPassword(string $inputPassword, string $storedPassword): bool
{
    if (isPasswordHash($storedPassword)) {
        return password_verify($inputPassword, $storedPassword);
    }

    // Alleen voor oude bestaande plaintext wachtwoorden.
    return hash_equals($storedPassword, $inputPassword);
}