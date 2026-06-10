<?php

// Let op:
// Geen session_start() in dit bestand.
// Geen require_once 'includes/db.php' in dit bestand.
// Dit bestand wordt geladen vanuit index.php nadat db.php al geladen is.

function omanidoTransactionTableExists(PDO $pdo): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'transaction'
    ");

    $stmt->execute();

    return (int)$stmt->fetchColumn() > 0;
}

function omanidoCheckConstraintExists(PDO $pdo, string $constraintName): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.CHECK_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND CONSTRAINT_NAME = ?
    ");

    $stmt->execute([$constraintName]);

    return (int)$stmt->fetchColumn() > 0;
}

if (!omanidoTransactionTableExists($pdo)) {
    $pdo->exec("
        CREATE TABLE `transaction` (
            `id` int NOT NULL AUTO_INCREMENT,
            `sender` int NOT NULL,
            `receiver` int NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `description` varchar(500) NOT NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `chk_transaction_amount_positive` CHECK (`amount` > 0)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ");

    $pdo->exec("
        INSERT INTO `transaction` (`id`, `sender`, `receiver`, `amount`, `description`) VALUES
        (1, 3, 2, 65.00, 'Auto'),
        (2, 5, 2, 94.00, '<script>alert(\"Je bent gehacked\")</script>'),
        (3, 6, 2, 38.84, 'Avondje stappen'),
        (4, 5, 6, 50.00, 'Boodschappen buurtsuper'),
        (5, 6, 5, 50.00, 'Vakantie'),
        (6, 2, 5, 30.00, 'Zakgeld'),
        (7, 5, 6, 47.68, 'Boodschappen')
    ");
} else {
    // Bestaande ongeldige data corrigeren voordat de CHECK constraint wordt toegevoegd.
    $pdo->exec("
        UPDATE `transaction`
        SET amount = CASE
            WHEN amount < 0 THEN ABS(amount)
            ELSE 0.01
        END
        WHERE amount <= 0
    ");

    if (!omanidoCheckConstraintExists($pdo, 'chk_transaction_amount_positive')) {
        $pdo->exec("
            ALTER TABLE `transaction`
            ADD CONSTRAINT `chk_transaction_amount_positive`
            CHECK (`amount` > 0)
        ");
    }
}