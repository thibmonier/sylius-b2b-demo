<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\FictitiousPlatformAnswer;
use App\Demo\Einvoice\FictitiousPlatformInterface;
use App\Demo\Einvoice\FictitiousPlatformTransmitter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Status\TransmissionStatusCatalog;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceTransmissionHandler;

/**
 * US-183: the adapter between the transmission port of the plugin and the fictitious platform, run through the handler of
 * the plugin and a journal that honours the same contract (statuses of the demo configuration: refused and collected).
 */
final class FictitiousPlatformTransmitterTest extends TestCase
{
    public function testTheInvoiceIsDepositedOnceAndTheStatusIsLogged(): void
    {
        $platform = self::platform(new FictitiousPlatformAnswer('deposited', null));
        $journal = self::journal();

        self::handler($platform, $journal)->handle(self::document());

        self::assertSame(1, $platform->deposits);
        self::assertSame(['status' => 'deposited', 'message' => null], $journal->lines['inv-1']);
    }

    public function testARejectionKeepsItsReason(): void
    {
        $journal = self::journal();

        self::handler(self::platform(new FictitiousPlatformAnswer('rejected', 'Simulated reason: the buyer address is not in the directory.')), $journal)->handle(self::document());

        self::assertSame('rejected', $journal->lines['inv-1']['status']);
        self::assertSame('Simulated reason: the buyer address is not in the directory.', $journal->lines['inv-1']['message']);
    }

    public function testTheStatusesAddedByTheDemoConfigurationAreAccepted(): void
    {
        foreach (['refused', 'collected'] as $status) {
            $journal = self::journal();
            $document = new InvoiceDocument('inv-' . $status, 'INV-' . $status, '<xml/>', 'pdf', 'i.pdf');

            self::handler(self::platform(new FictitiousPlatformAnswer($status, null)), $journal)->handle($document);

            self::assertSame($status, $journal->lines['inv-' . $status]['status']);
        }
    }

    public function testAReplayedEventDoesNotDepositASecondTime(): void
    {
        $platform = self::platform(new FictitiousPlatformAnswer('deposited', null));
        $journal = self::journal();
        $handler = self::handler($platform, $journal);

        $handler->handle(self::document());
        $handler->handle(self::document());

        self::assertSame(1, $platform->deposits);
        self::assertCount(1, $journal->lines);
    }

    public function testAStatusTheConfigurationDoesNotDeclareIsNeverStored(): void
    {
        $journal = self::journal();

        self::handler(self::platform(new FictitiousPlatformAnswer('invented_status', null)), $journal)->handle(self::document());

        self::assertSame('error', $journal->lines['inv-1']['status']);
        self::assertStringContainsString('Unknown transmission status "invented_status"', (string) $journal->lines['inv-1']['message']);
        self::assertStringContainsString('refused', (string) $journal->lines['inv-1']['message'], 'the message lists the known statuses');
        self::assertNotContains('invented_status', array_column($journal->lines, 'status'));
    }

    private static function journal(): InMemoryTransmissionJournal
    {
        return new InMemoryTransmissionJournal(self::catalog());
    }

    private static function catalog(): TransmissionStatusCatalog
    {
        return new TransmissionStatusCatalog(['refused', 'collected']);
    }

    private static function handler(FictitiousPlatformInterface $platform, InMemoryTransmissionJournal $journal): InvoiceTransmissionHandler
    {
        return new InvoiceTransmissionHandler(new FictitiousPlatformTransmitter($platform), $journal, new NullLogger());
    }

    private static function document(): InvoiceDocument
    {
        return new InvoiceDocument('inv-1', 'INV-1', '<xml/>', 'pdf', 'invoice.pdf');
    }

    private static function platform(FictitiousPlatformAnswer $answer): SpyFictitiousPlatform
    {
        return new SpyFictitiousPlatform($answer);
    }
}
