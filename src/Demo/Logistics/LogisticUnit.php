<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

/**
 * One sold unit as the container-fill plugin stores it: millimetres and grams. The catalogue CSV is
 * in centimetres and kilograms, hence the conversion in {@see self::fromCatalog()}.
 */
final readonly class LogisticUnit
{
    private const MM_PER_CM = 10;

    private const G_PER_KG = 1000;

    public function __construct(
        public int $widthMm,
        public int $heightMm,
        public int $depthMm,
        public int $weightG,
    ) {
    }

    public static function fromCatalog(float $weightKg, float $widthCm, float $heightCm, float $depthCm): self
    {
        return new self(
            (int) \round($widthCm * self::MM_PER_CM),
            (int) \round($heightCm * self::MM_PER_CM),
            (int) \round($depthCm * self::MM_PER_CM),
            (int) \round($weightKg * self::G_PER_KG),
        );
    }

    public function isUsable(): bool
    {
        return $this->widthMm > 0 && $this->heightMm > 0 && $this->depthMm > 0 && $this->weightG > 0;
    }

    public function volumeMm3(): int
    {
        return $this->widthMm * $this->heightMm * $this->depthMm;
    }

    public function footprintMm2(): int
    {
        return $this->widthMm * $this->depthMm;
    }
}
