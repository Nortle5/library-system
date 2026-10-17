<?php
$pageTitle = $pageTitle ?? 'Library System';
$isAdminArea = ($area ?? 'student') === 'admin';
$currentPage = $_SERVER['SCRIPT_NAME'];

// label, address, icon
$navLinks = $isAdminArea
    ? [['Home', url('home'), 'home'], ['Manage Books', url('admin_books'), 'book-open'], ['Borrowings', url('admin_borrowings'), 'list'], ['Log out', url('logout'), 'logout']]
    : [['Home', url('home'), 'home'], ['Books', url('books'), 'book-open'], ['Profile', url('profile'), 'user'], ['Log out', url('logout'), 'logout']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | Library System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-gray-900"
      style="background-image: url('<?= asset('images/books.jpg') ?>'); background-size: cover; background-position: center; background-attachment: fixed;">
    <header class="bg-gray-900 text-white shadow">
        <div class="flex items-center justify-between px-6 py-3 sm:px-10">
            <a href="<?= url('home') ?>" class="flex items-center gap-3">
                <img src="<?= asset('images/pcnl-logo.png') ?>" alt="PCNL logo" class="h-9 w-auto sm:h-10">
                <span class="text-xl font-semibold tracking-wide">PCNL</span>
            </a>

            <nav class="hidden items-center gap-8 text-base md:flex">
                <?php foreach ($navLinks as [$label, $href, $iconName]): ?>
                    <a href="<?= htmlspecialchars($href) ?>"
                    class="flex items-center gap-2 border-b-2 pb-1 <?= $href === $currentPage ? 'border-orange-500 font-semibold' : 'border-transparent hover:text-orange-400' ?>">
                        <?= icon($iconName, 'h-5 w-5') ?>
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <button type="button" id="menu-btn" aria-label="Menu" aria-expanded="false" class="p-2 md:hidden">
                <?= icon('bars', 'h-7 w-7') ?>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-white/10 px-6 pb-3 md:hidden">
            <?php foreach ($navLinks as [$label, $href, $iconName]): ?>
                <a href="<?= htmlspecialchars($href) ?>"
                class="flex items-center gap-3 rounded px-3 py-3 text-lg <?= $href === $currentPage ? 'font-semibold text-orange-400' : 'hover:bg-white/10' ?>">
                    <?= icon($iconName, 'h-5 w-5') ?>
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </header>
<div class="mx-auto min-h-screen max-w-5xl bg-white shadow-xl">
<main class="px-4 py-8 sm:px-8">