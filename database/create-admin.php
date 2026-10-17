<?php
// Run once from the command line: php database/create-admin.php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this from the command line.');
}

require_once __DIR__ . '/../includes/db.php';

$users = getDb()->selectCollection('users');

if ($users->findOne(['role' => 'admin'])) {
    exit('An admin account already exists.' . PHP_EOL);
}

echo 'Admin username: ';
$username = trim(fgets(STDIN));
echo 'Admin password (at least 8 characters): ';
$password = trim(fgets(STDIN));

if (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $username) || strlen($password) < 8) {
    exit('Invalid input. Username: 4 to 20 letters, numbers, or underscores. Password: at least 8 characters.' . PHP_EOL);
}

if ($users->findOne(['username' => $username])) {
    exit('That username is already taken by a student.' . PHP_EOL);
}

$users->insertOne([
    'firstName'    => 'Library',
    'lastName'     => 'Admin',
    'username'     => $username,
    'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
    'role'         => 'admin',
    'createdAt'    => new MongoDB\BSON\UTCDateTime(),
]);

echo 'Admin account created.' . PHP_EOL;