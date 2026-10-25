<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\DecidableApprovalCounter;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\OrderApprovalInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Security\ApprovalPermissions;
use Tmonier\SyliusQuoteRequestPlugin\Repository\QuoteRequestRepositoryInterface;
use Tmonier\SyliusQuoteRequestPlugin\Workflow\QuoteRequestStatuses;
use Tmonier\SyliusTradeCreditPlugin\Statement\StatementItem;

/**
 * What is waiting for the person, most urgent first, from the services that already know it: orders she has to decide, her own
 * order held for an approval, her own payments due, a quote proposal. Nothing is written here. Four cards at most.
 */
final class AccountTodoBuilder
{
    public const START_ICON = '→';

    private const MAX = 4;

    public function __construct(
        private readonly DecidableApprovalCounter $decidable,
        #[Autowire(service: 'tmonier_quote_request.repository.quote_request')]
        private readonly QuoteRequestRepositoryInterface $quotes,
        private readonly ApproverFinder $approvers,
        private readonly AccountFormatter $formatter,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly UrlGeneratorInterface $urls,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param list<OrderInterface>                     $latest the orders shown below, the only ones looked at for "held for approval"
     * @param array<int, OrderApprovalInterface>       $approvals by order id
     *
     * @return list<AccountTodo>
     */
    public function build(AccountContext $context, array $latest, array $approvals, bool $hasOrders): array
    {
        $todos = [
            ...$this->toDecide($context),
            ...$this->heldForApproval($context, $latest, $approvals),
            ...$this->overdue($context),
            ...$this->nextDue($context),
            ...$this->quoteProposal($context),
        ];
        if ([] === $todos) {
            return $hasOrders ? [$this->nothing($context)] : $this->starters($context);
        }

        return \array_slice($todos, 0, self::MAX);
    }

    /** @return list<AccountTodo> */
    private function toDecide(AccountContext $context): array
    {
        $member = $context->member;
        $count = $this->decidable->count($member);
        if (!$member instanceof CompanyMemberInterface || null === $count || $count < 1) {
            return [];
        }
        $oldest = $this->decidable->oldest($member, 1)[0] ?? null;
        $order = $oldest?->getOrder();
        $text = null === $oldest || null === $order ? '' : $this->t($context, 'todo.decide.text', [
            '%number%' => '#' . $order->getNumber(),
            '%buyer%' => $order->getCustomer()?->getFullName() ?? '',
            '%total%' => $this->formatter->money($oldest->getTotal(), (string) $oldest->getCurrencyCode(), $context->locale),
            '%date%' => $this->formatter->date($oldest->getCreatedAt(), $context->locale),
        ]);

        return [new AccountTodo(AccountTone::Waiting, (string) $count, $this->t($context, 'todo.decide.title', ['%count%' => $count]), $text, $this->t($context, 'todo.decide.link'), $this->urls->generate('tmonier_purchase_approval_shop_approvals', ['decidable' => 1, '_locale' => $context->locale]))];
    }

    /**
     * @param list<OrderInterface>               $latest
     * @param array<int, OrderApprovalInterface> $approvals
     *
     * @return list<AccountTodo>
     */
    private function heldForApproval(AccountContext $context, array $latest, array $approvals): array
    {
        $todos = [];
        foreach ($latest as $order) {
            $approval = $approvals[(int) $order->getId()] ?? null;
            if (!$approval instanceof OrderApprovalInterface || !$approval->isPending()) {
                continue;
            }
            $currency = (string) $approval->getCurrencyCode();
            $limit = $approval->getOrderLimit();
            $company = $context->company();
            $approver = null !== $company && $context->member instanceof CompanyMemberInterface ? $this->approvers->firstNameFor($company, $context->member, $approval->getTotal()) : null;
            $text = $this->t($context, 'todo.held.text', [
                '%total%' => $this->formatter->money($approval->getTotal(), $currency, $context->locale),
                '%limit%' => null === $limit ? '' : $this->formatter->money($limit, $currency, $context->locale),
                '%approver%' => $approver ?? '',
            ]);
            $todos[] = new AccountTodo(AccountTone::Waiting, '!', $this->t($context, 'todo.held.title', ['%number%' => '#' . $order->getNumber()]), $text, $this->t($context, 'todo.held.link'), $this->orderUrl($context, (string) $order->getNumber()));
        }

        return $todos;
    }

    /** @return list<AccountTodo> */
    private function overdue(AccountContext $context): array
    {
        $todos = [];
        foreach ($this->ownItems($context) as $item) {
            if (!$item->overdue) {
                continue;
            }
            $days = max(1, (int) $item->dueDate->diff(new \DateTimeImmutable('now', $item->dueDate->getTimezone()))->days);
            $todos[] = new AccountTodo(AccountTone::Late, '!', $this->t($context, 'todo.overdue.title', ['%number%' => '#' . $item->orderNumber]), $this->t($context, 'todo.overdue.text', ['%total%' => $this->money($item, $context), '%count%' => $days]), $this->t($context, 'todo.overdue.link'), $this->orderUrl($context, $item->orderNumber));
        }

        return $todos;
    }

    /** @return list<AccountTodo> */
    private function nextDue(AccountContext $context): array
    {
        $upcoming = array_values(array_filter($this->ownItems($context), static fn (StatementItem $item): bool => !$item->overdue));
        usort($upcoming, static fn (StatementItem $a, StatementItem $b): int => $a->dueDate <=> $b->dueDate);
        $item = $upcoming[0] ?? null;
        if (null === $item) {
            return [];
        }

        return [new AccountTodo(AccountTone::Info, '€', $this->t($context, 'todo.due.title', ['%date%' => $this->formatter->date($item->dueDate, $context->locale)]), $this->t($context, 'todo.due.text', ['%number%' => '#' . $item->orderNumber, '%total%' => $this->money($item, $context)]), $this->t($context, 'todo.due.link'), $this->urls->generate('tmonier_trade_credit_shop_statement', ['_locale' => $context->locale]))];
    }

    /** @return list<AccountTodo> */
    private function quoteProposal(AccountContext $context): array
    {
        $now = new \DateTimeImmutable();
        foreach ($this->quotes->findByCustomer($context->customer) as $quote) {
            if (QuoteRequestStatuses::PROPOSED !== $quote->getStatus() || !$quote->isValidAt($now)) {
                continue;
            }
            $number = (string) $quote->getNumber();
            $until = $quote->getValidUntil();

            return [new AccountTodo(AccountTone::Good, '€', $this->t($context, 'todo.quote.title', ['%number%' => $number]), $this->t($context, 'todo.quote.text', ['%count%' => $quote->getLines()->count(), '%date%' => null === $until ? '' : $this->formatter->date($until, $context->locale)]), $this->t($context, 'todo.quote.link'), $this->urls->generate('tmonier_quote_request_shop_account_show', ['number' => $number, '_locale' => $context->locale]))];
        }

        return [];
    }

    private function nothing(AccountContext $context): AccountTodo
    {
        return new AccountTodo(AccountTone::Good, '✓', $this->t($context, 'todo.nothing.title'), $this->t($context, 'todo.nothing.text'), $this->t($context, 'todo.nothing.link'), $this->urls->generate('sylius_shop_account_order_index', ['_locale' => $context->locale]));
    }

    /** @return list<AccountTodo> */
    private function starters(AccountContext $context): array
    {
        $starters = [new AccountTodo(AccountTone::Info, self::START_ICON, $this->t($context, 'todo.start.order.title'), $this->t($context, 'todo.start.order.text', ['%company%' => $context->company()?->getName() ?? '']), $this->t($context, 'todo.start.order.link'), $this->urls->generate('sylius_shop_homepage', ['_locale' => $context->locale]))];
        $company = $context->company();
        if (null !== $company && $this->authorization->isGranted(ApprovalPermissions::LIMITS_MANAGE, $company)) {
            $starters[] = new AccountTodo(AccountTone::Info, self::START_ICON, $this->t($context, 'todo.start.limits.title'), $this->t($context, 'todo.start.limits.text'), $this->t($context, 'todo.start.limits.link'), $this->urls->generate('tmonier_purchase_approval_shop_limits', ['_locale' => $context->locale]));
        }

        return $starters;
    }

    /** @return list<StatementItem> payments of the company account that the person herself placed */
    private function ownItems(AccountContext $context): array
    {
        return array_values(array_filter($context->statement->items ?? [], static fn (StatementItem $item): bool => $item->placedByViewer));
    }

    private function money(StatementItem $item, AccountContext $context): string
    {
        return $this->formatter->money($item->amount, (string) $item->terms->getCurrencyCode(), $context->locale);
    }

    private function orderUrl(AccountContext $context, string $number): string
    {
        return $this->urls->generate('sylius_shop_account_order_show', ['number' => $number, '_locale' => $context->locale]);
    }

    /** @param array<string, scalar> $parameters */
    private function t(AccountContext $context, string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, 'account', $context->locale);
    }
}
