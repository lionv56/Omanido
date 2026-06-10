<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';

requireLogin();

$error = '';
$success = '';

$userId = currentUserId();

$stmt = $pdo->prepare("SELECT balance FROM `user` WHERE id = ?");
$stmt->execute([$userId]);
$saldo = $stmt->fetchColumn();

if ($saldo === false) {
    session_destroy();
    redirectTo('index.php');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $receiverValidation = validateReceiverName($_POST['ontvanger'] ?? '');
    $amountValidation = normalizeMoneyAmount($_POST['bedrag'] ?? '');
    $descriptionValidation = validateTransferDescription($_POST['omschrijving'] ?? '');

    if (!$receiverValidation['valid']) {
        $error = $receiverValidation['error'];
    } elseif (!$amountValidation['valid']) {
        $error = $amountValidation['error'];
    } elseif (!$descriptionValidation['valid']) {
        $error = $descriptionValidation['error'];
    } else {
        $ontvangerNaam = $receiverValidation['value'];
        $bedrag = $amountValidation['amount'];
        $omschrijving = $descriptionValidation['value'];

        $stmt = $pdo->prepare("SELECT id, username, balance FROM `user` WHERE username = ? LIMIT 1");
        $stmt->execute([$ontvangerNaam]);
        $ontvanger = $stmt->fetch();

        if (!$ontvanger) {
            $error = "Deze gebruiker bestaat niet.";
        } elseif ((int)$ontvanger['id'] === $userId) {
            $error = "Je kunt geen geld naar jezelf overmaken.";
        } else {
            try {
                $pdo->beginTransaction();

                // Haal saldo opnieuw op met lock zodat het saldo tijdens de transactie klopt.
                $stmt = $pdo->prepare("SELECT balance FROM `user` WHERE id = ? FOR UPDATE");
                $stmt->execute([$userId]);
                $currentBalance = $stmt->fetchColumn();

                if ($currentBalance === false) {
                    throw new Exception("Gebruiker niet gevonden.");
                }

                if ((float)$currentBalance < (float)$bedrag) {
                    $pdo->rollBack();
                    $error = "Je hebt niet genoeg saldo om dit bedrag over te maken.";
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM `user` WHERE id = ? FOR UPDATE");
                    $stmt->execute([(int)$ontvanger['id']]);

                    if (!$stmt->fetch()) {
                        throw new Exception("Ontvanger niet gevonden.");
                    }

                    $stmt = $pdo->prepare(
                        "INSERT INTO `transaction` (sender, receiver, amount, description)
                         VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $userId,
                        (int)$ontvanger['id'],
                        $bedrag,
                        $omschrijving
                    ]);

                    $stmt = $pdo->prepare("UPDATE `user` SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([
                        $bedrag,
                        (int)$ontvanger['id']
                    ]);

                    $stmt = $pdo->prepare("UPDATE `user` SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([
                        $bedrag,
                        $userId
                    ]);

                    $pdo->commit();

                    $stmt = $pdo->prepare("SELECT balance FROM `user` WHERE id = ?");
                    $stmt->execute([$userId]);
                    $saldo = $stmt->fetchColumn();

                    $_SESSION['user']['balance'] = $saldo;

                    $success = "Het bedrag is succesvol overgemaakt.";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "Er ging iets mis bij het overmaken.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Omanido</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <div class="flex flex-wrap -mx-2">
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md h-full flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-xl mb-2">Mijn Saldo</h3>
                        <p class="text-sm text-gray-600 mb-4">Actueel Beschikbaar Saldo</p>
                    </div>

                    <p class="text-4xl font-bold mb-4 <?= $saldo >= 0 ? 'text-green-500' : 'text-red-500'; ?> self-center">
                        €<?= number_format((float)$saldo, 2, ',', '.'); ?>
                    </p>

                    <div class="text-center">
                        <a href="transacties.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Transactieoverzicht
                        </a>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-2/3 px-2 mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md h-full">
                    <h3 class="font-bold text-xl mb-4">Geld Overmaken</h3>

                    <form action="<?= e($_SERVER["PHP_SELF"]) ?>" method="post">
                        <div class="mb-4">
                            <label for="ontvanger" class="block text-sm font-medium text-gray-700">
                                Ontvanger:
                            </label>
                            <input
                                type="text"
                                id="ontvanger"
                                name="ontvanger"
                                required
                                maxlength="50"
                                pattern="[a-zA-Z0-9_]+"
                                title="Alleen letters, cijfers en underscores zijn toegestaan."
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="bedrag" class="block text-sm font-medium text-gray-700">
                                Bedrag (€):
                            </label>
                            <input
                                type="number"
                                id="bedrag"
                                name="bedrag"
                                step="0.01"
                                min="0.01"
                                max="10000"
                                required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                Alleen positieve bedragen tussen €0,01 en €10.000,00 zijn toegestaan.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="omschrijving" class="block text-sm font-medium text-gray-700">
                                Omschrijving:
                            </label>
                            <input
                                type="text"
                                id="omschrijving"
                                name="omschrijving"
                                required
                                minlength="2"
                                maxlength="500"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                            >
                        </div>

                        <input
                            type="submit"
                            value="Overmaken"
                            class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                        >

                        <?php if ($error !== ''): ?>
                            <p class="text-red-500 text-sm mt-2">
                                <?= e($error) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($success !== ''): ?>
                            <p class="text-green-500 text-sm mt-2">
                                <?= e($success) ?>
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>