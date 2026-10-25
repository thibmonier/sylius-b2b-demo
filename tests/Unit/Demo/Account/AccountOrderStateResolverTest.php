<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Account;

use App\Demo\Account\Dashboard\AccountOrderStateResolver;
use App\Demo\Account\Dashboard\AccountTone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-199: the three states of the shop (order, payment, shipping), the approval and the payment term become ONE sentence of the buyer. */
final class AccountOrderStateResolverTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, ?string, ?bool, string, AccountTone}> */
    public static function cases(): iterable
    {
        yield 'above the limit, waiting for an approver' => ['new', 'awaiting_payment', 'ready', 'pending', null, 'pending_approval', AccountTone::Waiting];
        yield 'cancelled' => ['cancelled', 'cancelled', 'cancelled', null, null, 'cancelled', AccountTone::Neutral];
        yield 'refunded' => ['fulfilled', 'refunded', 'shipped', null, null, 'refunded', AccountTone::Neutral];
        yield 'delivered' => ['fulfilled', 'paid', 'shipped', null, null, 'delivered', AccountTone::Good];
        yield 'delivered after an approval' => ['fulfilled', 'paid', 'shipped', 'approved', null, 'delivered', AccountTone::Good];
        yield 'paid, being prepared' => ['new', 'paid', 'ready', null, null, 'paid_preparing', AccountTone::Good];
        yield 'to pay, no term' => ['new', 'awaiting_payment', 'ready', null, null, 'awaiting_payment', AccountTone::Waiting];
        yield 'on account, not due yet' => ['new', 'awaiting_payment', 'ready', null, false, 'due_on', AccountTone::Waiting];
        yield 'on account, overdue' => ['new', 'awaiting_payment', 'ready', null, true, 'payment_late', AccountTone::Late];
        yield 'approved then on account and overdue' => ['new', 'awaiting_payment', 'ready', 'approved', true, 'payment_late', AccountTone::Late];
        yield 'a state the shop adds one day' => ['new', 'authorized', 'ready', null, null, 'in_progress', AccountTone::Waiting];
    }

    #[DataProvider('cases')]
    public function testTheStatesBecomeOneSentence(string $order, string $payment, string $shipping, ?string $approval, ?bool $overdue, string $key, AccountTone $tone): void
    {
        $due = null === $overdue ? null : new \DateTimeImmutable('2026-11-13');

        $state = (new AccountOrderStateResolver())->resolve($order, $payment, $shipping, $approval, $overdue, $due);

        self::assertSame($key, $state->key);
        self::assertSame($tone, $state->tone);
        self::assertSame('due_on' === $key ? $due : null, $state->dueDate);
    }
}
