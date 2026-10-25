<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/**
 * An order has three states in the shop (order, payment, shipping), an approval and, on account, a due date. The buyer reads ONE
 * sentence. The first rule that applies wins: an approval still pending, then a closed order, then the payment term, then the
 * payment, then the delivery.
 */
final class AccountOrderStateResolver
{
    /** @param bool|null $overdue null: the order is not paid on account */
    public function resolve(string $orderState, ?string $paymentState, ?string $shippingState, ?string $approvalState, ?bool $overdue, ?\DateTimeImmutable $dueDate): AccountOrderState
    {
        if ('pending' === $approvalState) {
            return new AccountOrderState('pending_approval', AccountTone::Waiting);
        }
        if ('cancelled' === $orderState) {
            return new AccountOrderState('cancelled', AccountTone::Neutral);
        }
        if (\in_array($paymentState, ['refunded', 'partially_refunded'], true)) {
            return new AccountOrderState('refunded', AccountTone::Neutral);
        }
        if (null !== $overdue) {
            return $overdue ? new AccountOrderState('payment_late', AccountTone::Late) : new AccountOrderState('due_on', AccountTone::Waiting, $dueDate);
        }
        if ('awaiting_payment' === $paymentState) {
            return new AccountOrderState('awaiting_payment', AccountTone::Waiting);
        }
        if ('paid' === $paymentState) {
            return 'shipped' === $shippingState || 'fulfilled' === $orderState ? new AccountOrderState('delivered', AccountTone::Good) : new AccountOrderState('paid_preparing', AccountTone::Good);
        }

        return new AccountOrderState('in_progress', AccountTone::Waiting);
    }
}
