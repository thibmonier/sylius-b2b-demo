<?php

declare(strict_types=1);

namespace App\Demo\Packs;

final readonly class PacksResult
{
    /**
     * @param array<string, array<array-key, mixed>> $packs    facts per pack repository
     * @param array<string, list<string>>            $failures sources that failed, per pack
     */
    public function __construct(
        public string $generatedAt,
        public array $packs,
        public array $failures,
    ) {
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        return ['generated_at' => $this->generatedAt, 'packs' => $this->packs];
    }
}
