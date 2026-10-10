<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/borrowings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['borrowingId'])) {
    header('Location: profile.php');
    exit;
}

$user = currentUser();
$result = $user['role'] === 'student'
    ? returnBook($user['id'], (string) $_POST['borrowingId'])
    : 'error';

header('Location: profile.php?result=' . $result);
exit;