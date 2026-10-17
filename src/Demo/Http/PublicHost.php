<?php

declare(strict_types=1);

namespace App\Demo\Http;

/**
 * US-066: the optional custom host of the demo (DEMO_PUBLIC_HOST, e.g. a subdomain of the owner's site), accepted next to
 * the Railway domain during the transition. Only a bare host name is usable; anything else (pattern, URL, path) is ignored.
 */
final class PublicHost
{
    private const HOST = '/^(?=.{1,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/';

    public static function normalize(string $value): ?string
    {
        $host = strtolower(trim($value));

        return 1 === preg_match(self::HOST, $host) ? $host : null;
    }
}
