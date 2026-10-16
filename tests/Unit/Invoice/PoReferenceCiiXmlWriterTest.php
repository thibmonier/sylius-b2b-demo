<?php

declare(strict_types=1);

namespace App\Tests\Unit\Invoice;

use App\Entity\Order\Order;
use App\Invoice\PoReferenceCiiXmlWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Tmonier\SyliusFacturXPlugin\Model\Address;
use Tmonier\SyliusFacturXPlugin\Model\FacturXInvoice;
use Tmonier\SyliusFacturXPlugin\Model\Party;
use Tmonier\SyliusFacturXPlugin\Xml\CiiXmlWriterInterface;

/**
 * US-022 — Factur-X BT-10 (BuyerReference): present, absent, bridge failure (graceful), corrupted value.
 */
final class PoReferenceCiiXmlWriterTest extends TestCase
{
    private const RAM = 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100';

    private const XML = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:ram="' . self::RAM . '">'
        . '<rsm:SupplyChainTradeTransaction><ram:ApplicableHeaderTradeAgreement>'
        . '<ram:SellerTradeParty><ram:Name>Seller</ram:Name></ram:SellerTradeParty>'
        . '</ram:ApplicableHeaderTradeAgreement></rsm:SupplyChainTradeTransaction></rsm:CrossIndustryInvoice>';

    public function testBuyerReferenceIsWrittenFirstInTheAgreement(): void
    {
        $writer = $this->writer($this->orderWith('PO-1001'));

        $agreement = $this->agreement($writer->write($this->invoice('000000001')));

        self::assertSame('BuyerReference', $agreement->firstElementChild?->localName);
        self::assertSame('PO-1001', $agreement->firstElementChild->textContent);
    }

    public function testOrderWithoutPoReferenceKeepsTheXmlUntouched(): void
    {
        $writer = $this->writer($this->orderWith(null));

        self::assertSame(self::XML, $writer->write($this->invoice('000000001')));
    }

    public function testPunchOutOrderWithoutAnyOrderIsUntouched(): void
    {
        $writer = $this->writer(null);

        self::assertSame(self::XML, $writer->write($this->invoice('000000001')));
    }

    public function testCorruptedValueIsEscaped(): void
    {
        $xml = $this->writer($this->orderWith('<script>alert(1)</script>'))->write($this->invoice('000000001'));

        self::assertStringNotContainsString('<script>', $xml);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $xml);
    }

    public function testHostileReferenceKeepsTheXmlWellFormedAndStripsControlCharacters(): void
    {
        $xml = $this->writer($this->orderWith("A<&\"'>\x01B"))->write($this->invoice('000000001'));

        $document = new \DOMDocument();
        self::assertTrue($document->loadXML($xml));
        self::assertSame("A<&\"'>B", $this->agreement($xml)->firstElementChild?->textContent);
    }

    public function testBridgeFailureDegradesGracefullyWithAWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $repository = $this->createStub(OrderRepositoryInterface::class);
        $repository->method('findOneBy')->willThrowException(new \RuntimeException('db down'));

        $xml = (new PoReferenceCiiXmlWriter($this->inner(self::XML), $repository, $logger))->write($this->invoice('000000001'));

        self::assertSame(self::XML, $xml);
    }

    public function testMalformedXmlDegradesGracefullyWithAWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');
        $repository = $this->createStub(OrderRepositoryInterface::class);
        $repository->method('findOneBy')->willReturn($this->orderWith('PO-1'));

        $xml = (new PoReferenceCiiXmlWriter($this->inner('<not-cii/>'), $repository, $logger))->write($this->invoice('000000001'));

        self::assertSame('<not-cii/>', $xml);
    }

    private function writer(?Order $order): PoReferenceCiiXmlWriter
    {
        $repository = $this->createStub(OrderRepositoryInterface::class);
        $repository->method('findOneBy')->willReturn($order);

        return new PoReferenceCiiXmlWriter($this->inner(self::XML), $repository, $this->createStub(LoggerInterface::class));
    }

    private function inner(string $xml): CiiXmlWriterInterface
    {
        $inner = $this->createStub(CiiXmlWriterInterface::class);
        $inner->method('write')->willReturn($xml);

        return $inner;
    }

    private function orderWith(?string $poReference): Order
    {
        $order = new Order();
        $order->setPoReference($poReference);

        return $order;
    }

    private function agreement(string $xml): \DOMElement
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);
        $agreement = $document->getElementsByTagNameNS(self::RAM, 'ApplicableHeaderTradeAgreement')->item(0);
        self::assertInstanceOf(\DOMElement::class, $agreement);

        return $agreement;
    }

    private function invoice(string $orderNumber): FacturXInvoice
    {
        $party = new Party('Party', new Address('FR'));

        return new FacturXInvoice(
            number: 'INV-1',
            issueDate: new \DateTimeImmutable('2026-10-16'),
            currencyCode: 'EUR',
            seller: $party,
            buyer: $party,
            lines: [],
            vatBreakdown: [],
            lineTotalAmount: 0,
            taxBasisTotalAmount: 0,
            taxTotalAmount: 0,
            grandTotalAmount: 0,
            prepaidAmount: 0,
            duePayableAmount: 0,
            salesOrderReference: $orderNumber,
        );
    }
}
