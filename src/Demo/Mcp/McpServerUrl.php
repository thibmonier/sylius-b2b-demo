<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-103: the address of the MCP server shown on the guide is the one of the public site (DEFAULT_URI: the Railway domain,
 * then the public sub-domain), whatever address the page was reached by. DEFAULT_URI counts as public only when it is
 * https on a host that is not a loopback or local name; otherwise (local development, tests) the request's own address is kept.
 */
final readonly class McpServerUrl
{
    public function __construct(
        #[Autowire(env: 'DEFAULT_URI')]
        private string $defaultUri,
    ) {
    }

    /** @param string $requestUrl the absolute URL of the MCP endpoint as generated from the current request */
    public function forRequest(string $requestUrl): string
    {
        $path = parse_url($requestUrl, \PHP_URL_PATH);
        if (!$this->isPublic() || !\is_string($path)) {
            return $requestUrl;
        }

        return rtrim($this->defaultUri, '/') . $path;
    }

    private function isPublic(): bool
    {
        $parts = parse_url(trim($this->defaultUri));
        $host = strtolower($parts['host'] ?? '');

        return false !== $parts &&
            'https' === ($parts['scheme'] ?? null) &&
            '' !== $host &&
            1 !== preg_match('/^(?:localhost|0\.0\.0\.0|127(?:\.\d+){3}|\[?::1\]?)$|\.(?:local|localhost|internal)$/', $host);
    }
}
