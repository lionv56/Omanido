<?php

// Let op:
// Geen session_start() in dit bestand.
// Geen require_once 'includes/db.php' in dit bestand.
// Dit bestand wordt geladen vanuit index.php nadat db.php al geladen is.

$checkTable = $pdo->query("SHOW TABLES LIKE 'user'");

if ($checkTable->rowCount() == 0) {
    $pdo->exec("
        CREATE TABLE `user` (
            `id` int NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `balance` decimal(10,2) NOT NULL,
            `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ");

    $pdo->exec("
        INSERT INTO `user` (`id`, `username`, `password`, `balance`, `isAdmin`) VALUES
        (1, 'Admin', 'AlfaBankAdminAccount', 1000.00, 1),
        (2, 'FerryKuhlman', '12345678', 1255.36, 0),
        (5, 'Han2002', 'password', 23424.84, 0),
        (6, 'RoyBos', 'qwerty', 9.23, 0)
    ");
}