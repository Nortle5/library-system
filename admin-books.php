<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Manage Books';
$area = 'admin';

$errors = [];
$booksCol = getDb()->selectCollection('books');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && ($_POST['id'] ?? '') === '') {
    $title  = trim($_POST['title']);
    $author = trim($_POST['author'] ?? '');
    $genre  = trim($_POST['genre'] ?? '');
    $copies = trim($_POST['totalCopies'] ?? '');

    if ($title === '' || mb_strlen($title) > 150) {
        $errors[] = 'Title is required (up to 150 characters).';
    }
    if ($author === '' || mb_strlen($author) > 100) {
        $errors[] = 'Author is required (up to 100 characters).';
    }
    if ($genre === '' || mb_strlen($genre) > 50) {
        $errors[] = 'Genre is required (up to 50 characters).';
    }
    if (filter_var($copies, FILTER_VALIDATE_INT) === false || $copies < 1 || $copies > 1000) {
        $errors[] = 'Total copies must be a whole number from 1 to 1000.';
    }

    if (empty($errors)) {
        $booksCol->insertOne([
            'title'           => $title,
            'author'          => $author,
            'genre'           => $genre,
            'totalCopies'     => (int) $copies,
            'availableCopies' => (int) $copies,
            'createdAt'       => new MongoDB\BSON\UTCDateTime(),
        ]);
        header('Location: admin-books.php?added=1');
        exit;
    }
}

$books = [];
foreach ($booksCol->find([], ['sort' => ['title' => 1]]) as $doc) {
    $books[] = [
        'id'        => (string) $doc['_id'],
        'title'     => $doc['title'],
        'author'    => $doc['author'],
        'genre'     => $doc['genre'],
        'total'     => $doc['totalCopies'],
        'available' => $doc['availableCopies'],
    ];
}

$borrowers = [];
$usersCol = getDb()->selectCollection('users');
foreach (getDb()->selectCollection('borrowings')->find(['status' => 'borrowed']) as $b) {
    $u = $usersCol->findOne(['_id' => $b['userId']]);
    $name = $u ? $u['firstName'] . ' ' . $u['lastName'] : 'Unknown student';
    $borrowers[(string) $b['bookId']][] = $name;
}

include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['added'])): ?>
    <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-700">Book added.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
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
                <th class="px-4 py-3">Borrowed by</th>
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
                    <td class="px-4 py-3 text-gray-600">
                        <?php if (empty($borrowers[$book['id']])): ?>
                            —
                        <?php else: ?>
                            <?php foreach ($borrowers[$book['id']] as $borrowerName): ?>
                                <div><?= htmlspecialchars($borrowerName) ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                    
                        <button type="button"
                                class="edit-book-btn mr-3 text-blue-600 hover:underline" data-id="<?= htmlspecialchars($book['id']) ?>"
                                data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>"
                                data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES) ?>"
                                data-genre="<?= htmlspecialchars($book['genre'], ENT_QUOTES) ?>"
                                data-copies="<?= (int) $book['total'] ?>">
                            Edit
                        </button>

                        <form method="post" action="admin-books.php" class="inline">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
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