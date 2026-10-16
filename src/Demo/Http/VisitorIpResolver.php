<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * The IP of the visitor, as resolved by the trusted proxies of the framework (X-Forwarded-For is only
 * read when the connection comes from a trusted proxy). Never the address of the proxy itself: when the
 * request carries no resolvable client the key is a fixed placeholder shared by those requests only.
 */
final class VisitorIpResolver
{
    public const UNKNOWN = 'unknown';

    public function resolve(Request $request): string
    {
        return $request->getClientIp() ?? self::UNKNOWN;
    }
}
