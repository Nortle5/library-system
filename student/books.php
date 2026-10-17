<?php
    require_once __DIR__ . '/../includes/auth.php';
    requireLogin();
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/books.php';

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
    if ($flash) {
    toast($flash[0] === 'green' ? 'success' : 'error', $flash[1]);
    }

    $books = searchBooks($q);

    include __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-800">Browse Books</h1>

<form method="get" action="<?= url('books') ?>" class="mb-6 flex gap-2">
    <input type="text" id="search-input" data-endpoint="<?= url('search_books') ?>" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by title, author, or genre"
           class="w-full rounded border border-gray-300 bg-white px-3 py-2 focus:border-orange-500 focus:outline-none">
    <button type="submit" class="inline-flex items-center gap-2 rounded bg-orange-500 px-5 py-2 font-medium text-white hover:bg-orange-600">
        <?= icon('search', 'h-5 w-5') ?>
        Search
    </button>
</form>

<div id="book-results">
    <?php renderBookCards($books, $q); ?>
</div>

<dialog id="details-dialog" class="m-auto w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
    <div class="p-6">
        <h2 id="d-title" class="text-xl font-semibold text-gray-800"></h2>
        <p id="d-author" class="mt-1 text-gray-600"></p>
        <p id="d-genre" class="mt-3 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700"></p>

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

        <form method="post" action="<?= url('borrow') ?>" class="mt-6 flex justify-end gap-2">
            <input type="hidden" name="bookId" id="d-id" value="">
            <button type="button" id="d-close" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Close
            </button>
            <button type="submit" id="d-borrow" class="rounded bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                Borrow this book
            </button>
        </form>
    </div>
</dialog>

<script>
    const detailsDialog = document.getElementById('details-dialog');
    const borrowBtn = document.getElementById('d-borrow');

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.details-btn');
        if (!btn) return;

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
            : 'rounded bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600';

        detailsDialog.showModal();
    });

    document.getElementById('d-close').addEventListener('click', () => detailsDialog.close());
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>