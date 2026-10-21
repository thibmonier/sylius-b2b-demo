<?php

declare(strict_types=1);

namespace App\Demo\Proof;

/** What the data file says, fact by fact: a fact the file does not hold (or holds without proof) is "unavailable". */
final readonly class ProofReport
{
    /**
     * @param array<string, array<string, ProofFact>> $plugins  facts per plugin slug
     * @param array<string, ProofFact>                $delivery facts about how the demo itself is delivered
     */
    public function __construct(
        private ?string $generatedAt,
        private array $plugins = [],
        private array $delivery = [],
    ) {
    }

    public function generatedAt(): ?string
    {
        return $this->generatedAt;
    }

    public function fact(string $slug, string $key): ProofFact
    {
        return $this->plugins[$slug][$key] ?? self::missing($key);
    }

    public function deliveryFact(string $key): ProofFact
    {
        return $this->delivery[$key] ?? self::missing($key);
    }

    /** A coverage that is not in the file is "not measured", never a figure and never an error. */
    private static function missing(string $key): ProofFact
    {
        return 'coverage' === $key ? ProofFact::unmeasured() : ProofFact::unavailable();
    }
}
