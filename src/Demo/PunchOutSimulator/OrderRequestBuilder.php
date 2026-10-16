<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Symfony\Component\Clock\ClockInterface;

/**
 * Builds the OrderRequest the procurement tool sends once the cart is approved: the transferred lines
 * become ItemOut, the orderID is the purchase order number (shown as PO reference in the shop, US-022).
 * Addresses are the fictitious Acme ones.
 */
final readonly class OrderRequestBuilder
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function build(BuyerCredentials $credentials, ReceivedCart $cart, string $orderId): string
    {
        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        $envelope = new CxmlEnvelope($credentials, SetupRequestBuilder::payloadId(), $now);

        $order = $envelope->append($envelope->request, 'OrderRequest');
        $header = $envelope->append($order, 'OrderRequestHeader', null, [
            'orderID' => $orderId,
            'orderDate' => $now->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP'),
            'type' => 'new',
        ]);
        $envelope->append($envelope->append($header, 'Total'), 'Money', $cart->total, ['currency' => $cart->currency]);
        foreach (['ShipTo', 'BillTo'] as $role) {
            $this->address($envelope, $envelope->append($header, $role));
        }
        foreach ($cart->lines as $index => $line) {
            $this->item($envelope, $order, $index + 1, $line);
        }

        return $envelope->toXml();
    }

    private function address(CxmlEnvelope $envelope, \DOMElement $parent): void
    {
        $address = $envelope->append($parent, 'Address', null, ['isoCountryCode' => 'FR']);
        $envelope->append($address, 'Name', 'Acme Industries', ['xml:lang' => 'fr']);
        $postal = $envelope->append($address, 'PostalAddress');
        $envelope->append($postal, 'DeliverTo', 'Claire Martin');
        $envelope->append($postal, 'Street', '12 rue de l\'Exemple');
        $envelope->append($postal, 'City', 'Lyon');
        $envelope->append($postal, 'PostalCode', '69001');
        $envelope->append($postal, 'Country', 'France', ['isoCountryCode' => 'FR']);
    }

    private function item(CxmlEnvelope $envelope, \DOMElement $order, int $number, ReceivedLine $line): void
    {
        $item = $envelope->append($order, 'ItemOut', null, ['quantity' => (string) $line->quantity, 'lineNumber' => (string) $number]);
        $id = $envelope->append($item, 'ItemID');
        $envelope->append($id, 'SupplierPartID', $line->supplierPartId);
        $envelope->append($id, 'SupplierPartAuxiliaryID', $line->supplierPartAuxId);
        $detail = $envelope->append($item, 'ItemDetail');
        $envelope->append($envelope->append($detail, 'UnitPrice'), 'Money', $line->unitPrice, ['currency' => $line->currency]);
        $envelope->append($detail, 'Description', $line->description, ['xml:lang' => 'fr']);
        $envelope->append($detail, 'UnitOfMeasure', $line->unitOfMeasure);
    }
}
