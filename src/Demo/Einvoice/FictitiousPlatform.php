<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/**
 * US-183: answers the scenario of the order. The order is found with BT-10 (the PO reference the demo writes in the CII XML,
 * see PoReferenceCiiXmlWriter), so that the scenario follows the demo data and not the invoice identifier.
 */
final class FictitiousPlatform implements FictitiousPlatformInterface
{
    public function __construct(private readonly FictitiousPlatformOutage $outage = new FictitiousPlatformOutage())
    {
    }

    private const RAM_NAMESPACE = 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100';

    public function deposit(InvoiceDocument $document): FictitiousPlatformAnswer
    {
        if ('' === trim($document->xml) || '' === $document->pdfContent) {
            throw new \InvalidArgumentException('The fictitious platform needs the XML and the PDF of the invoice.');
        }

        $poReference = $this->buyerReference($document->xml);
        if (null !== $poReference && \in_array($poReference, DemoTransmissionScenarioPlan::outageOrders(), true) && !$this->outage->isOver()) {
            throw new \RuntimeException('Simulated outage of the fictitious platform: try again later.');
        }

        $scenario = DemoTransmissionScenarioPlan::for($poReference);

        return new FictitiousPlatformAnswer($scenario->value, $scenario->reason());
    }

    private function buyerReference(string $xml): ?string
    {
        $document = new \DOMDocument();
        // No network and no external entity: the XML comes from the shop, but the platform trusts nothing.
        if (!@$document->loadXML($xml, \LIBXML_NONET)) {
            return null;
        }

        $reference = $document->getElementsByTagNameNS(self::RAM_NAMESPACE, 'BuyerReference')->item(0)?->textContent;

        return null === $reference || '' === trim($reference) ? null : trim($reference);
    }
}
