<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$isAdmin = currentUser()['role'] === 'admin';

$heroImage = asset('images/library-home.jpg');
$logoImage = asset('images/pcnl-logo.png');

$navLinks = $isAdmin
    ? [['Home', url('home'), 'home'], ['Manage Books', url('admin_books'), 'book-open'], ['Borrowings', url('admin_borrowings'), 'list'], ['Log out', url('logout'), 'logout']]
    : [['Home', url('home'), 'home'], ['Books', url('books'), 'book-open'], ['Profile', url('profile'), 'user'], ['Log out', url('logout'), 'logout']];

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
            <a href="<?= url('home') ?>" class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($logoImage) ?>" alt="PCNL logo" class="h-9 w-auto sm:h-11">
                <span class="text-xl font-semibold tracking-wide sm:text-2xl">PCNL</span>
            </a>

            <nav class="hidden items-center gap-8 text-base md:flex lg:text-lg">
                <?php foreach ($navLinks as $i => [$label, $href, $iconName]): ?>
                    <a href="<?= htmlspecialchars($href) ?>"
                    class="flex items-center gap-2 border-b-2 pb-1 <?= $i === 0 ? 'border-orange-500 font-semibold' : 'border-transparent hover:text-orange-400' ?>">
                        <?= icon($iconName, 'h-5 w-5') ?>
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <button type="button" id="menu-btn" aria-label="Menu" aria-expanded="false" class="p-2 md:hidden">
                <?= icon('bars', 'h-7 w-7') ?>
            </button>
        </header>

        <div id="mobile-menu" class="relative z-10 mx-6 mt-2 hidden rounded-xl bg-black/80 p-4 backdrop-blur md:hidden">
            <?php foreach ($navLinks as [$label, $href, $iconName]): ?>
                <a href="<?= htmlspecialchars($href) ?>" class="flex items-center gap-3 rounded px-3 py-3 text-lg hover:bg-white/10">
                    <?= icon($iconName, 'h-5 w-5') ?>
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
            <?php foreach ($buttons as $i => [$label, $href, $iconName]): ?>
                <a href="<?= htmlspecialchars($href) ?>"
                class="flex w-full max-w-xs items-center justify-center gap-3 whitespace-nowrap rounded px-6 py-3 text-center text-base font-bold uppercase tracking-widest transition sm:w-72 <?= $i === 0
                    ? 'bg-orange-500 text-white hover:bg-orange-600'
                    : 'border border-white/80 text-white hover:bg-white hover:text-gray-900' ?>">
                    <?= icon($iconName, 'h-6 w-6') ?>
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>