<?php

declare(strict_types=1);

// Stand-in for an internal service of the demo, run by `php -S` during the MCP guard test: /redirect sends the client to
// REDIRECT_TARGET (a host outside the demo), /hit records that somebody got there (CAPTURE_FILE).
$uri = $_SERVER['REQUEST_URI'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if (\is_string($uri) && \is_string($host) && str_starts_with($uri, '/hit')) {
    file_put_contents((string) getenv('CAPTURE_FILE'), $host . "\n", \FILE_APPEND);
    echo 'reached';

    return;
}

header('Location: ' . getenv('REDIRECT_TARGET'), true, 302);
