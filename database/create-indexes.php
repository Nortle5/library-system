<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this from the command line.');
}

require_once __DIR__ . '/../includes/db.php';

$users = getDb()->selectCollection('users');
$users->createIndex(['username' => 1], ['unique' => true]);
$users->createIndex(['studentId' => 1], ['unique' => true]);

echo 'Indexes created.' . PHP_EOL;