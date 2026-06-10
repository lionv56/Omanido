<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/security.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function currentUser(): array
{
    return $_SESSION['user'] ?? [];
}

function currentUserId(): int
{
    return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
}

function isAdmin(): bool
{
    return isset($_SESSION['user']['isAdmin']) && (int)$_SESSION['user']['isAdmin'] === 1;
}

function redirectTo(string $location): void
{
    header("Location: {$location}");
    exit;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirectTo('index.php');
    }
}

function requireAdmin(): void
{
    if (!isLoggedIn()) {
        redirectTo('index.php');
    }

    if (!isAdmin()) {
        http_response_code(403);
        echo '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Geen toegang | Omanido</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-20 bg-white p-8 rounded shadow text-center">
        <h1 class="text-2xl font-bold text-red-600 mb-4">Geen toegang</h1>
        <p class="mb-6">Je hebt geen rechten om deze pagina te bekijken.</p>
        <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded">Terug naar dashboard</a>
    </div>
</body>
</html>';
        exit;
    }
}

function requireOwnerOrAdmin(int $ownerId): void
{
    requireLogin();

    if (!isAdmin() && currentUserId() !== $ownerId) {
        http_response_code(403);
        echo "Geen toegang tot deze gegevens.";
        exit;
    }
}