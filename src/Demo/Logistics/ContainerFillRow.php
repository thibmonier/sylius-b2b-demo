<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

/** The container-fill relevant columns of one catalogue CSV row. */
final readonly class ContainerFillRow
{
    /** Container type planned by layers in the demo. */
    public const LAYER_TYPE = 'EUR_PALLET';

    public function __construct(
        public string $sku,
        public string $taxon,
        public ?string $packagingUnit,
        public LogisticUnit $unit,
    ) {
    }

    /** @param array<string, string> $csvRow */
    public static function fromCsv(array $csvRow): self
    {
        $attributes = \json_decode(\trim($csvRow['attributes_json'] ?? ''), true);
        $packaging = \is_array($attributes) && \is_string($attributes['packaging_unit'] ?? null) ? $attributes['packaging_unit'] : null;

        return new self(
            \trim($csvRow['sku']),
            \trim($csvRow['taxon']),
            $packaging,
            LogisticUnit::fromCatalog(
                (float) ($csvRow['weight_kg'] ?? 0),
                (float) ($csvRow['width_cm'] ?? 0),
                (float) ($csvRow['height_cm'] ?? 0),
                (float) ($csvRow['depth_cm'] ?? 0),
            ),
        );
    }
}
