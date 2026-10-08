<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}
function requireLogin(): void
{
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    $user = currentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }
    if ($user['role'] !== 'admin') {
        header('Location: books.php');
        exit;
    }
}