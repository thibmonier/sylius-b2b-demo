<?php

declare(strict_types=1);

namespace App\Demo\Packs;

final readonly class PacksReport
{
    /** @param list<Pack> $packs in the order of the file */
    public function __construct(private ?string $generatedAt, private array $packs = [])
    {
    }

    public function generatedAt(): ?string
    {
        return $this->generatedAt;
    }

    /** @return list<Pack> */
    public function packs(): array
    {
        return $this->packs;
    }

    public function pack(string $repository): ?Pack
    {
        foreach ($this->packs as $pack) {
            if ($pack->repository === $repository) {
                return $pack;
            }
        }

        return null;
    }
}
