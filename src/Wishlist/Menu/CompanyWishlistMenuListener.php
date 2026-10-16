<?php

declare(strict_types=1);

namespace App\Wishlist\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Security\CompanyPermissions;

/** "Company lists" in the customer account menu, for the members of an enabled company only. */
#[AsEventListener(event: 'sylius.menu.shop.account')]
final readonly class CompanyWishlistMenuListener
{
    public function __construct(
        private CurrentMemberResolverInterface $currentMemberResolver,
        private AuthorizationCheckerInterface $authorizationChecker,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(MenuBuilderEvent $event): void
    {
        $company = $this->currentMemberResolver->getMember()?->getCompany();
        if (null === $company || !$this->authorizationChecker->isGranted(CompanyPermissions::COMPANY_VIEW, $company)) {
            return;
        }

        $event->getMenu()
            ->addChild('app_company_wishlists', ['route' => 'app_shop_company_wishlist_index'])
            ->setLabel($this->translator->trans('app.company_wishlist.menu', [], 'wishlist'))
            ->setLabelAttribute('icon', 'tabler:list-check')
        ;
    }
}
