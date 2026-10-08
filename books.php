<?php
$pageTitle = 'Books';

// Sample data for the front end. The database will replace this later.
$books = require __DIR__ . '/includes/sample_books.php';

include __DIR__ . '/includes/header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-800">Browse Books</h1>

<form method="get" action="books.php" class="mb-6 flex gap-2">
    <input type="text" name="q" placeholder="Search by title, author, or genre"
           class="w-full rounded border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:outline-none">
    <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
        Search
    </button>
</form>

<div class="grid gap-4 sm:grid-cols-2">
    <?php foreach ($books as $book): ?>
        <div class="rounded-lg bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($book['title']) ?></h2>
            <p class="text-sm text-gray-600">by <?= htmlspecialchars($book['author']) ?></p>
            <p class="mt-2 inline-block rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">
                <?= htmlspecialchars($book['genre']) ?>
            </p>

            <div class="mt-4 flex items-center justify-between">
                <?php if ($book['available'] > 0): ?>
                    <span class="text-sm text-green-700"><?= $book['available'] ?> of <?= $book['total'] ?> available</span>
                <?php else: ?>
                    <span class="text-sm text-red-600">All copies borrowed</span>
                <?php endif; ?>
                <a href="book-details.php?id=<?= $book['id'] ?>" class="text-sm font-medium text-blue-600 hover:underline">
                    View details
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>