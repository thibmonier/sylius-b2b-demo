<?php

declare(strict_types=1);

namespace App\PunchOut;

use App\Order\OrderPoReferenceAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * US-022: copies the cXML OrderRequest orderID into the PO reference of the order the PunchOut
 * plugin creates.
 *
 * The plugin dispatches no event of its own: its OrderBuilder stores the orderID in the order notes
 * ("PunchOut purchase order <orderID>") before completing the checkout through the Sylius state
 * machine, and the order is persisted afterwards. The completion transition is the real hook.
 * A request without orderID is rejected by the plugin (no order); a note without id is ignored.
 */
final class PunchOutPoReferenceSubscriber implements EventSubscriberInterface
{
    public const NOTES_PREFIX = 'PunchOut purchase order ';

    private const MAX_LENGTH = 100;

    public static function getSubscribedEvents(): array
    {
        return [
            sprintf('workflow.%s.completed.%s', OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE) => 'onOrderCompleted',
        ];
    }

    public function onOrderCompleted(CompletedEvent $event): void
    {
        $order = $event->getSubject();
        if (!$order instanceof OrderPoReferenceAwareInterface || !$order instanceof OrderInterface) {
            return;
        }
        if (null !== $order->getPoReference() && '' !== $order->getPoReference()) {
            return;
        }

        $reference = $this->extractOrderId((string) $order->getNotes());
        if (null !== $reference) {
            $order->setPoReference($reference);
        }
    }

    private function extractOrderId(string $notes): ?string
    {
        if (!str_starts_with($notes, self::NOTES_PREFIX)) {
            return null;
        }

        $firstLine = explode("\n", substr($notes, \strlen(self::NOTES_PREFIX)), 2)[0];
        $orderId = trim($firstLine);

        return '' === $orderId ? null : mb_substr($orderId, 0, self::MAX_LENGTH);
    }
}
