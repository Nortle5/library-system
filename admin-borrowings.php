<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Borrowings';
$area = 'admin';

$books = require __DIR__ . '/includes/sample_books.php';

// Sample data for the front end. The database will replace this later.
$students = [
    '2023-00123' => 'Juan Dela Cruz',
    '2023-00456' => 'Maria Santos',
    '2022-00789' => 'Pedro Reyes',
];

$borrowings = [
    ['studentId' => '2023-00123', 'bookId' => 1, 'borrowedAt' => '2026-10-05', 'returnedAt' => null,         'status' => 'borrowed'],
    ['studentId' => '2023-00123', 'bookId' => 4, 'borrowedAt' => '2026-10-07', 'returnedAt' => null,         'status' => 'borrowed'],
    ['studentId' => '2023-00456', 'bookId' => 3, 'borrowedAt' => '2026-10-02', 'returnedAt' => null,         'status' => 'borrowed'],
    ['studentId' => '2022-00789', 'bookId' => 2, 'borrowedAt' => '2026-09-12', 'returnedAt' => '2026-09-19', 'status' => 'returned'],
    ['studentId' => '2023-00456', 'bookId' => 5, 'borrowedAt' => '2026-09-01', 'returnedAt' => '2026-09-08', 'status' => 'returned'],
];

function bookTitle(array $books, int $bookId): string
{
    foreach ($books as $book) {
        if ($book['id'] === $bookId) {
            return $book['title'];
        }
    }
    return 'Unknown book';
}

$activeCount = count(array_filter($borrowings, fn($b) => $b['status'] === 'borrowed'));

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-800">Borrowings</h1>
    <p class="text-sm text-gray-600"><?= $activeCount ?> currently borrowed</p>
</div>

<div class="overflow-x-auto rounded-lg bg-white shadow">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-gray-600">
            <tr>
                <th class="px-4 py-3">Student</th>
                <th class="px-4 py-3">Student ID</th>
                <th class="px-4 py-3">Book</th>
                <th class="px-4 py-3">Borrowed</th>
                <th class="px-4 py-3">Returned</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowings as $b): ?>
                <tr class="border-b border-gray-200 last:border-0">
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($students[$b['studentId']] ?? 'Unknown student') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['studentId']) ?></td>
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars(bookTitle($books, $b['bookId'])) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['borrowedAt']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= $b['returnedAt'] ? htmlspecialchars($b['returnedAt']) : '—' ?></td>
                    <td class="px-4 py-3">
                        <?php if ($b['status'] === 'borrowed'): ?>
                            <span class="rounded bg-yellow-50 px-2 py-0.5 text-xs text-yellow-700">Borrowed</span>
                        <?php else: ?>
                            <span class="rounded bg-green-50 px-2 py-0.5 text-xs text-green-700">Returned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>