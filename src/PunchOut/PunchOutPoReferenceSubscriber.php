<?php

declare(strict_types=1);

namespace App\PunchOut;

use App\Order\OrderPoReferenceAwareInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * US-022: copies the cXML OrderRequest orderID into the PO reference of the order the PunchOut
 * plugin creates.
 *
 * The plugin dispatches no event of its own: its OrderBuilder stores the orderID in the order notes
 * ("PunchOut purchase order <orderID>") before completing the checkout through the Sylius state
 * machine, and the order is persisted afterwards. The completion transition is the real hook.
 * A request without orderID is rejected by the plugin (no order); a note without id is ignored.
 *
 * Provenance is established server side: the subscriber only acts while the current request is the
 * plugin cXML endpoint (route {@see self::ENDPOINT_ROUTE}), never on notes typed by a shop customer.
 * The orderID must satisfy the same rules as the checkout form field, else no PO is set (and a
 * warning is logged); it is never silently truncated.
 */
final class PunchOutPoReferenceSubscriber implements EventSubscriberInterface
{
    public const NOTES_PREFIX = 'PunchOut purchase order ';

    /** Route of the plugin cXML endpoint, the only server-side proof the order comes from a PunchOut OrderRequest. */
    public const ENDPOINT_ROUTE = 'tmonier_punchout_cxml_endpoint';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
    }

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
        if (self::ENDPOINT_ROUTE !== $this->requestStack->getMainRequest()?->attributes->get('_route')) {
            return;
        }

        $reference = $this->extractOrderId((string) $order->getNotes());
        if (null === $reference) {
            return;
        }
        if (!$this->isValid($reference)) {
            $this->logger->warning('PunchOut orderID rejected as PO reference (invalid characters or length)', [
                'order' => $order->getNumber(),
            ]);

            return;
        }

        $order->setPoReference($reference);
    }

    private function isValid(string $reference): bool
    {
        return mb_strlen($reference) <= OrderPoReferenceAwareInterface::PO_REFERENCE_MAX_LENGTH &&
            1 === preg_match(OrderPoReferenceAwareInterface::PO_REFERENCE_PATTERN, $reference);
    }

    private function extractOrderId(string $notes): ?string
    {
        if (!str_starts_with($notes, self::NOTES_PREFIX)) {
            return null;
        }

        $firstLine = explode("\n", substr($notes, \strlen(self::NOTES_PREFIX)), 2)[0];
        $orderId = trim($firstLine);

        return '' === $orderId ? null : $orderId;
    }
}
