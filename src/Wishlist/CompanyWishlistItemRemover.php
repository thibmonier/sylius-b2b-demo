<?php

declare(strict_types=1);

namespace App\Wishlist;

use App\Entity\Wishlist\CompanyWishlist;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Removes one item from a company list and writes it with a single flush. A write that fails leaves nothing half done:
 * the unit of work is cleared and the failure is reported as {@see CompanyWishlistWriteFailed}.
 */
class CompanyWishlistItemRemover
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return bool whether the item was in the list (nothing is written otherwise)
     *
     * @throws CompanyWishlistWriteFailed
     */
    public function remove(CompanyWishlist $list, int $itemId): bool
    {
        $wishlist = $list->getWishlist();
        foreach ($wishlist->getWishlistProducts() as $item) {
            if ($item->getId() !== $itemId) {
                continue;
            }
            $wishlist->removeProduct($item);
            $this->write();

            return true;
        }

        return false;
    }

    private function write(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->entityManager->clear();

            throw new CompanyWishlistWriteFailed('The company list could not be written.', 0, $exception);
        }
    }
}
