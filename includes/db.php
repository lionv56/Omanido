<?php

// PDO database verbinding voor Docker

$host = 'db';        // Moet hetzelfde zijn als de database service in docker-compose.yml
$db   = 'mydb';      // Moet hetzelfde zijn als MYSQL_DATABASE
$user = 'user';      // Moet hetzelfde zijn als MYSQL_USER
$pass = 'test';      // Moet hetzelfde zijn als MYSQL_PASSWORD
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Kleine retry-loop, omdat MySQL soms iets later klaar is dan Apache/PHP
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
                "Foutmelding: " . htmlspecialchars($e->getMessage())
            );
        }

        sleep(1);
    }
}