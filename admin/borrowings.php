<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

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
        'borrowedAt' => $r['borrowedAt']->toDateTime()->setTimezone($tz)->format('M j, Y'),
        'returnedAt' => $r['returnedAt'] ? $r['returnedAt']->toDateTime()->setTimezone($tz)->format('M j, Y') : null,
        'status'     => $r['status'],
    ];
}

$activeCount = count(array_filter($borrowings, fn($b) => $b['status'] === 'borrowed'));
$totalCount = count($borrowings);
$returnedCount = $totalCount - $activeCount;

include __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-6 text-2xl font-bold text-gray-800">Borrowings</h1>

<!-- Summary -->
<section class="mb-6 grid grid-cols-3 gap-3 sm:gap-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-orange-500"><?= $activeCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Currently borrowed</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-green-600"><?= $returnedCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Returned</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-gray-800"><?= $totalCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Total records</p>
    </div>
</section>

<!-- Filter tabs -->
<div class="mb-4 flex flex-wrap gap-2">
    <button type="button" data-filter="all"
            class="rounded-full bg-orange-500 px-4 py-1.5 text-sm font-medium text-white">All</button>
    <button type="button" data-filter="borrowed"
            class="rounded-full bg-gray-100 px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">Borrowed</button>
    <button type="button" data-filter="returned"
            class="rounded-full bg-gray-100 px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">Returned</button>
</div>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full min-w-[40rem] text-left text-sm">
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
                <tr data-status="<?= htmlspecialchars($b['status']) ?>"
                    class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($b['student']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['studentId']) ?></td>
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($b['book']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['borrowedAt']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= $b['returnedAt'] ? htmlspecialchars($b['returnedAt']) : '—' ?></td>
                    <td class="px-4 py-3">
                        <?php if ($b['status'] === 'borrowed'): ?>
                            <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-medium text-orange-700">Borrowed</span>
                        <?php else: ?>
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">Returned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr id="no-records" class="<?= empty($borrowings) ? '' : 'hidden' ?>">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No records to show.</td>
            </tr>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>