<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/books.php';

$q = mb_substr(trim($_GET['q'] ?? ''), 0, 100);

header('Content-Type: text/html; charset=utf-8');
renderBookCards(searchBooks($q), $q);