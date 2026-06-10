<?php
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $isLoggedIn && isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user']['isAdmin']) && (int)$_SESSION['user']['isAdmin'] === 1;
?>

<div class="bg-white py-4 shadow-md">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
        <a href="<?= $isLoggedIn ? 'dashboard.php' : 'index.php' ?>">
            <img src="img/Omanido2.png" alt="Omanido Logo" class="h-12">
        </a>

        <?php if ($isLoggedIn): ?>
            <div class="flex items-center gap-4 text-sm">
                <a href="dashboard.php" class="text-blue-600 hover:underline">
                    Dashboard
                </a>

                <!-- Geen user-id meer in de URL -->
                <a href="transacties.php" class="text-blue-600 hover:underline">
                    Mijn transacties
                </a>

                <?php if ($isAdmin): ?>
                    <a href="users.php" class="text-blue-600 hover:underline">
                        Gebruikers
                    </a>
                <?php endif; ?>

                <span class="text-gray-500">
                    Welkom, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                </span>

                <a href="logout.php" class="text-red-600 hover:underline">
                    Uitloggen
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>