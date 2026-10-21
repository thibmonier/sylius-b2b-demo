<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use App\Wishlist\CompanyWishlistRepository;
use Sylius\WishlistPlugin\Entity\WishlistProductInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;

/** The items of a purchase list of the signed-in member's company (named by the `list` option, else the first), for the scenarios. */
final readonly class ScenarioListResolver
{
    public function __construct(
        private CurrentMemberResolverInterface $members,
        private CompanyWishlistRepository $lists,
    ) {
    }

    /**
     * @param array<string, string> $options
     *
     * @return non-empty-list<WishlistProductInterface>
     */
    public function items(array $options): array
    {
        $company = $this->members->getMember()?->getCompany();
        if (null === $company) {
            throw new ScenarioPreparationFailed('The signed-in visitor has no company.');
        }
        foreach ($this->lists->findByCompany($company) as $list) {
            $items = [];
            $wishlist = $list->getWishlist();
            if (isset($options['list']) && $wishlist->getName() !== $options['list']) {
                continue;
            }
            foreach ($wishlist->getWishlistProducts() as $item) {
                $items[] = $item;
            }
            if ([] !== $items) {
                return $items;
            }
        }

        throw new ScenarioPreparationFailed('No purchase list to start from.');
    }
}
