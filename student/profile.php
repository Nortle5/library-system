<?php
    require_once __DIR__ . '/../includes/auth.php';
    requireLogin();
    require_once __DIR__ . '/../includes/db.php';

    $sessionUser = currentUser();
    if ($sessionUser['role'] !== 'student') {
        header('Location: ' . url('admin_books'));
        exit;
    }

    $pageTitle = 'My Profile';
    $db = getDb();
    $userId = new MongoDB\BSON\ObjectId($sessionUser['id']);

    $student = $db->selectCollection('users')->findOne(['_id' => $userId]);
    if (!$student) {
        header('Location: ' . url('logout'));
        exit;
    }

    $bookTitles = [];
    foreach ($db->selectCollection('books')->find() as $b) {
        $bookTitles[(string) $b['_id']] = $b['title'];
    }

    $tz = new DateTimeZone('Asia/Manila');
    $now = new DateTime('now', $tz);

    $borrowings = [];
    foreach ($db->selectCollection('borrowings')->find(['userId' => $userId], ['sort' => ['borrowedAt' => -1]]) as $r) {
        $borrowedAt = $r['borrowedAt']->toDateTime()->setTimezone($tz);
        $days = (int) $now->diff($borrowedAt)->days;

        $borrowings[] = [
            'id'         => (string) $r['_id'],
            'book'       => $bookTitles[(string) $r['bookId']] ?? 'Deleted book',
            'borrowedAt' => $borrowedAt->format('M j, Y'),
            'daysAgo'    => $days === 0 ? 'today' : ($days === 1 ? '1 day ago' : "$days days ago"),
            'returnedAt' => $r['returnedAt'] ? $r['returnedAt']->toDateTime()->setTimezone($tz)->format('M j, Y') : null,
            'status'     => $r['status'],
        ];
    }

    $current = array_values(array_filter($borrowings, fn($b) => $b['status'] === 'borrowed'));
    $currentCount = count($current);
    $totalCount = count($borrowings);
    $returnedCount = $totalCount - $currentCount;

    $initials = mb_strtoupper(mb_substr($student['firstName'] ?? '', 0, 1) . mb_substr($student['lastName'] ?? '', 0, 1));
    if ($initials === '') {
        $initials = mb_strtoupper(mb_substr($student['username'], 0, 1));
    }

    $year = (int) ($student['yearLevel'] ?? 0);
    $ordinals = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];
    $yearLabel = $year ? ($ordinals[$year] ?? $year . 'th') . ' Year' : null;

    $messages = [
        'returned' => ['green', 'Book returned. Thank you!'],
        'notfound' => ['red',   'That borrowing was not found or was already returned.'],
        'error'    => ['red',   'Something went wrong. Please try again.'],
    ];
    $flash = $messages[$_GET['result'] ?? ''] ?? null;
    if ($flash) {
    toast($flash[0] === 'green' ? 'success' : 'error', $flash[1]);
    }

    include __DIR__ . '/../includes/header.php';
?>

    <!-- Banner -->
<section class="flex flex-col items-center gap-5 rounded-2xl bg-gray-900 p-6 text-center text-white shadow sm:flex-row sm:p-8 sm:text-left">
    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-orange-500 text-4xl font-bold">
        <?= htmlspecialchars($initials) ?>
    </div>

    <div>
        <h1 class="text-3xl font-bold"><?= htmlspecialchars(($student['firstName'] ?? '') . ' ' . ($student['lastName'] ?? '')) ?></h1>
            <p class="mt-1 text-gray-300">@<?= htmlspecialchars($student['username']) ?></p>
        <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm sm:justify-start">
            <?php if (!empty($student['course'])): ?>
                <span class="rounded-full bg-white/10 px-3 py-1"><?= htmlspecialchars($student['course']) ?></span>
            <?php endif; ?>
            <?php if ($yearLabel): ?>
                <span class="rounded-full bg-white/10 px-3 py-1"><?= htmlspecialchars($yearLabel) ?></span>
            <?php endif; ?>
            <?php if (!empty($student['studentId'])): ?>
                <span class="rounded-full bg-white/10 px-3 py-1">ID <?= htmlspecialchars($student['studentId']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

    <!-- Stats -->
<section class="mt-6 grid grid-cols-3 gap-3 sm:gap-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-orange-500"><?= $currentCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Currently borrowed</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-gray-800"><?= $returnedCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Returned</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
        <p class="text-3xl font-bold text-gray-800"><?= $totalCount ?></p>
        <p class="mt-1 text-xs text-gray-500 sm:text-sm">Total borrowed</p>
    </div>
</section>

    <!-- Currently borrowed -->
<section class="mt-8">
    <h2 class="mb-3 text-xl font-semibold text-gray-800">Currently borrowed</h2>

    <?php if (empty($current)): ?>
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
            <p class="text-gray-600">You have no borrowed books right now.</p>
            <a href="<?= url('books') ?>" cclass="mt-4 inline-flex items-center gap-2 rounded bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600">
                <?= icon('book-open', 'h-4 w-4') ?>
                Browse books
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($current as $b): ?>
                <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div>
                        <p class="break-words text-lg font-semibold text-gray-800"><?= htmlspecialchars($b['book']) ?></p>
                        <p class="mt-1 text-sm text-gray-500">
                            Borrowed <?= htmlspecialchars($b['daysAgo']) ?> &middot; <?= htmlspecialchars($b['borrowedAt']) ?>
                        </p>
                    </div>
                    <form method="post" action="<?= url('return') ?>" class="mt-4" onsubmit="return confirm('Return this book?');">
                        <input type="hidden" name="borrowingId" value="<?= htmlspecialchars($b['id']) ?>">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">
                            <?= icon('return', 'h-4 w-4') ?>
                            Return book
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

    <!-- History -->
    <section class="mt-8">
        <h2 class="mb-3 text-xl font-semibold text-gray-800">Borrowing history</h2>
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full min-w-[32rem] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Book</th>
                        <th class="px-4 py-3">Borrowed</th>
                        <th class="px-4 py-3">Returned</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowings)): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Nothing here yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($borrowings as $b): ?>
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($b['book']) ?></td>
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
                </tbody>
            </table>
        </div>
    </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>