<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

// Tabellen aanmaken als ze nog niet bestaan
require_once __DIR__ . '/includes/userTable.php';
require_once __DIR__ . '/includes/transactionTable.php';

$error = '';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

function getThrottleKey(string $username): string
{
    return strtolower(trim($username)) ?: 'unknown';
}

function isSessionThrottled(string $username): bool
{
    $key = getThrottleKey($username);

    if (!isset($_SESSION['login_attempts'][$key])) {
        return false;
    }

    $lockedUntil = $_SESSION['login_attempts'][$key]['locked_until'] ?? 0;

    return $lockedUntil > time();
}

function registerSessionLoginFailure(string $username): void
{
    $key = getThrottleKey($username);

    if (!isset($_SESSION['login_attempts'][$key])) {
        $_SESSION['login_attempts'][$key] = [
            'count' => 0,
            'locked_until' => 0
        ];
    }

    $_SESSION['login_attempts'][$key]['count']++;

    if ($_SESSION['login_attempts'][$key]['count'] >= 5) {
        $_SESSION['login_attempts'][$key]['locked_until'] = time() + 300;
    }
}

function resetSessionLoginFailures(string $username): void
{
    $key = getThrottleKey($username);
    unset($_SESSION['login_attempts'][$key]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Vul een gebruikersnaam en wachtwoord in.";
    } elseif (strlen($username) > 50) {
        $error = "Gebruikersnaam is te lang.";
    } elseif (strlen($password) > 255) {
        $error = "Wachtwoord is te lang.";
    } elseif (isSessionThrottled($username)) {
        $error = "Te veel foutieve inlogpogingen. Probeer het over 5 minuten opnieuw.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, username, password, balance, isAdmin, failed_attempts, locked_until
             FROM `user`
             WHERE username = :username
             LIMIT 1"
        );

        $stmt->execute([
            ':username' => $username
        ]);

        $user = $stmt->fetch();

        $loginAllowed = true;

        if ($user && !empty($user['locked_until'])) {
            $lockedUntilTime = strtotime($user['locked_until']);

            if ($lockedUntilTime !== false && $lockedUntilTime > time()) {
                $loginAllowed = false;
                $error = "Dit account is tijdelijk geblokkeerd door te veel foutieve inlogpogingen.";
            }
        }

        if ($loginAllowed && $user && verifyStoredPassword($password, (string)$user['password'])) {
            if (isCommonPassword($password)) {
                $error = "Dit wachtwoord is niet meer toegestaan. Maak een nieuw sterk wachtwoord aan.";
            } else {
                if (password_needs_rehash((string)$user['password'], PASSWORD_DEFAULT)) {
                    $newHash = hashPassword($password);

                    $stmt = $pdo->prepare("UPDATE `user` SET password = ? WHERE id = ?");
                    $stmt->execute([$newHash, (int)$user['id']]);
                }

                $stmt = $pdo->prepare("
                    UPDATE `user`
                    SET failed_attempts = 0, locked_until = NULL
                    WHERE id = ?
                ");
                $stmt->execute([(int)$user['id']]);

                resetSessionLoginFailures($username);

                session_regenerate_id(true);

                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user'] = [
                    'id'       => (int)$user['id'],
                    'username' => $user['username'],
                    'balance'  => $user['balance'],
                    'isAdmin'  => (int)$user['isAdmin']
                ];

                header("Location: dashboard.php");
                exit;
            }
        } elseif ($loginAllowed) {
            registerSessionLoginFailure($username);

            if ($user) {
                $failedAttempts = (int)$user['failed_attempts'] + 1;
                $lockedUntil = null;

                if ($failedAttempts >= 5) {
                    $lockedUntil = date('Y-m-d H:i:s', time() + 300);
                }

                $stmt = $pdo->prepare("
                    UPDATE `user`
                    SET failed_attempts = ?, locked_until = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $failedAttempts,
                    $lockedUntil,
                    (int)$user['id']
                ]);
            }

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
                    <?= e($error) ?>
                </span>
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