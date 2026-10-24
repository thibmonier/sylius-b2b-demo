<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use App\Demo\Mail\MailTwigExtension;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEvent;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;

/**
 * US-190: the excerpt of the page "Flat-file exchange". The contract of the plugin writes the file from the last placed orders
 * of the demo; the e-mail of the customer is masked like in the demo mailbox. A demo without placed order shows two fictitious ones.
 */
final readonly class ExchangeFileSample
{
    private const ORDERS = 3;

    public function __construct(private RecentPlacedOrders $orders)
    {
    }

    public function csv(): string
    {
        $events = $this->orders->latest(self::ORDERS);
        if ([] === $events) {
            $events = $this->fictitious();
        }

        $masked = array_map($this->masked(...), \array_slice($events, 0, self::ORDERS));

        return implode('', iterator_to_array((new OrderLinesCsv())->lines($masked), false));
    }

    private function masked(OutboxEventInterface $event): OutboxEvent
    {
        $payload = $event->getPayload();
        $customer = \is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
        $payload['customer'] = ['email' => MailTwigExtension::maskAddresses(\is_string($customer['email'] ?? null) ? $customer['email'] : '')];

        return new OutboxEvent($event->getType(), $event->getVersion(), $event->getOccurredAt(), $payload, $event->getChannelCode(), $event->getResourceType(), $event->getResourceId(), null, $event->getId());
    }

    /** @return list<OutboxEvent> */
    private function fictitious(): array
    {
        $order = static fn (int $n, string $placedAt, array $items): OutboxEvent => new OutboxEvent(
            'order.placed',
            1,
            new \DateTimeImmutable($placedAt),
            [
                'number' => sprintf('0000000%d', $n),
                'currency_code' => 'EUR',
                'checkout_completed_at' => $placedAt,
                'customer' => ['email' => 'buyer@acme.example'],
                'shipping_address' => ['country_code' => 'FR'],
                'items' => $items,
            ],
            'WEB',
            'order',
            (string) $n,
            null,
            sprintf('0192a000-0000-7000-8000-%012d', $n),
        );

        return [
            $order(1, '2026-10-20T10:00:00+00:00', [
                ['variant_code' => 'PAPER-A4', 'quantity' => 10, 'unit_price' => 450, 'total' => 4500],
                ['variant_code' => 'PEN-BLUE', 'quantity' => 5, 'unit_price' => 120, 'total' => 600],
            ]),
            $order(2, '2026-10-21T14:30:00+00:00', [
                ['variant_code' => 'FOLDER-GRN', 'quantity' => 20, 'unit_price' => 95, 'total' => 1900],
            ]),
        ];
    }
}
