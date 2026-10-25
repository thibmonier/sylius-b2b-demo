<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;
use Tmonier\SyliusCompanyInsightsPlugin\Security\InsightsPermissions;

/**
 * US-199: "Purchasing dashboard" in the menu of the account, for the members who may open it (the same right as the page itself:
 * nobody gets a link to a refusal). The plugin offers the page through the tabs of "My company" only; the menu is the one the
 * account page draws its blocks from.
 */
#[AsEventListener(event: 'sylius.menu.shop.account')]
final readonly class InsightsMenuListener
{
    public function __construct(
        private CurrentMemberResolverInterface $members,
        private AuthorizationCheckerInterface $authorization,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(MenuBuilderEvent $event): void
    {
        $company = $this->members->getMember()?->getCompany();
        if (null === $company || !$this->authorization->isGranted(InsightsPermissions::COMPANY_VIEW_INSIGHTS, $company)) {
            return;
        }

        $event->getMenu()
            ->addChild('app_insights', ['route' => 'tmonier_company_insights_shop_insights'])
            ->setLabel($this->translator->trans('nav.insights', [], 'account'))
            ->setLabelAttribute('icon', 'tabler:chart-bar')
        ;
    }
}
