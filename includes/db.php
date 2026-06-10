<?php

// PDO database verbinding voor Docker

$host = 'db';
$db   = 'mydb';
$user = 'user';
$pass = 'test';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$maxAttempts = 20;
$attempt = 0;

while (true) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        break;
    } catch (PDOException $e) {
        $attempt++;

        if ($attempt >= $maxAttempts) {
            die(
                "Database verbinding mislukt na {$maxAttempts} pogingen.<br>" .
                "Foutmelding: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
            );
        }

        sleep(1);
    }
}