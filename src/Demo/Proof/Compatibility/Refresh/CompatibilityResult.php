<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

final readonly class CompatibilityResult
{
    /**
     * @param array<string, mixed>|null                    $demo     the stack of the demo
     * @param array<string, array<array-key, mixed>>       $plugins  facts per plugin slug
     * @param array<string, list<string>>                  $failures sources that failed, per subject
     */
    public function __construct(
        public string $generatedAt,
        public ?array $demo,
        public array $plugins,
        public array $failures,
    ) {
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        return ['generated_at' => $this->generatedAt] + (null === $this->demo ? [] : ['demo' => $this->demo]) + ['plugins' => $this->plugins];
    }
}
