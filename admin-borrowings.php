<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Borrowings';
$area = 'admin';

$db = getDb();

$students = [];
foreach ($db->selectCollection('users')->find(['role' => 'student']) as $u) {
    $students[(string) $u['_id']] = $u;
}
$bookTitles = [];
foreach ($db->selectCollection('books')->find() as $b) {
    $bookTitles[(string) $b['_id']] = $b['title'];
}

$tz = new DateTimeZone('Asia/Manila');
$borrowings = [];
foreach ($db->selectCollection('borrowings')->find([], ['sort' => ['borrowedAt' => -1]]) as $r) {
    $student = $students[(string) $r['userId']] ?? null;
    $borrowings[] = [
        'student'    => $student ? $student['firstName'] . ' ' . $student['lastName'] : 'Unknown student',
        'studentId'  => $student['studentId'] ?? '—',
        'book'       => $bookTitles[(string) $r['bookId']] ?? 'Deleted book',
        'borrowedAt' => $r['borrowedAt']->toDateTime()->setTimezone($tz)->format('Y-m-d'),
        'returnedAt' => $r['returnedAt'] ? $r['returnedAt']->toDateTime()->setTimezone($tz)->format('Y-m-d') : null,
        'status'     => $r['status'],
    ];
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
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($b['student']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['studentId']) ?></td>
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($b['book']) ?></td>
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