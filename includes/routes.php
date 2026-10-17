<?php
require_once __DIR__ . '/paths.php';
const ROUTES = [
    'home'             => '/home.php',
    'login'            => '/login.php',
    'register'         => '/register.php',
    'logout'           => '/logout.php',
    'books'            => '/student/books.php',
    'profile'          => '/student/profile.php',
    'admin_books'      => '/admin/books.php',
    'admin_borrowings' => '/admin/borrowings.php',
    'borrow'           => '/actions/borrow.php',
    'return'           => '/actions/return.php',
    'delete_book'      => '/actions/delete-book.php',
    'search_books'     => '/actions/search-books.php',
];
function url(string $name): string
{
    return BASE_URL . ROUTES[$name];
}
function asset(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}
