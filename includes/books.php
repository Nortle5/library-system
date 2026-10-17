<?php
require_once __DIR__ . '/db.php';
function searchBooks(string $q): array
{
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

    return $books;
}

function renderBookCards(array $books, string $q): void{
    if (empty($books)) { ?>
        <p class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow">
            <?= $q !== '' ? 'No books match your search.' : 'There are no books yet.' ?>
        </p>
    <?php
        return;
    } ?>
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
<?php
}