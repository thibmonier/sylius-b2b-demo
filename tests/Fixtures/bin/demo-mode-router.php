<?php

declare(strict_types=1);

/**
 * Router of `php -S` imitating the demo in one of its two access modes (DEMO_STUB_MODE=public|private), only for the
 * test of bin/verify-public-mode. DEMO_STUB_BREAK names one path that answers wrongly, to prove a check can fail.
 */
$mode = getenv('DEMO_STUB_MODE') ?: 'public';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
// A string key: PHP 8.5 deprecates null as an array offset (parse_url returns null or false on odd input).
$path = (string) parse_url(\is_string($requestUri) ? $requestUri : '/', \PHP_URL_PATH);
$broken = getenv('DEMO_STUB_BREAK') ?: null;
// US-207: DEMO_STUB_INDEXING=1 imitates the setting DEMO_INDEXING=1 (showcase pages open to robots, sitemap).
$indexing = '1' === getenv('DEMO_STUB_INDEXING');

$respond = static function (int $code, string $body = '', array $headers = []): void {
    http_response_code($code);
    foreach ($headers as $header) {
        header($header);
    }
    echo $body;
};

if (in_array($path, ['/health', '/healthz'], true)) {
    $respond(200, 'ok');

    return;
}
if ('private' === $mode) {
    $respond(401, '', ['WWW-Authenticate: Basic realm="demo"']);

    return;
}

$answers = [
    '/fr_FR/' => [200, 'home', ['X-Robots-Tag: noindex, nofollow']],
    '/fr_FR/register' => [403],
    '/fr_FR/checkout/address' => [403],
    '/fr_FR/quote-request' => [302, '', ['Location: /fr_FR/login']],
    '/robots.txt' => [200, "User-agent: *\nDisallow: /\n"],
    '/fr_FR/mcp' => [200, '<div>MCP guide</div>'],
    '/.well-known/oauth-authorization-server' => [200, '{}'],
    '/api/v2/shop/products' => [404],
    '/fr_FR/products/mug/reviews/new' => [403],
];
if ($indexing) {
    $answers['/fr_FR/'] = [200, 'home'];
    $answers['/fr_FR/plugins'] = [200, 'plugins'];
    $answers['/fr_FR/login'] = [200, 'login', ['X-Robots-Tag: noindex, nofollow']];
    $answers['/robots.txt'] = [200, "User-agent: *\nDisallow: /admin\n\nSitemap: https://stub.example/sitemap.xml\n"];
    $answers['/sitemap.xml'] = [200, '<urlset/>'];
}
if ($broken === $path) {
    $answers[$path] = '/fr_FR/login' === $path ? [200, 'login'] : [200, 'open registration'];
}
[$code, $body, $headers] = ($answers[$path] ?? [404]) + [1 => '', 2 => []];
$respond($code, $body, $headers);
