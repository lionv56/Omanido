<?php
session_start();

require_once __DIR__ . '/includes/db.php';

// Tabellen aanmaken als ze nog niet bestaan
require_once __DIR__ . '/includes/userTable.php';
require_once __DIR__ . '/includes/transactionTable.php';

$error = '';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Input-validatie
    if ($username === '' || $password === '') {
        $error = "Vul een gebruikersnaam en wachtwoord in.";
    } elseif (strlen($username) > 50) {
        $error = "Gebruikersnaam is te lang.";
    } elseif (strlen($password) > 255) {
        $error = "Wachtwoord is te lang.";
    } else {
        /*
            SQL-injectie oplossing:
            Voorheen stond hier kwetsbare code zoals:

            SELECT * FROM user WHERE username = '$username' AND password = '$password'

            Dat is gevaarlijk, omdat invoer dan als SQL-code kan worden uitgevoerd.
            Nu gebruiken we een prepared statement.
        */
        $stmt = $pdo->prepare(
            "SELECT id, username, password, balance, isAdmin
             FROM `user`
             WHERE username = :username
             LIMIT 1"
        );

        $stmt->execute([
            ':username' => $username
        ]);

        $user = $stmt->fetch();

        /*
            Let op:
            De wachtwoorden staan in deze les-app nog als platte tekst in de database.
            Dat hoort bij de latere opdracht over cryptografie/wachtwoorden.
            Voor fase 1 lossen we vooral SQL-injectie op.
        */
        if ($user && hash_equals((string)$user['password'], (string)$password)) {
            session_regenerate_id(true);

            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'username' => $user['username'],
                'password' => $user['password'],
                'balance'  => $user['balance'],
                'isAdmin'  => (int)$user['isAdmin']
            ];

            header("Location: dashboard.php");
            exit;
        } else {
            // Algemene foutmelding, geen SQL-query of database-informatie tonen
            $error = "Gebruikersnaam of wachtwoord is onjuist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omanido - Inloggen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container mx-auto mt-20 p-6 bg-white max-w-sm shadow-md rounded-md">
        <div class="flex justify-center">
            <img src="img/Omanido1.png" alt="Omanido Logo" class="mb-6 w-1/2">
        </div>

        <h2 class="text-lg text-center font-bold mb-6">Inloggen bij Omanido</h2>

        <?php if ($error !== ''): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <strong class="font-bold">Fout!</strong>
                <span class="block sm:inline">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="post">
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
                    autocomplete="current-password"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <input
                type="submit"
                value="Inloggen"
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline"
            >
        </form>

        <a href="register.php" class="block text-center text-sm text-blue-600 hover:underline mt-4">
            Nog geen account? Registreer hier
        </a>
    </div>
</body>
</html>