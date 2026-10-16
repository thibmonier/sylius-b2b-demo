<?php

declare(strict_types=1);

namespace App\Demo\Http;

/** Host names the demo may talk to: loopback, Railway private network and the configured extras. */
final readonly class HostAllowList
{
    private const ALWAYS = ['localhost', '127.0.0.1', '::1', '[::1]'];

    private const INTERNAL_SUFFIX = '.railway.internal';

    /** @param list<string> $hosts */
    private function __construct(private array $hosts)
    {
    }

    public static function fromCsv(string $csv): self
    {
        $hosts = array_filter(array_map(static fn (string $host): string => strtolower(trim($host)), explode(',', $csv)), static fn (string $host): bool => '' !== $host);

        return new self([...self::ALWAYS, ...$hosts]);
    }

    public function allows(string $host): bool
    {
        $host = strtolower($host);

        return \in_array($host, $this->hosts, true) || str_ends_with($host, self::INTERNAL_SUFFIX);
    }
}
