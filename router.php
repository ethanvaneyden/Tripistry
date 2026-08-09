<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__ . '/client';
$file = realpath($root . $uri);

if ($file && is_file($file) && str_starts_with($file, realpath($root))) {
    return false;
}

if ($uri === '/' || $uri === '' || is_dir($root . $uri)) {
    readfile($root . '/index.html');
    return true;
}

require __DIR__ . '/server/api.php';
