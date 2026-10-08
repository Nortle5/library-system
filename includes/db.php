<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getDb(): MongoDB\Database
{
    static $db = null;

    if ($db === null) {
        $uri = getenv('MONGO_URI');
        $dbName = getenv('DB_NAME') ?: 'library';

        if (!$uri) {
            $config = require __DIR__ . '/../config.php';
            $uri = $config['mongo_uri'];
            $dbName = $config['db_name'];
        }

        $client = new MongoDB\Client($uri);
        $db = $client->selectDatabase($dbName);
    }

    return $db;
}