<?php

declare(strict_types=1);

// Router of bin/local-server: what the front controller expects of a web server, which the PHP built-in server does not give by itself.
// An existing file of public/ is served as it is (build, media, thumbnails); everything else goes through public/index.php.
$root = \dirname(__DIR__) . '/public';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url(\is_string($uri) ? $uri : '/', \PHP_URL_PATH) ?: '/';

if (is_file($root . $path) && !str_ends_with($path, '.php')) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
chdir($root);
require $root . '/index.php';
