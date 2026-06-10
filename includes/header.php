<?php
require_once __DIR__ . '/auth.php';

$isLoggedIn = isLoggedIn();
$user = currentUser();
$username = $user['username'] ?? '';
$isAdmin = isAdmin();
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

                <a href="transacties.php" class="text-blue-600 hover:underline">
                    Mijn transacties
                </a>

                <?php if ($isAdmin): ?>
                    <a href="users.php" class="text-blue-600 hover:underline">
                        Gebruikers
                    </a>
                <?php endif; ?>

                <span class="text-gray-500">
                    Welkom, <?= e($username) ?>
                </span>

                <a href="logout.php" class="text-red-600 hover:underline">
                    Uitloggen
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>