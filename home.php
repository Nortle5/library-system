<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$isAdmin = currentUser()['role'] === 'admin';

$heroImage = 'images/library-home.jpg';
$logoImage = 'images/pcnl-logo.png';

$navLinks = $isAdmin
    ? [['Home', 'home.php'], ['Manage Books', 'admin-books.php'], ['Borrowings', 'admin-borrowings.php'], ['Log out', 'logout.php']]
    : [['Home', 'home.php'], ['Books', 'books.php'], ['Profile', 'profile.php'], ['Log out', 'logout.php']];

$buttons = array_slice($navLinks, 1, 2);

$intro = $isAdmin
    ? 'Add and update the collection, and keep track of who has borrowed what.'
    : 'Search the collection, borrow the books you need, and keep track of everything you have borrowed.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home | Library System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-900">
<section class="relative flex min-h-screen flex-col bg-cover bg-center text-white"
         style="background-image: url('<?= htmlspecialchars($heroImage) ?>');">
    <div class="absolute inset-0 bg-black/45"></div>

    <header class="relative z-10 flex items-center justify-between px-6 py-4 sm:px-10 sm:py-5">
        <a href="home.php" class="flex items-center gap-4">
            <img src="<?= htmlspecialchars($logoImage) ?>" alt="PCNL logo" class="h-9 w-auto sm:h-11">
            <span class="text-xl font-semibold tracking-wide sm:text-2xl">PCNL</span>
        </a>

        <nav class="hidden items-center gap-8 text-base md:flex lg:text-lg">
            <?php foreach ($navLinks as $i => [$label, $href]): ?>
                <a href="<?= htmlspecialchars($href) ?>"
                   class="pb-1 <?= $i === 0 ? 'border-b-2 border-orange-500 font-semibold' : 'hover:text-orange-400' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <button type="button" id="menu-btn" aria-label="Menu" aria-expanded="false" class="space-y-2 p-2 md:hidden">
            <span class="block h-0.5 w-9 bg-white"></span>
            <span class="block h-0.5 w-9 bg-white"></span>
            <span class="block h-0.5 w-9 bg-white"></span>
        </button>
    </header>

    <div id="mobile-menu" class="relative z-10 mx-6 hidden rounded-xl bg-black/80 p-4 backdrop-blur md:hidden">
        <?php foreach ($navLinks as [$label, $href]): ?>
            <a href="<?= htmlspecialchars($href) ?>" class="block rounded px-3 py-3 text-lg hover:bg-white/10">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 text-center drop-shadow-lg">
        <h1 class="text-5xl font-bold uppercase tracking-wider sm:text-7xl lg:text-8xl">Welcome</h1>
        <p class="mt-4 text-lg font-semibold uppercase tracking-[0.25em] sm:text-2xl">to PCNL Library System</p>
        <p class="mt-6 max-w-2xl text-base text-gray-100 sm:text-lg"><?= htmlspecialchars($intro) ?></p>
    </div>

    <div class="relative z-10 flex flex-col items-center justify-center gap-4 px-6 pb-24 sm:flex-row sm:gap-6 sm:pb-44">
        <?php foreach ($buttons as $i => [$label, $href]): ?>
            <a href="<?= htmlspecialchars($href) ?>"
               class="w-full max-w-xs rounded px-10 py-3 text-center text-base font-bold uppercase tracking-widest transition sm:w-60 <?= $i === 0
                   ? 'bg-orange-500 text-white hover:bg-orange-600'
                   : 'border border-white/80 text-white hover:bg-white hover:text-gray-900' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<script src="js/script.js"></script>
</body>
</html>