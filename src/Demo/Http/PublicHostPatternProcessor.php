<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

/**
 * `%env(demo_host_pattern:DEMO_PUBLIC_HOST)%`: the trusted-hosts pattern of the optional custom host (US-066). Without a
 * valid host the pattern `(?!)` matches nothing, so only the Railway domain stays accepted.
 */
final class PublicHostPatternProcessor implements EnvVarProcessorInterface
{
    private const MATCHES_NOTHING = '(?!)';

    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $host = PublicHost::normalize((string) $getEnv($name));

        return null === $host ? self::MATCHES_NOTHING : '^' . preg_quote($host, '/') . '$';
    }

    public static function getProvidedTypes(): array
    {
        return ['demo_host_pattern' => 'string'];
    }
}
