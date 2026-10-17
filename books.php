<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Books';

$q = trim($_GET['q'] ?? '');
$messages = [
    'borrowed'    => ['green', 'Book borrowed! You can see it on your profile.'],
    'already'     => ['red',   'You already have a copy of that book.'],
    'unavailable' => ['red',   'Sorry, all copies were just taken.'],
    'notstudent'  => ['red',   'Only student accounts can borrow books.'],
    'error'       => ['red',   'Something went wrong. Please try again.'],
];
$flash = $messages[$_GET['result'] ?? ''] ?? null;

$filter = [];

if ($q !== '') {
    $regex = new MongoDB\BSON\Regex(preg_quote($q, '/'), 'i');
    $filter = ['$or' => [
        ['title'  => $regex],
        ['author' => $regex],
        ['genre'  => $regex],
    ]];
}

$books = [];
foreach (getDb()->selectCollection('books')->find($filter, ['sort' => ['title' => 1]]) as $doc) {
    $books[] = [
        'id'        => (string) $doc['_id'],
        'title'     => $doc['title'],
        'author'    => $doc['author'],
        'genre'     => $doc['genre'],
        'total'     => $doc['totalCopies'],
        'available' => $doc['availableCopies'],
    ];
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="mb-4 rounded border border-<?= $flash[0] ?>-300 bg-<?= $flash[0] ?>-50 p-3 text-sm text-<?= $flash[0] ?>-700">
        <?= htmlspecialchars($flash[1]) ?>
    </div>
<?php endif; ?>

<h1 class="mb-6 text-2xl font-semibold text-gray-800">Browse Books</h1>

<form method="get" action="books.php" class="mb-6 flex gap-2">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by title, author, or genre"
           class="w-full rounded border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:outline-none">
    <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">
        Search
    </button>
</form>

<?php if (empty($books)): ?>
    <p class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow">
        <?= $q !== '' ? 'No books match your search.' : 'There are no books yet.' ?>
    </p>
<?php else: ?>
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
                    <button type="button"
                            class="details-btn text-sm font-medium text-blue-600 hover:underline"
                            data-id="<?= htmlspecialchars($book['id']) ?>"
                            data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>"
                            data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES) ?>"
                            data-genre="<?= htmlspecialchars($book['genre'], ENT_QUOTES) ?>"
                            data-total="<?= (int) $book['total'] ?>"
                            data-available="<?= (int) $book['available'] ?>">
                        View details
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>    
<?php endif; ?>

<dialog id="details-dialog" class="m-auto w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
    <div class="p-6">
        <h2 id="d-title" class="text-xl font-semibold text-gray-800"></h2>
        <p id="d-author" class="mt-1 text-gray-600"></p>
        <p id="d-genre" class="mt-3 inline-block rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700"></p>

        <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Total copies</dt>
                <dd id="d-total" class="font-medium text-gray-800"></dd>
            </div>
            <div>
                <dt class="text-gray-500">Available now</dt>
                <dd id="d-available" class="font-medium"></dd>
            </div>
        </dl>

        <form method="post" action="borrow.php" class="mt-6 flex justify-end gap-2">
            <input type="hidden" name="bookId" id="d-id" value="">
            <button type="button" id="d-close" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Close
            </button>
            <button type="submit" id="d-borrow" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Borrow this book
            </button>
        </form>
    </div>
</dialog>

<script>
    const detailsDialog = document.getElementById('details-dialog');
    const borrowBtn = document.getElementById('d-borrow');

    document.querySelectorAll('.details-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const available = parseInt(btn.dataset.available, 10);

            document.getElementById('d-id').value = btn.dataset.id;
            document.getElementById('d-title').textContent = btn.dataset.title;
            document.getElementById('d-author').textContent = 'by ' + btn.dataset.author;
            document.getElementById('d-genre').textContent = btn.dataset.genre;
            document.getElementById('d-total').textContent = btn.dataset.total;

            const availableEl = document.getElementById('d-available');
            availableEl.textContent = available;
            availableEl.className = 'font-medium ' + (available > 0 ? 'text-green-700' : 'text-red-600');

            borrowBtn.disabled = available < 1;
            borrowBtn.textContent = available < 1 ? 'All copies borrowed' : 'Borrow this book';
            borrowBtn.className = available < 1
                ? 'cursor-not-allowed rounded bg-gray-300 px-4 py-2 text-sm font-medium text-gray-500'
                : 'rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700';

            detailsDialog.showModal();
        });
    });

    document.getElementById('d-close').addEventListener('click', () => detailsDialog.close());
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>