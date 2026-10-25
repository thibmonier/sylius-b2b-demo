<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalPolicy;
use Tmonier\SyliusTradeCreditPlugin\Statement\StatementBuilder;

/** The page "My account" of a signed-in B2B buyer. The blocks are read from the services of the shop and of the plugins, on each request. */
final class AccountDashboardProvider
{
    public function __construct(
        private readonly CurrentMemberResolverInterface $members,
        #[Autowire(service: 'tmonier_trade_credit.statement_builder')]
        private readonly StatementBuilder $statements,
        private readonly ApprovalPolicy $policy,
        private readonly AccountOrdersBuilder $orders,
        private readonly AccountTodoBuilder $todos,
        private readonly AccountShortcutsBuilder $shortcuts,
        private readonly AccountCompanyPanelBuilder $company,
        private readonly AccountListsAndQuotesBuilder $listsAndQuotes,
        private readonly RequestStack $requests,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** Null when nobody is signed in as a customer. */
    public function provide(): ?AccountDashboard
    {
        $customer = $this->members->getCustomer();
        if (null === $customer) {
            return null;
        }
        $member = $this->members->getMember();
        $context = new AccountContext($customer, $this->requests->getCurrentRequest()?->getLocale() ?? 'en_US', $member, $this->statements->forCustomer($customer));
        $latest = $this->orders->latest($context);
        $approvals = $this->orders->approvals($latest);
        $rows = $this->orders->rows($context, $latest, $approvals);
        $total = [] === $rows ? 0 : $this->orders->count($context);
        $todos = $this->todos->build($context, $latest, $approvals, [] !== $rows);
        $currency = (string) ($latest[0] ?? null)?->getCurrencyCode() ?: ($context->statement?->account->getCurrencyCode() ?? 'EUR');
        $user = $customer->getUser();

        return new AccountDashboard(
            (string) $customer->getFirstName(),
            null === $member ? null : $this->company->roleLabel($context, $member),
            $context->company()?->getName(),
            (string) $customer->getEmail(),
            $user instanceof ShopUserInterface && $user->isVerified(),
            $this->todoTitle($context, $todos, [] !== $rows),
            $todos,
            $rows,
            $total,
            $this->shortcuts->build($context),
            $this->company->facts($context, $currency),
            $this->company->credit($context),
            $this->listsAndQuotes->lists($context),
            $this->listsAndQuotes->quotes($context),
        );
    }

    /** @param list<AccountTodo> $todos */
    private function todoTitle(AccountContext $context, array $todos, bool $hasOrders): string
    {
        $starting = !$hasOrders && [] === array_filter($todos, static fn (AccountTodo $todo): bool => AccountTodoBuilder::START_ICON !== $todo->icon);
        $decides = null !== $context->member && ($context->member->isAdmin() || $this->policy->isApprover($context->member));
        $key = $starting ? 'start' : ($decides ? 'do' : 'follow');

        return $this->translator->trans('todo.title.' . $key, [], 'account', $context->locale);
    }
}
