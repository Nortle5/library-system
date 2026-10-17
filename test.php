<?php
require __DIR__ . '/vendor/autoload.php';

$uri = getenv('MONGO_URI');
$dbName = getenv('DB_NAME') ?: 'library';

if (!$uri) {
    $config = require __DIR__ . '/config.php';
    $uri = $config['mongo_uri'];
    $dbName = $config['db_name'];
}

$client = new MongoDB\Client($uri);
$collection = $client->selectCollection($dbName, 'connection_test');

$result = $collection->insertOne(['message' => 'hello from PHP', 'time' => date('c')]);
echo 'Inserted ID: ' . $result->getInsertedId();