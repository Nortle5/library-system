<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: admin-books.php');
    exit;
}

$result = 'error';

try {
    $bookId = new MongoDB\BSON\ObjectId((string) $_POST['id']);
    $db = getDb();

    $stillOut = $db->selectCollection('borrowings')->countDocuments(['bookId' => $bookId, 'status' => 'borrowed']);

    if ($stillOut > 0) {
        $result = 'blocked';
    } else {
        $db->selectCollection('books')->deleteOne(['_id' => $bookId]);
        $result = 'deleted';
    }
} catch (Throwable $e) {
    $result = 'error';
}

header('Location: admin-books.php?result=' . $result);
exit;