<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordcheck = $_POST['passwordcheck'] ?? '';

    if ($username === '' || $password === '' || $passwordcheck === '') {
        $error = "Vul alle velden in.";
    } elseif (strlen($username) > 50) {
        $error = "Gebruikersnaam is te lang.";
    } elseif (strlen($password) > 255 || strlen($passwordcheck) > 255) {
        $error = "Wachtwoord is te lang.";
    } elseif ($password !== $passwordcheck) {
        $error = "De wachtwoorden komen niet overeen.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);

        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO `user` (username, password, balance, isAdmin)
                 VALUES (?, ?, 100, 0)"
            );
            $stmt->execute([$username, $password]);

            $success = "Je account is aangemaakt, je kunt nu inloggen.";
        } else {
            $error = "Deze gebruikersnaam is al in gebruik.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omanido - Registreren</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container mx-auto mt-20 p-6 bg-white max-w-sm shadow-md rounded-md">
        <div class="flex justify-center">
            <img src="img/Omanido1.png" alt="Omanido Logo" class="mb-6 w-1/2">
        </div>

        <h2 class="text-lg text-center font-bold mb-6">Registreren bij Omanido</h2>

        <?php if ($error !== ''): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Fout!</strong>
                <span class="block sm:inline"><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Gelukt!</strong>
                <span class="block sm:inline"><?= e($success) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= e($_SERVER["PHP_SELF"]) ?>" method="post">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">
                    Gebruikersnaam:
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    maxlength="50"
                    required
                    autocomplete="username"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Wachtwoord:
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    maxlength="255"
                    required
                    autocomplete="new-password"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <div class="mb-6">
                <label for="passwordcheck" class="block text-sm font-medium text-gray-700">
                    Herhaal wachtwoord:
                </label>
                <input
                    type="password"
                    id="passwordcheck"
                    name="passwordcheck"
                    maxlength="255"
                    required
                    autocomplete="new-password"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <div class="flex justify-center">
                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Registreren
                </button>
            </div>
        </form>
    </div>
</body>
</html>