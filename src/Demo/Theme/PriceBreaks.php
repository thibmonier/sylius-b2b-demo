<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** Price breaks of a variant for the current customer (row 1 is the catalogue price of one unit). */
final readonly class PriceBreaks
{
    /** Below this gap with the catalogue price, a "from X" mention is noise (8.90 -> 8.89). */
    public const SIGNIFICANT_GAP_PERCENT = 5.0;

    /** @param list<PriceBreakRow> $rows */
    public function __construct(
        public array $rows,
        public ?string $groupName = null,
    ) {
    }

    public function basePrice(): int
    {
        return $this->rows[0]->unitPrice;
    }

    public function lowestPrice(): int
    {
        return min($this->rows[0]->unitPrice, ...array_map(static fn (PriceBreakRow $row): int => $row->unitPrice, $this->rows));
    }

    /** The first quantity at which the company price beats the public tiers. */
    public function negotiated(): ?PriceBreakRow
    {
        foreach ($this->rows as $row) {
            if ($row->negotiated) {
                return $row;
            }
        }

        return null;
    }

    /** "From X" is only worth showing when the lowest tier is strictly more than 5 % under the catalogue price. */
    public function hasSignificantGap(): bool
    {
        return ($this->basePrice() - $this->lowestPrice()) * 100 > self::SIGNIFICANT_GAP_PERCENT * $this->basePrice();
    }
}
