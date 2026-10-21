<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

/** The next content of config/demo/proof.json, and the sources that failed while building it. */
final readonly class RefreshResult
{
    /**
     * @param array<string, array<string, array<string, mixed>>> $plugins  facts per plugin slug
     * @param array<string, array<string, mixed>>                $delivery facts about how the demo is delivered
     * @param array<string, list<string>>                        $failures failed sources per subject (a slug or "delivery")
     */
    public function __construct(
        public string $generatedAt,
        public array $plugins,
        public array $delivery,
        public array $failures,
    ) {
    }

    /** @return array{generated_at: string, plugins: array<string, array<string, array<string, mixed>>>, delivery: array<string, array<string, mixed>>} */
    public function data(): array
    {
        return ['generated_at' => $this->generatedAt, 'plugins' => $this->plugins, 'delivery' => $this->delivery];
    }
}
