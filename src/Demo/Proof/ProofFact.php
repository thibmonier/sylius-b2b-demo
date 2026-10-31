<?php

declare(strict_types=1);

namespace App\Demo\Proof;

/** One fact of the technical proof (US-111): displayed only with its public source and the date it was checked. */
final readonly class ProofFact
{
    public const VERIFIED = 'verified';

    public const UNPUBLISHED = 'unpublished';

    public const UNMEASURED = 'unmeasured';

    public const UNAVAILABLE = 'unavailable';

    private function __construct(
        public string $status,
        public string|int|float|bool|null $value = null,
        public ?string $source = null,
        public ?string $checkedAt = null,
        public bool $stale = false,
    ) {
    }

    public static function verified(string|int|float|bool $value, string $source, string $checkedAt, bool $stale = false): self
    {
        return new self(self::VERIFIED, $value, $source, $checkedAt, $stale);
    }

    public static function unpublished(string $source, string $checkedAt): self
    {
        return new self(self::UNPUBLISHED, null, $source, $checkedAt);
    }

    public static function unmeasured(): self
    {
        return new self(self::UNMEASURED);
    }

    public static function unavailable(): self
    {
        return new self(self::UNAVAILABLE);
    }

    public function isVerified(): bool
    {
        return self::VERIFIED === $this->status;
    }
}
