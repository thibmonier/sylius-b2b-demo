<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use App\Wishlist\CompanyWishlistRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyInsightsPlugin\Security\InsightsPermissions;

/**
 * The four shortcuts: quick order, the first list of the company, a quote, then the purchasing dashboard for the people who may
 * open it and the equipment of the company for the others. A shortcut to a page that would refuse the person is never offered.
 */
final class AccountShortcutsBuilder
{
    public function __construct(
        private readonly CompanyWishlistRepository $lists,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly UrlGeneratorInterface $urls,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @return list<AccountShortcut> */
    public function build(AccountContext $context): array
    {
        $company = $context->company();
        $locale = ['_locale' => $context->locale];
        $first = null === $company ? null : ($this->lists->findByCompany($company)[0] ?? null);
        $list = null === $first
            ? new AccountShortcut($this->t($context, 'quick.list.title_none'), $this->t($context, 'quick.list.text_none'), $this->urls->generate('app_shop_company_wishlist_index', $locale))
            : new AccountShortcut($this->t($context, 'quick.list.title', ['%name%' => $first->getName()]), $this->t($context, 'quick.list.text', ['%count%' => $first->getWishlist()->getWishlistProducts()->count()]), $this->urls->generate('app_shop_company_wishlist_index', $locale));

        return [
            new AccountShortcut($this->t($context, 'quick.quick_order.title'), $this->t($context, 'quick.quick_order.text'), $this->urls->generate('tmonier_quick_order_shop_index', $locale)),
            $list,
            new AccountShortcut($this->t($context, 'quick.quote.title'), $this->t($context, 'quick.quote.text'), $this->urls->generate('tmonier_quote_request_shop_new', $locale)),
            null !== $company && $this->authorization->isGranted(InsightsPermissions::COMPANY_VIEW_INSIGHTS, $company)
                ? new AccountShortcut($this->t($context, 'quick.insights.title'), $this->t($context, 'quick.insights.text'), $this->urls->generate('tmonier_company_insights_shop_insights', $locale))
                : new AccountShortcut($this->t($context, 'quick.equipment.title'), $this->t($context, 'quick.equipment.text'), $this->urls->generate('tmonier_equipment_shop_fleet_index', $locale)),
        ];
    }

    /** @param array<string, scalar> $parameters */
    private function t(AccountContext $context, string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, 'account', $context->locale);
    }
}
