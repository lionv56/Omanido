<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

$isAdmin = isset($_SESSION['user']['isAdmin']) && (int)$_SESSION['user']['isAdmin'] === 1;

if (!$isAdmin) {
    http_response_code(403);
    die("Geen toegang tot deze pagina.");
}

$stmt = $pdo->prepare("SELECT id, username, balance, isAdmin FROM `user` ORDER BY id ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikers | Omanido</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.15/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container mx-auto mt-20 p-6 bg-white shadow-md rounded-md">
    <h2 class="text-lg text-center font-bold mb-6">Gebruikers</h2>

    <table class="w-full">
        <thead>
            <tr>
                <th class="border-b-2 p-2">ID</th>
                <th class="border-b-2 p-2">Gebruikersnaam</th>
                <th class="border-b-2 p-2">Saldo</th>
                <th class="border-b-2 p-2">Rol</th>
                <th class="border-b-2 p-2">Actie</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="border-b p-2">
                        <?= (int)$user['id'] ?>
                    </td>

                    <td class="border-b p-2">
                        <?= e($user['username']) ?>
                    </td>

                    <td class="border-b p-2">
                        €<?= number_format((float)$user['balance'], 2, ',', '.') ?>
                    </td>

                    <td class="border-b p-2">
                        <?= (int)$user['isAdmin'] === 1 ? 'Beheerder' : 'Gebruiker' ?>
                    </td>

                    <td class="border-b p-2">
                        <a
                            href="transacties.php?id=<?= (int)$user['id'] ?>"
                            class="text-blue-600 hover:underline"
                        >
                            Bekijk transacties
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>