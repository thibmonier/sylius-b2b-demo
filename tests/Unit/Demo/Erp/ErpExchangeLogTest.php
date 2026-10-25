<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\ErpExchangeLog;
use App\Demo\Erp\ImportedBatches;
use App\Demo\Erp\ImportedBatchRow;
use App\Demo\Erp\InboundBatch;
use App\Demo\Erp\OutboundBatch;
use App\Demo\Erp\OutboundBatches;
use App\Demo\Erp\SimulatorJournal;
use PHPUnit\Framework\TestCase;

/** US-188: the journal of the exchanges, read only: the batches sent (journal of the export plugin) and the ones received (acknowledgements and rejections of the simulator), most recent first. */
final class ErpExchangeLogTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/erp-log-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/{,.}*', \GLOB_BRACE) ?: [] as $file) {
            is_file($file) && unlink($file);
        }
        rmdir($this->directory);
    }

    public function testTheBatchesOfBothDirectionsAreMergedMostRecentFirst(): void
    {
        file_put_contents($this->directory . '/orders-a.csv', "header\nl1\nl2\nl3\n");
        $journal = new SimulatorJournal($this->directory);
        $journal->record(new InboundBatch('orders-a.csv', 'ack-orders-a.csv', new \DateTimeImmutable('2026-10-24T10:20:00Z'), 3, 2, 1, null));
        $journal->record(new InboundBatch('orders-b.csv', '', new \DateTimeImmutable('2026-10-24T11:20:00Z'), 5, 0, 5, 'header'));

        $batches = (new ErpExchangeLog($this->directory, $this->outbound(new OutboundBatch('orders-a.csv', new \DateTimeImmutable('2026-10-24T10:15:00Z'), 3)), $journal))->batches();

        self::assertSame(['in', 'in', 'out'], array_map(static fn ($batch): string => $batch->direction, $batches));
        self::assertSame(['orders-b.csv', 'ack-orders-a.csv', 'orders-a.csv'], array_map(static fn ($batch): string => $batch->name ?: $batch->source, $batches));
        $out = $batches[2];
        self::assertSame([3, null, null], [$out->read, $out->accepted, $out->rejected], 'a batch sent: its lines; accepted and rejected are the business of the reader');
        self::assertSame([5, 0, 5, 'header'], [$batches[0]->read, $batches[0]->accepted, $batches[0]->rejected, $batches[0]->reason]);
    }

    public function testWithoutAnyExchangeTheLogIsEmpty(): void
    {
        self::assertSame([], (new ErpExchangeLog($this->directory, $this->outbound(), new SimulatorJournal($this->directory)))->batches());
    }

    public function testAMissingDirectoryGivesAnEmptyLogAndCreatesNothing(): void
    {
        $missing = $this->directory . '/absent';

        self::assertSame([], (new ErpExchangeLog($missing, $this->outbound(), new SimulatorJournal($missing)))->batches());
        self::assertDirectoryDoesNotExist($missing);
    }

    public function testTheJournalCanBeEmptiedByTheReset(): void
    {
        $journal = new SimulatorJournal($this->directory);
        $journal->record(new InboundBatch('orders-a.csv', 'ack-orders-a.csv', new \DateTimeImmutable('2026-10-24T10:20:00Z'), 1, 1, 0, null));

        $journal->clear();

        self::assertSame([], $journal->all());
        self::assertFalse($journal->processed('orders-a.csv'));
    }

    public function testTheImportBatchesOfTheJournalOfThePluginAreListedWithTheirReport(): void
    {
        $imports = new class() implements ImportedBatches {
            public function all(): array
            {
                return [new ImportedBatchRow('prices-a.csv', str_repeat('ab', 32), 'prices', 'rejected', new \DateTimeImmutable('2026-10-24T12:00:00Z'), 3, 0, 2, [['line' => 3, 'reason' => 'price'], ['line' => 4, 'reason' => 'currency']])];
            }
        };

        $batches = (new ErpExchangeLog($this->directory, $this->outbound(), new SimulatorJournal($this->directory), $imports))->batches();

        self::assertCount(1, $batches);
        self::assertSame(['in', 'import-prices', 'prices-a.csv', 'rejected', [3, 0, 2]], [$batches[0]->direction, $batches[0]->kind, $batches[0]->name, $batches[0]->outcome, [$batches[0]->read, $batches[0]->accepted, $batches[0]->rejected]]);
        self::assertSame(str_repeat('ab', 32), $batches[0]->checksum);
        self::assertSame([['line' => 3, 'reason' => 'price'], ['line' => 4, 'reason' => 'currency']], $batches[0]->report);
    }

    public function testTheKindOfTheOtherBatchesIsOrdersOrAcknowledgement(): void
    {
        $journal = new SimulatorJournal($this->directory);
        $journal->record(new InboundBatch('orders-a.csv', 'ack-orders-a.csv', new \DateTimeImmutable('2026-10-24T10:20:00Z'), 1, 1, 0, null));

        $batches = (new ErpExchangeLog($this->directory, $this->outbound(new OutboundBatch('orders-a.csv', new \DateTimeImmutable('2026-10-24T10:15:00Z'), 1)), $journal))->batches();

        self::assertSame(['acknowledgement', 'orders'], array_map(static fn ($batch): ?string => $batch->kind, $batches));
    }

    private function outbound(OutboundBatch ...$batches): OutboundBatches
    {
        return new class(array_values($batches)) implements OutboundBatches {
            /** @param list<OutboundBatch> $batches */
            public function __construct(private readonly array $batches)
            {
            }

            public function all(): array
            {
                return $this->batches;
            }
        };
    }
}
