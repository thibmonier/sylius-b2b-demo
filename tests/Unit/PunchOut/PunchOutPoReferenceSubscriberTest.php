<?php

declare(strict_types=1);

namespace App\Tests\Unit\PunchOut;

use App\Entity\Order\Order;
use App\PunchOut\PunchOutPoReferenceSubscriber;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

/**
 * US-022 — PunchOut OrderRequest orderID reported as the PO reference of the created order.
 */
final class PunchOutPoReferenceSubscriberTest extends TestCase
{
    public function testOrderIdBecomesThePoReference(): void
    {
        $order = $this->complete("PunchOut purchase order ORD-PO-456\nBuyer comments: urgent");

        self::assertSame('ORD-PO-456', $order->getPoReference());
    }

    public function testNoteWithoutOrderIdLeavesThePoReferenceEmpty(): void
    {
        self::assertNull($this->complete('PunchOut purchase order ')->getPoReference());
    }

    public function testNonPunchOutOrderIsIgnored(): void
    {
        self::assertNull($this->complete('Please deliver in the morning')->getPoReference());
        self::assertNull($this->complete(null)->getPoReference());
    }

    public function testExistingPoReferenceIsNotOverwritten(): void
    {
        self::assertSame('PO-MINE', $this->complete('PunchOut purchase order ORD-1', 'PO-MINE')->getPoReference());
    }

    public function testListensToTheCheckoutCompletionTransition(): void
    {
        self::assertArrayHasKey(
            'workflow.sylius_order_checkout.completed.complete',
            PunchOutPoReferenceSubscriber::getSubscribedEvents(),
        );
    }

    private function complete(?string $notes, ?string $existingPo = null): Order
    {
        $order = new Order();
        $order->setNotes($notes);
        $order->setPoReference($existingPo);

        $workflow = $this->createStub(\Symfony\Component\Workflow\WorkflowInterface::class);
        $event = new CompletedEvent(
            $order,
            new Marking(),
            new Transition(OrderCheckoutTransitions::TRANSITION_COMPLETE, 'payment_selected', 'completed'),
            $workflow,
        );
        (new PunchOutPoReferenceSubscriber())->onOrderCompleted($event);

        return $order;
    }
}
