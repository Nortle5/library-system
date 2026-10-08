<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$books = require __DIR__ . '/includes/sample_books.php';

$id = (int) ($_GET['id'] ?? 0);
$book = null;
foreach ($books as $candidate) {
    if ($candidate['id'] === $id) {
        $book = $candidate;
        break;
    }
}

$pageTitle = $book ? $book['title'] : 'Book not found';
include __DIR__ . '/includes/header.php';
?>

<a href="books.php" class="mb-4 inline-block text-sm text-blue-600 hover:underline">&larr; Back to books</a>

<?php if (!$book): ?>
    <div class="rounded-lg bg-white p-6 shadow">
        <h1 class="text-xl font-semibold text-gray-800">Book not found</h1>
        <p class="mt-2 text-sm text-gray-600">That book doesn't exist or was removed.</p>
    </div>
<?php else: ?>
    <div class="rounded-lg bg-white p-6 shadow">
        <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($book['title']) ?></h1>
        <p class="mt-1 text-gray-600">by <?= htmlspecialchars($book['author']) ?></p>
        <p class="mt-3 inline-block rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">
            <?= htmlspecialchars($book['genre']) ?>
        </p>

        <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Total copies</dt>
                <dd class="font-medium text-gray-800"><?= $book['total'] ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Available now</dt>
                <dd class="font-medium <?= $book['available'] > 0 ? 'text-green-700' : 'text-red-600' ?>">
                    <?= $book['available'] ?>
                </dd>
            </div>
        </dl>

        <form method="post" action="book-details.php?id=<?= $book['id'] ?>" class="mt-6">
            <?php if ($book['available'] > 0): ?>
                <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
                    Borrow this book
                </button>
            <?php else: ?>
                <button type="button" disabled
                        class="cursor-not-allowed rounded bg-gray-300 px-5 py-2 font-medium text-gray-500">
                    All copies borrowed
                </button>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>