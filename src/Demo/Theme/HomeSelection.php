<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\ProductInterface;

/**
 * The three product blocks of the signed-in B2B home page (US-089). A product appears in one block only.
 * `usualFromHistory` / `restockFromList` tell a real history from catalogue suggestions (an account without orders).
 */
final readonly class HomeSelection
{
    /**
     * @param list<ProductInterface> $usual
     * @param list<ProductInterface> $restock
     * @param list<ProductInterface> $negotiated
     */
    public function __construct(
        public array $usual,
        public array $restock,
        public array $negotiated,
        public bool $usualFromHistory,
        public bool $restockFromList,
    ) {
    }

    public static function empty(): self
    {
        return new self([], [], [], false, false);
    }

    public function isEmpty(): bool
    {
        return [] === $this->usual && [] === $this->restock && [] === $this->negotiated;
    }
}
