<?php
    require_once __DIR__ . '/includes/auth.php';
    requireLogin();
    require_once __DIR__ . '/includes/db.php';

    $sessionUser = currentUser();
    if ($sessionUser['role'] !== 'student') {
        header('Location: admin-books.php');
        exit;
    }

    $pageTitle = 'My Profile';
    $db = getDb();
    $userId = new MongoDB\BSON\ObjectId($sessionUser['id']);

    $student = $db->selectCollection('users')->findOne(['_id' => $userId]);
    if (!$student) {
        header('Location: logout.php');
        exit;
    }

    $bookTitles = [];
    foreach ($db->selectCollection('books')->find() as $b) {
        $bookTitles[(string) $b['_id']] = $b['title'];
    }

    $tz = new DateTimeZone('Asia/Manila');
    $borrowings = [];
    foreach ($db->selectCollection('borrowings')->find(['userId' => $userId], ['sort' => ['borrowedAt' => -1]]) as $r) {
        $borrowings[] = [
            'id'         => (string) $r['_id'],
            'book'       => $bookTitles[(string) $r['bookId']] ?? 'Deleted book',
            'borrowedAt' => $r['borrowedAt']->toDateTime()->setTimezone($tz)->format('Y-m-d'),
            'returnedAt' => $r['returnedAt'] ? $r['returnedAt']->toDateTime()->setTimezone($tz)->format('Y-m-d') : null,
            'status'     => $r['status'],
        ];
    }
    $current = array_filter($borrowings, fn($b) => $b['status'] === 'borrowed');

    $messages = [
        'returned' => ['green', 'Book returned. Thank you!'],
        'notfound' => ['red',   'That borrowing was not found or was already returned.'],
        'error'    => ['red',   'Something went wrong. Please try again.'],
    ];
    $flash = $messages[$_GET['result'] ?? ''] ?? null;

    include __DIR__ . '/includes/header.php';
    ?>

    <?php if ($flash): ?>
        <div class="mb-4 rounded border border-<?= $flash[0] ?>-300 bg-<?= $flash[0] ?>-50 p-3 text-sm text-<?= $flash[0] ?>-700">
            <?= htmlspecialchars($flash[1]) ?>
        </div>
    <?php endif; ?>

    <h1 class="mb-6 text-2xl font-semibold text-gray-800">My Profile</h1>

    <section class="mb-8 rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-800">
            <?= htmlspecialchars($student['firstName'] . ' ' . $student['lastName']) ?>
        </h2>
        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
            <div>
                <dt class="text-gray-500">Student ID</dt>
                <dd class="font-medium text-gray-800"><?= htmlspecialchars($student['studentId'] ?? '—') ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Course</dt>
                <dd class="font-medium text-gray-800"><?= htmlspecialchars($student['course'] ?? '—') ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Year level</dt>
                <dd class="font-medium text-gray-800"><?= (int) ($student['yearLevel'] ?? 0) ?: '—' ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Username</dt>
                <dd class="font-medium text-gray-800"><?= htmlspecialchars($student['username']) ?></dd>
            </div>
        </dl>
    </section>

    <section class="mb-8">
        <h2 class="mb-3 text-lg font-semibold text-gray-800">Currently borrowed</h2>
        <?php if (empty($current)): ?>
            <p class="rounded-lg bg-white p-5 text-sm text-gray-600 shadow">You have no borrowed books right now.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($current as $b): ?>
                    <div class="flex items-center justify-between rounded-lg bg-white p-5 shadow">
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($b['book']) ?></p>
                            <p class="text-sm text-gray-500">Borrowed on <?= htmlspecialchars($b['borrowedAt']) ?></p>
                        </div>
                        <form method="post" action="return.php">
                            <input type="hidden" name="borrowingId" value="<?= htmlspecialchars($b['id']) ?>">
                            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Return
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="mb-3 text-lg font-semibold text-gray-800">Borrowing history</h2>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="w-full text-left text-sm">
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
                        <tr><td colspan="4" class="px-4 py-4 text-gray-600">Nothing here yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($borrowings as $b): ?>
                        <tr class="border-b border-gray-200 last:border-0">
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
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>