<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * The stock of a variant as the cards of the list show it (US-198): in stock, low (below LOW_THRESHOLD), out of stock, or no information
 * when the stock is not tracked. The quantity is what can still be sold (on hand minus on hold).
 */
final readonly class StockStatus
{
    public const IN = 'in';

    public const LOW = 'low';

    public const OUT = 'out';

    public const UNTRACKED = 'untracked';

    public const LOW_THRESHOLD = 10;

    private function __construct(public string $level, public int $available)
    {
    }

    public static function ofVariant(?ProductVariantInterface $variant): self
    {
        if (!$variant instanceof ProductVariantInterface || !$variant->isTracked()) {
            return new self(self::UNTRACKED, 0);
        }
        $available = max(0, $variant->getOnHand() - $variant->getOnHold());

        return new self(match (true) {
            0 === $available => self::OUT,
            $available < self::LOW_THRESHOLD => self::LOW,
            default => self::IN,
        }, $available);
    }

    public function canBuy(): bool
    {
        return self::OUT !== $this->level;
    }
}
