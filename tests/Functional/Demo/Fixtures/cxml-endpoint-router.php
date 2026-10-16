<?php

declare(strict_types=1);

// Stand-in for the PunchOut endpoint of the shop, run by `php -S` during the hardening tests: it records the
// headers of every call (CAPTURE_FILE) and answers a cXML success.
$headers = [];
foreach ($_SERVER as $name => $value) {
    if (str_starts_with($name, 'HTTP_')) {
        $headers[str_replace('_', '-', substr($name, 5))] = $value;
    }
}
$headers['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';
file_put_contents((string) getenv('CAPTURE_FILE'), json_encode($headers, \JSON_THROW_ON_ERROR) . "\n", \FILE_APPEND);

header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?><cXML><Response><Status code="200" text="OK">OK</Status></Response></cXML>';
