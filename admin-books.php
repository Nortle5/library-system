<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Manage Books';
$area = 'admin';

$books = require __DIR__ . '/includes/sample_books.php';

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-800">Manage Books</h1>

    <button type="button" id="add-book-btn" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + Add book
    </button>
</div>

<div class="overflow-x-auto rounded-lg bg-white shadow">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-gray-600">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Author</th>
                <th class="px-4 py-3">Genre</th>
                <th class="px-4 py-3">Available</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr class="border-b border-gray-200 last:border-0">
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($book['title']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($book['author']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($book['genre']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= $book['available'] ?> of <?= $book['total'] ?></td>
                    <td class="px-4 py-3 text-right">
                    
                        <button type="button"
                                class="edit-book-btn mr-3 text-blue-600 hover:underline"
                                data-id="<?= (int) $book['id'] ?>"
                                data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>"
                                data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES) ?>"
                                data-genre="<?= htmlspecialchars($book['genre'], ENT_QUOTES) ?>"
                                data-copies="<?= (int) $book['total'] ?>">
                            Edit
                        </button>

                        <form method="post" action="admin-books.php" class="inline">
                            <input type="hidden" name="id" value="<?= $book['id'] ?>">
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="book-dialog" class="m-auto w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
    <form method="post" action="admin-books.php" novalidate class="space-y-4 p-6">
        <h2 id="book-dialog-title" class="text-lg font-semibold text-gray-800">Add book</h2>
        <input type="hidden" name="id" id="book-id" value="">

        <div>
            <label for="book-title" class="mb-1 block text-sm font-medium text-gray-700">Title</label>
            <input type="text" id="book-title" name="title" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <div>
            <label for="book-author" class="mb-1 block text-sm font-medium text-gray-700">Author</label>
            <input type="text" id="book-author" name="author" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <div>
            <label for="book-genre" class="mb-1 block text-sm font-medium text-gray-700">Genre</label>
            <input type="text" id="book-genre" name="genre" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <div>
            <label for="book-copies" class="mb-1 block text-sm font-medium text-gray-700">Total copies</label>
            <input type="number" id="book-copies" name="totalCopies" min="1" value="1" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" id="book-cancel" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Save book
            </button>
        </div>
    </form>
</dialog>

<script>
    const bookDialog = document.getElementById('book-dialog');
    const bookForm = bookDialog.querySelector('form');
    const dialogTitle = document.getElementById('book-dialog-title');

    function openBookDialog(book) {
        bookForm.reset();
        if (book) {
            dialogTitle.textContent = 'Edit book';
            document.getElementById('book-id').value = book.id;
            document.getElementById('book-title').value = book.title;
            document.getElementById('book-author').value = book.author;
            document.getElementById('book-genre').value = book.genre;
            document.getElementById('book-copies').value = book.copies;
        } else {
            dialogTitle.textContent = 'Add book';
        }
        bookDialog.showModal();
    }

    document.getElementById('add-book-btn').addEventListener('click', () => openBookDialog(null));
    document.getElementById('book-cancel').addEventListener('click', () => bookDialog.close());

    document.querySelectorAll('.edit-book-btn').forEach((btn) => {
        btn.addEventListener('click', () => openBookDialog({
            id: btn.dataset.id,
            title: btn.dataset.title,
            author: btn.dataset.author,
            genre: btn.dataset.genre,
            copies: btn.dataset.copies,
        }));
    });
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>