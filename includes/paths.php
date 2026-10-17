<?php
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    define('BASE_URL', str_replace($docRoot, '', $projectDir));
}
