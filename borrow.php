<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/borrowings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bookId'])) {
    header('Location: books.php');
    exit;
}

$user = currentUser();
$result = $user['role'] === 'student'
    ? borrowBook($user['id'], (string) $_POST['bookId'])
    : 'notstudent';

header('Location: books.php?result=' . $result);
exit;