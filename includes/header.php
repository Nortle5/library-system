<?php
$pageTitle = $pageTitle ?? 'Library System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | Library System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-gray-100">
<header class="bg-white shadow">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
        <a href="books.php" class="text-lg font-semibold text-gray-800">Library System</a>
        <div class="flex items-center gap-4 text-sm">
            <a href="books.php" class="text-gray-600 hover:text-blue-600">Books</a>
            <a href="profile.php" class="text-gray-600 hover:text-blue-600">My Profile</a>
            <a href="login.php" class="text-gray-600 hover:text-blue-600">Log out</a>
        </div>
    </nav>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">