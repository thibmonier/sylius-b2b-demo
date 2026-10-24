<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceTransmissionHandler;

/**
 * US-183: the journal of the transmission plugin after the `demo` fixtures. Each paid order of the plan was invoiced
 * (Factur-X), the event reached the plugin, and the fictitious platform answered the status chosen by the demo data.
 */
final class FictitiousPlatformJournalTest extends KernelTestCase
{
    use DatabaseAccess;

    private const LINE_OF_AN_ORDER = 'SELECT t.invoice_id, t.status, t.message, t.updated_at FROM tmonier_einvoice_transmission t'
        . ' JOIN sylius_invoicing_plugin_invoice i ON i.id = t.invoice_id'
        . ' JOIN sylius_order o ON o.number = i.order_number WHERE o.po_reference = ?';

    /** @return iterable<string, array{string, string}> */
    public static function plannedOrders(): iterable
    {
        yield 'deposited' => ['ACME-PO-2026-0412', 'deposited'];
        yield 'rejected' => ['ACME-PO-2026-0458', 'rejected'];
        yield 'refused' => ['ACME-PO-2026-0503', 'refused'];
        yield 'collected' => ['ACME-PO-2026-0591', 'collected'];
    }

    #[DataProvider('plannedOrders')]
    public function testTheJournalKeepsTheStatusOfTheScenarioWithItsDate(string $poReference, string $status): void
    {
        self::bootKernel();

        $line = $this->db()->fetchAssociative(self::LINE_OF_AN_ORDER, [$poReference]);

        self::assertIsArray($line, 'the order ' . $poReference . ' has a line in the journal');
        self::assertSame($status, $line['status']);
        self::assertNotEmpty($line['updated_at']);
    }

    public function testARejectionAndARefusalKeepASimulatedReason(): void
    {
        self::bootKernel();

        foreach (['ACME-PO-2026-0458', 'ACME-PO-2026-0503'] as $poReference) {
            $message = $this->nullableStringValue('SELECT t.message FROM tmonier_einvoice_transmission t JOIN sylius_invoicing_plugin_invoice i ON i.id = t.invoice_id JOIN sylius_order o ON o.number = i.order_number WHERE o.po_reference = ?', [$poReference]);

            self::assertNotNull($message);
            self::assertStringStartsWith('Simulated', $message);
        }
    }

    public function testEveryInvoicedCoreOrderHasExactlyOneLineAndNoStatusOutsideTheSet(): void
    {
        self::bootKernel();

        self::assertSame(
            $this->intValue('SELECT COUNT(*) FROM sylius_invoicing_plugin_invoice'),
            $this->intValue('SELECT COUNT(*) FROM tmonier_einvoice_transmission'),
            'one line per invoice',
        );
        $statuses = array_map('strval', $this->db()->fetchFirstColumn('SELECT DISTINCT status FROM tmonier_einvoice_transmission'));
        sort($statuses);
        self::assertSame(['collected', 'deposited', 'refused', 'rejected'], $statuses);
    }

    public function testAReplayedEventLeavesTheLineAndItsDateAsTheyWere(): void
    {
        self::bootKernel();
        $before = $this->db()->fetchAssociative(self::LINE_OF_AN_ORDER, ['ACME-PO-2026-0412']);
        self::assertIsArray($before);
        $handler = self::getContainer()->get(InvoiceTransmissionHandler::class);
        self::assertInstanceOf(InvoiceTransmissionHandler::class, $handler);
        $number = $this->stringValue('SELECT number FROM sylius_invoicing_plugin_invoice WHERE id = ?', [(string) $before['invoice_id']]);

        $handler->handle(new InvoiceDocument((string) $before['invoice_id'], $number, '<xml/>', 'pdf', 'invoice.pdf'));

        self::assertSame($before, $this->db()->fetchAssociative(self::LINE_OF_AN_ORDER, ['ACME-PO-2026-0412']));
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM tmonier_einvoice_transmission WHERE invoice_id = ?', [(string) $before['invoice_id']]));
    }
}
