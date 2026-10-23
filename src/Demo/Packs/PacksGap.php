<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** One way the packs data is behind the repositories (US-178). */
final readonly class PacksGap
{
    public const string REFRESH = 'php bin/packs-refresh';

    public function __construct(public string $figure, public string $detail)
    {
    }

    public function describe(): string
    {
        return \sprintf('%s: %s; refresh with %s', $this->figure, $this->detail, self::REFRESH);
    }
}
