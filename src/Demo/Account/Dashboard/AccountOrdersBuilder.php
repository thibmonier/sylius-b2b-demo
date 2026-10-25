<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\OrderApprovalInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\OrderApprovalRepositoryInterface;
use Tmonier\SyliusTradeCreditPlugin\Statement\StatementItem;

/** The last orders of the buyer, each with its state in plain words, its total and the way to order it again. */
final class AccountOrdersBuilder
{
    public const LATEST = 5;

    /** @param OrderRepositoryInterface<OrderInterface> $orders */
    public function __construct(
        #[Autowire(service: 'sylius.repository.order')]
        private readonly OrderRepositoryInterface $orders,
        private readonly ChannelContextInterface $channels,
        #[Autowire(service: 'tmonier_purchase_approval.repository.order_approval')]
        private readonly OrderApprovalRepositoryInterface $approvals,
        private readonly AccountOrderStateResolver $states,
        private readonly AccountFormatter $formatter,
        private readonly UrlGeneratorInterface $urls,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @return list<OrderInterface> */
    public function latest(AccountContext $context): array
    {
        /** @var list<OrderInterface> $orders */
        $orders = $this->query($context)
            ->orderBy('o.checkoutCompletedAt', 'DESC')
            ->addOrderBy('o.id', 'DESC')
            ->setMaxResults(self::LATEST)
            ->getQuery()
            ->getResult();

        return $orders;
    }

    public function count(AccountContext $context): int
    {
        return (int) $this->query($context)->select('COUNT(o.id)')->getQuery()->getSingleScalarResult();
    }

    /**
     * @param list<OrderInterface> $orders
     *
     * @return array<int, OrderApprovalInterface> by order id
     */
    public function approvals(array $orders): array
    {
        return $this->approvals->findByOrderIds(array_map(static fn (OrderInterface $order): int => (int) $order->getId(), $orders));
    }

    /**
     * @param list<OrderInterface>               $orders
     * @param array<int, OrderApprovalInterface> $approvals by order id
     *
     * @return list<AccountOrderRow>
     */
    public function rows(AccountContext $context, array $orders, array $approvals): array
    {
        $items = $this->openItems($context);
        $rows = [];
        foreach ($orders as $order) {
            $number = (string) $order->getNumber();
            $item = $items[$number] ?? null;
            $state = $this->states->resolve($order->getState(), $order->getPaymentState(), $order->getShippingState(), ($approvals[(int) $order->getId()] ?? null)?->getState(), $item?->overdue, $item?->dueDate);
            $rows[] = new AccountOrderRow(
                '#' . $number,
                $this->urls->generate('sylius_shop_account_order_show', ['number' => $number, '_locale' => $context->locale]),
                $this->formatter->date($order->getCheckoutCompletedAt() ?? $order->getCreatedAt() ?? new \DateTimeImmutable(), $context->locale),
                $this->label($state, $context),
                $state->tone,
                $this->formatter->money($order->getTotal(), (string) $order->getCurrencyCode(), $context->locale),
                $this->urls->generate('tmonier_quick_order_shop_reorder', ['number' => $number, '_locale' => $context->locale]),
            );
        }

        return $rows;
    }

    private function label(AccountOrderState $state, AccountContext $context): string
    {
        $date = null === $state->dueDate ? '' : $this->formatter->date($state->dueDate, $context->locale);

        return $this->translator->trans('order_state.' . $state->key, ['%date%' => $date], 'account', $context->locale);
    }

    /** @return array<string, StatementItem> by order number */
    private function openItems(AccountContext $context): array
    {
        $items = [];
        foreach ($context->statement->items ?? [] as $item) {
            $items[$item->orderNumber] = $item;
        }

        return $items;
    }

    private function query(AccountContext $context): \Doctrine\ORM\QueryBuilder
    {
        $channel = $this->channels->getChannel();

        return $this->orders->createByCustomerAndChannelIdQueryBuilder($context->customer->getId(), $channel->getId());
    }
}
