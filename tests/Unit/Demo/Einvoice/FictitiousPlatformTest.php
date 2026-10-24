<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\DemoTransmissionScenario;
use App\Demo\Einvoice\FictitiousPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/**
 * US-183: the fictitious platform answers a simulated status chosen by the demo data (the PO reference of the order, which
 * the invoice carries as BT-10). Nothing leaves the process: it is a service, not an HTTP call (ADR 0008).
 */
final class FictitiousPlatformTest extends TestCase
{
    /** @return iterable<string, array{string, string, bool}> */
    public static function scenarios(): iterable
    {
        yield 'deposited' => ['ACME-PO-2026-0412', 'deposited', false];
        yield 'rejected, with a reason' => ['ACME-PO-2026-0458', 'rejected', true];
        yield 'refused, with a reason' => ['ACME-PO-2026-0503', 'refused', true];
        yield 'collected' => ['ACME-PO-2026-0591', 'collected', false];
    }

    #[DataProvider('scenarios')]
    public function testTheAnswerFollowsTheScenarioOfTheOrder(string $poReference, string $status, bool $hasReason): void
    {
        $answer = (new FictitiousPlatform())->deposit(self::document($poReference));

        self::assertSame($status, $answer->status);
        if ($hasReason) {
            self::assertNotNull($answer->reason);
            self::assertStringContainsString('Simulated', (string) $answer->reason, 'a reason says it is simulated');
        } else {
            self::assertNull($answer->reason);
        }
    }

    public function testAnOrderWithoutScenarioIsDeposited(): void
    {
        self::assertSame('deposited', (new FictitiousPlatform())->deposit(self::document('UNKNOWN-PO'))->status);
        self::assertSame('deposited', (new FictitiousPlatform())->deposit(self::document(null))->status);
    }

    public function testAnEmptyDocumentIsRefusedWithAClearMessage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/XML|PDF/');

        (new FictitiousPlatform())->deposit(new InvoiceDocument('inv-1', 'INV-1', '', '', 'inv-1.pdf'));
    }

    public function testNothingLeavesTheProcess(): void
    {
        foreach ([FictitiousPlatform::class, \App\Demo\Einvoice\FictitiousPlatformTransmitter::class] as $class) {
            $constructor = (new \ReflectionClass($class))->getConstructor();
            foreach ($constructor?->getParameters() ?? [] as $parameter) {
                self::assertDoesNotMatchRegularExpression('/Http|Client|Mailer/', (string) $parameter->getType(), $class . ' takes no network dependency');
            }
            $source = (string) file_get_contents((string) (new \ReflectionClass($class))->getFileName());
            self::assertDoesNotMatchRegularExpression('/curl_|fsockopen|stream_socket|file_get_contents\s*\(\s*[\'"]http/', $source, $class);
        }
    }

    public function testTheScenariosAreTheFourSimulatedStatuses(): void
    {
        self::assertSame(['deposited', 'rejected', 'refused', 'collected'], array_map(static fn (DemoTransmissionScenario $scenario): string => $scenario->value, DemoTransmissionScenario::cases()));
    }

    private static function document(?string $poReference): InvoiceDocument
    {
        $buyerReference = null === $poReference ? '' : '<ram:BuyerReference>' . $poReference . '</ram:BuyerReference>';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"><rsm:SupplyChainTradeTransaction><ram:ApplicableHeaderTradeAgreement>' . $buyerReference . '</ram:ApplicableHeaderTradeAgreement></rsm:SupplyChainTradeTransaction></rsm:CrossIndustryInvoice>';

        return new InvoiceDocument('inv-' . ($poReference ?? 'none'), 'INV-1', $xml, '%PDF-1.7 fictive', 'invoice.pdf');
    }
}
