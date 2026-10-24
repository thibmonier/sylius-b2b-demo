<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\FictitiousPlatform;
use App\Demo\Einvoice\FictitiousPlatformOutage;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/**
 * US-184: the scenario "error, then success on the relaunch". The outage of the fictitious platform lasts until an
 * administrator relaunches the send; the first send (the fixtures) meets it, the relaunch does not.
 */
final class FictitiousPlatformOutageTest extends TestCase
{
    private const ORDER_WITH_OUTAGE = 'TP-2026-142';

    public function testTheFirstSendOfTheOrderMeetsTheOutage(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Simulated outage');

        (new FictitiousPlatform(new FictitiousPlatformOutage()))->deposit(self::document(self::ORDER_WITH_OUTAGE));
    }

    public function testTheRelaunchFindsThePlatformBackAndDeposits(): void
    {
        $outage = new FictitiousPlatformOutage();
        $outage->recover();

        self::assertSame('deposited', (new FictitiousPlatform($outage))->deposit(self::document(self::ORDER_WITH_OUTAGE))->status);
    }

    public function testOtherOrdersNeverMeetTheOutage(): void
    {
        self::assertSame('deposited', (new FictitiousPlatform(new FictitiousPlatformOutage()))->deposit(self::document('ACME-PO-2026-0412'))->status);
    }

    private static function document(string $poReference): InvoiceDocument
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"><rsm:SupplyChainTradeTransaction><ram:ApplicableHeaderTradeAgreement><ram:BuyerReference>' . $poReference . '</ram:BuyerReference></ram:ApplicableHeaderTradeAgreement></rsm:SupplyChainTradeTransaction></rsm:CrossIndustryInvoice>';

        return new InvoiceDocument('inv-1', 'INV-1', $xml, '%PDF-1.7 fictive', 'invoice.pdf');
    }
}
