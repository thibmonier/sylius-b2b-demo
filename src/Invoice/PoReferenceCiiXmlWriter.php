<?php

declare(strict_types=1);

namespace App\Invoice;

use App\Order\OrderPoReferenceAwareInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Tmonier\SyliusFacturXPlugin\Model\FacturXInvoice;
use Tmonier\SyliusFacturXPlugin\Xml\CiiXmlWriterInterface;

/**
 * US-022: adds the order PO reference as BT-10 (BuyerReference) to the Factur-X CII XML.
 *
 * The Factur-X plugin model has no buyer reference field, so the XML written by the plugin is
 * completed here. The order is found with BT-14 (the order number, set by the plugin mapper).
 * Any failure degrades gracefully: the invoice is generated without BT-10 and a warning is logged.
 */
final class PoReferenceCiiXmlWriter implements CiiXmlWriterInterface
{
    private const RAM_NAMESPACE = 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100';

    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(
        private readonly CiiXmlWriterInterface $inner,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function write(FacturXInvoice $invoice): string
    {
        $xml = $this->inner->write($invoice);

        try {
            return $this->addBuyerReference($xml, $this->findPoReference($invoice)) ?? $xml;
        } catch (\Throwable $exception) {
            $this->logger->warning('Factur-X BT-10 BuyerReference skipped: {reason}', [
                'reason' => $exception->getMessage(),
                'invoice' => $invoice->number,
            ]);

            return $xml;
        }
    }

    private function findPoReference(FacturXInvoice $invoice): ?string
    {
        if (null === $invoice->salesOrderReference) {
            return null;
        }

        $order = $this->orderRepository->findOneBy(['number' => $invoice->salesOrderReference]);
        if (!$order instanceof OrderPoReferenceAwareInterface) {
            return null;
        }

        $reference = trim((string) $order->getPoReference());

        return '' === $reference ? null : $reference;
    }

    private function addBuyerReference(string $xml, ?string $reference): ?string
    {
        if (null === $reference) {
            return null;
        }

        $document = new \DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->loadXML($xml, \LIBXML_NONET);

        $agreement = $document->getElementsByTagNameNS(self::RAM_NAMESPACE, 'ApplicableHeaderTradeAgreement')->item(0);
        if (!$agreement instanceof \DOMElement) {
            throw new \RuntimeException('ApplicableHeaderTradeAgreement not found in the Factur-X XML');
        }
        if (0 !== $agreement->getElementsByTagNameNS(self::RAM_NAMESPACE, 'BuyerReference')->length) {
            return null;
        }

        // textContent is escaped by the DOM serializer; BuyerReference must come first in the XSD sequence.
        $element = $document->createElementNS(self::RAM_NAMESPACE, 'ram:BuyerReference');
        $element->textContent = $reference;
        $agreement->insertBefore($element, $agreement->firstChild);

        return $document->saveXML() ?: null;
    }
}
