<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility;

/** The combination the demo runs (US-171), as read from its composer.lock. */
final readonly class DemoEnvironment
{
    public function __construct(
        public string $sylius,
        public string $symfony,
        public ?string $php,
        public string $source,
        public string $checkedAt,
        public bool $stale = false,
    ) {
    }
}
