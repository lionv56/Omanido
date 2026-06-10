<?php

require_once __DIR__ . '/security.php';

if (!function_exists('omanidoTableExists')) {
    function omanidoTableExists(PDO $pdo, string $tableName): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ");

        $stmt->execute([$tableName]);

        return (int)$stmt->fetchColumn() > 0;
    }
}

if (!function_exists('omanidoColumnExists')) {
    function omanidoColumnExists(PDO $pdo, string $tableName, string $columnName): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ");

        $stmt->execute([$tableName, $columnName]);

        return (int)$stmt->fetchColumn() > 0;
    }
}

if (!omanidoTableExists($pdo, 'user')) {
    $pdo->exec("
        CREATE TABLE `user` (
            `id` int NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `balance` decimal(10,2) NOT NULL,
            `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
            `failed_attempts` int NOT NULL DEFAULT 0,
            `locked_until` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username_unique` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ");
} else {
    if (!omanidoColumnExists($pdo, 'user', 'failed_attempts')) {
        $pdo->exec("
            ALTER TABLE `user`
            ADD `failed_attempts` int NOT NULL DEFAULT 0
        ");
    }

    if (!omanidoColumnExists($pdo, 'user', 'locked_until')) {
        $pdo->exec("
            ALTER TABLE `user`
            ADD `locked_until` datetime NULL DEFAULT NULL
        ");
    }

    if (!omanidoColumnExists($pdo, 'user', 'isAdmin')) {
        $pdo->exec("
            ALTER TABLE `user`
            ADD `isAdmin` tinyint(1) NOT NULL DEFAULT '0'
        ");
    }
}

// Veilige standaardaccounts.
// Oude zwakke wachtwoorden worden vervangen door sterke gehashte wachtwoorden.
$defaultUsers = [
    [
        'id' => 1,
        'username' => 'Admin',
        'weak_password' => 'AlfaBankAdminAccount',
        'new_password' => 'OmanidoAdmin!2026',
        'balance' => 1000.00,
        'isAdmin' => 1
    ],
    [
        'id' => 2,
        'username' => 'FerryKuhlman',
        'weak_password' => '12345678',
        'new_password' => 'FerryVeilig!2026',
        'balance' => 1255.36,
        'isAdmin' => 0
    ],
    [
        'id' => 5,
        'username' => 'Han2002',
        'weak_password' => 'password',
        'new_password' => 'HanVeilig!2026',
        'balance' => 23424.84,
        'isAdmin' => 0
    ],
    [
        'id' => 6,
        'username' => 'RoyBos',
        'weak_password' => 'qwerty',
        'new_password' => 'RoyVeilig!2026',
        'balance' => 9.23,
        'isAdmin' => 0
    ],
];

foreach ($defaultUsers as $defaultUser) {
    $stmt = $pdo->prepare("
        SELECT id, password
        FROM `user`
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$defaultUser['username']]);
    $existingUser = $stmt->fetch();

    $newHash = hashPassword($defaultUser['new_password']);

    if (!$existingUser) {
        $stmt = $pdo->prepare("
            INSERT INTO `user`
                (`id`, `username`, `password`, `balance`, `isAdmin`, `failed_attempts`, `locked_until`)
            VALUES
                (?, ?, ?, ?, ?, 0, NULL)
        ");

        $stmt->execute([
            $defaultUser['id'],
            $defaultUser['username'],
            $newHash,
            $defaultUser['balance'],
            $defaultUser['isAdmin']
        ]);
    } else {
        $storedPassword = (string)$existingUser['password'];

        $usesOldWeakPassword =
            hash_equals($storedPassword, $defaultUser['weak_password']) ||
            verifyStoredPassword($defaultUser['weak_password'], $storedPassword);

        if ($usesOldWeakPassword) {
            $stmt = $pdo->prepare("
                UPDATE `user`
                SET password = ?,
                    isAdmin = ?,
                    failed_attempts = 0,
                    locked_until = NULL
                WHERE username = ?
            ");

            $stmt->execute([
                $newHash,
                $defaultUser['isAdmin'],
                $defaultUser['username']
            ]);
        }
    }
}

// Extra migratie:
// Als er nog zelf aangemaakte gebruikers met plaintext wachtwoorden bestaan,
// worden die automatisch omgezet naar een hash.
$stmt = $pdo->query("
    SELECT id, password
    FROM `user`
");

$allUsers = $stmt->fetchAll();

foreach ($allUsers as $user) {
    $storedPassword = (string)$user['password'];

    if (!isPasswordHash($storedPassword)) {
        $hashedPassword = hashPassword($storedPassword);

        $updateStmt = $pdo->prepare("
            UPDATE `user`
            SET password = ?
            WHERE id = ?
        ");

        $updateStmt->execute([
            $hashedPassword,
            (int)$user['id']
        ]);
    }
}