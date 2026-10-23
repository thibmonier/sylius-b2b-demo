<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

/** One way the compatibility data is behind the repository (US-171). */
final readonly class CompatibilityGap
{
    public const string REFRESH = 'php bin/compat-refresh';

    public function __construct(public string $figure, public string $detail)
    {
    }

    public function describe(): string
    {
        return \sprintf('%s: %s; refresh with %s', $this->figure, $this->detail, self::REFRESH);
    }
}
