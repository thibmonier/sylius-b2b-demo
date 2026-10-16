<?php

declare(strict_types=1);

// Stand-in for an internal service of the demo, run by `php -S` during the MCP guard test: /redirect sends the client to
// REDIRECT_TARGET (a host outside the demo), /hit records that somebody got there (CAPTURE_FILE).
if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/hit')) {
    file_put_contents((string) getenv('CAPTURE_FILE'), ($_SERVER['HTTP_HOST'] ?? '') . "\n", \FILE_APPEND);
    echo 'reached';

    return;
}

header('Location: ' . getenv('REDIRECT_TARGET'), true, 302);
