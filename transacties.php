<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$currentUserId = currentUserId();
$viewUserId = $currentUserId;

// Normale gebruikers mogen geen id in de URL gebruiken.
// Zij mogen alleen hun eigen transacties zien.
if (!isAdmin() && isset($_GET['id'])) {
    redirectTo('transacties.php');
}

// Alleen admins mogen via id een andere gebruiker bekijken.
if (isAdmin() && isset($_GET['id'])) {
    if (!ctype_digit((string)$_GET['id'])) {
        http_response_code(400);
        die("Ongeldige gebruiker.");
    }

    $viewUserId = (int)$_GET['id'];
}

// Extra server-side autorisatiecheck
requireOwnerOrAdmin($viewUserId);

$stmt = $pdo->prepare("SELECT id, username, balance FROM `user` WHERE id = ? LIMIT 1");
$stmt->execute([$viewUserId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    die("Gebruiker niet gevonden.");
}

$stmt = $pdo->prepare(
    "SELECT id, sender, receiver, amount, description
     FROM `transaction`
     WHERE sender = ?
     ORDER BY id DESC"
);
$stmt->execute([$viewUserId]);
$outgoingTransactions = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT id, sender, receiver, amount, description
     FROM `transaction`
     WHERE receiver = ?
     ORDER BY id DESC"
);
$stmt->execute([$viewUserId]);
$incomingTransactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($user['username']) ?> | Omanido</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.15/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container mx-auto mt-20 p-6 bg-white shadow-md rounded-md">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="col-span-1">
            <div class="flex justify-center">
                <img src="img/Omanido1.png" alt="Omanido Logo" class="mb-6 w-1/2">
            </div>

            <h2 class="text-lg text-center font-bold mb-6">
                <?= e($user['username']) ?>
            </h2>

            <p class="text-center mb-6">
                Saldo: €<?= number_format((float)$user['balance'], 2, ',', '.') ?>
            </p>

            <div class="flex justify-center">
                <a href="dashboard.php"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Geld overmaken
                </a>
            </div>

            <div class="flex justify-center mt-6">
                <a href="logout.php"
                   class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Uitloggen
                </a>
            </div>
        </div>

        <div class="col-span-1">
            <?php if (!empty($outgoingTransactions)): ?>
                <h2 class="text-lg text-center font-bold mb-6">
                    Uitgaande Transacties
                </h2>

                <div class="bg-red-100 p-2 rounded">
                    <?php foreach ($outgoingTransactions as $transaction): ?>
                        <div class="flex justify-between mb-2">
                            <p><?= e($transaction['description']) ?></p>
                            <p>€<?= number_format((float)$transaction['amount'], 2, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-red-500 font-bold">
                    Er zijn geen uitgaande transacties.
                </p>
            <?php endif; ?>
        </div>

        <div class="col-span-1">
            <?php if (!empty($incomingTransactions)): ?>
                <h2 class="text-lg text-center font-bold mb-6">
                    Inkomende Transacties
                </h2>

                <div class="bg-green-100 p-2 rounded">
                    <?php foreach ($incomingTransactions as $transaction): ?>
                        <div class="flex justify-between mb-2">
                            <p><?= e($transaction['description']) ?></p>
                            <p>€<?= number_format((float)$transaction['amount'], 2, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-red-500 font-bold">
                    Er zijn geen inkomende transacties.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>