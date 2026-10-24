<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\ErpDemoSimulator;
use App\Demo\Erp\SimulationStatus;
use App\Demo\Erp\SimulatorJournal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;

/**
 * US-188: "ERP Démo (fictif)" reads the last file of orders of the exchange directory and deposits a simulated acknowledgement of
 * receipt (a CSV of statuses, atomically), or rejects the file and records why. It only reads and writes files of the directory:
 * no network, the file it read is left as it was.
 */
final class ErpDemoSimulatorTest extends TestCase
{
    private const ORDERS = 'orders-20261024T101500Z-3fa9c1d2.csv';

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/erp-sim-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/.incoming', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (array_merge(glob($this->directory . '/.incoming/*') ?: [], glob($this->directory . '/.*') ?: [], glob($this->directory . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        rmdir($this->directory . '/.incoming');
        rmdir($this->directory);
    }

    public function testAnOrderFileIsAcknowledgedLineByLineAndRecorded(): void
    {
        $this->orders([$this->row(1), $this->row(2), $this->row(3)]);

        $result = $this->simulator()->process();

        self::assertSame(SimulationStatus::Acknowledged, $result->status);
        self::assertSame([3, 3, 0], [$result->read, $result->accepted, $result->rejected]);
        $ack = $this->lines('ack-' . self::ORDERS);
        self::assertSame('order_number;webhook_id;status;reason', $ack[0]);
        self::assertCount(4, $ack);
        foreach (\array_slice($ack, 1) as $line) {
            self::assertSame('received', explode(';', $line)[2]);
        }
        $batches = $this->journal()->all();
        self::assertCount(1, $batches);
        self::assertSame([self::ORDERS, 'ack-' . self::ORDERS, 3, 3, 0], [$batches[0]->source, $batches[0]->name, $batches[0]->read, $batches[0]->accepted, $batches[0]->rejected]);
    }

    public function testTheFileItReadIsLeftIntactAndNoProvisionalFileRemains(): void
    {
        $this->orders([$this->row(1)]);
        $before = md5_file($this->directory . '/' . self::ORDERS);

        $this->simulator()->process();

        self::assertSame($before, md5_file($this->directory . '/' . self::ORDERS));
        self::assertSame([], glob($this->directory . '/.incoming/*'));
    }

    public function testAnInvalidLineIsRejectedWithItsReasonAndTheOthersAreAcknowledged(): void
    {
        $this->orders([$this->row(1), str_replace(';10;450;', ';-3;450;', $this->row(2)), 'only;three;columns', $this->row(4)]);

        $result = $this->simulator()->process();

        self::assertSame(SimulationStatus::Acknowledged, $result->status);
        self::assertSame([4, 2, 2], [$result->read, $result->accepted, $result->rejected]);
        $statuses = array_map(static fn (string $line): array => \array_slice(explode(';', $line), 2, 2), \array_slice($this->lines('ack-' . self::ORDERS), 1));
        self::assertSame([['received', ''], ['rejected', 'quantity'], ['rejected', 'columns'], ['received', '']], $statuses);
    }

    public function testAFileWithAnInvalidHeaderIsRejectedWithoutAcknowledgementAndRecorded(): void
    {
        file_put_contents($this->directory . '/' . self::ORDERS, "num;id\n" . $this->row(1) . "\n" . $this->row(2) . "\n");

        $result = $this->simulator()->process();

        self::assertSame(SimulationStatus::Rejected, $result->status);
        self::assertSame([2, 0, 2], [$result->read, $result->accepted, $result->rejected]);
        self::assertSame([], glob($this->directory . '/ack-*'), 'no acknowledgement');
        $batches = $this->journal()->all();
        self::assertSame('header', $batches[0]->reason);
        self::assertSame('', $batches[0]->name);
        self::assertSame(self::ORDERS, $batches[0]->source);
    }

    public function testAFileWithOnlyTheHeaderIsRejectedAsEmpty(): void
    {
        $this->orders([]);

        self::assertSame(SimulationStatus::Rejected, $this->simulator()->process()->status);
        self::assertSame('empty', $this->journal()->all()[0]->reason);
    }

    public function testNothingToProcessWithoutFileAndAFileIsNeverProcessedTwice(): void
    {
        $simulator = $this->simulator();
        self::assertSame(SimulationStatus::NothingToProcess, $simulator->process()->status);

        $this->orders([$this->row(1)]);
        self::assertSame(SimulationStatus::Acknowledged, $simulator->process()->status);
        self::assertSame(SimulationStatus::NothingToProcess, $simulator->process()->status);
        self::assertCount(1, $this->journal()->all());
    }

    public function testTheLastFileIsTheOneRead(): void
    {
        $this->orders([$this->row(1)], 'orders-20261024T090000Z-aaaaaaaa.csv', 1000);
        $this->orders([$this->row(2)], self::ORDERS, 2000);

        $this->simulator()->process();

        self::assertSame(self::ORDERS, $this->journal()->all()[0]->source);
    }

    public function testAMissingDirectoryIsNothingToProcessAndNothingIsCreated(): void
    {
        $missing = $this->directory . '/absent';

        $result = (new ErpDemoSimulator($missing, new SimulatorJournal($missing), new MockClock('2026-10-24T10:20:00Z')))->process();

        self::assertSame(SimulationStatus::NothingToProcess, $result->status);
        self::assertDirectoryDoesNotExist($missing);
    }

    public function testTheSimulatorNeverReachesTheNetwork(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../../../src/Demo/Erp/ErpDemoSimulator.php');

        foreach (['HttpClient', 'curl_', 'fsockopen', 'stream_socket', 'file_get_contents(\'http', 'Component\\Process', 'exec(', 'shell_exec'] as $call) {
            self::assertStringNotContainsString($call, $source);
        }
    }

    /** @param list<string> $rows */
    private function orders(array $rows, string $name = self::ORDERS, int $time = 1000): void
    {
        file_put_contents($this->directory . '/' . $name, implode(';', OrderLinesCsv::HEADER) . "\n" . implode('', array_map(static fn (string $row): string => $row . "\n", $rows)));
        touch($this->directory . '/' . $name, $time);
    }

    private function row(int $n): string
    {
        return sprintf('%010d;0192a000-0000-7000-8000-%012d;2026-10-20T10:00:00Z;EUR;c***@acme.example;PAPER-A4;10;450;4500;FR', $n, $n);
    }

    /** @return list<string> */
    private function lines(string $name): array
    {
        self::assertFileExists($this->directory . '/' . $name);

        return explode("\n", rtrim((string) file_get_contents($this->directory . '/' . $name), "\n"));
    }

    private function simulator(): ErpDemoSimulator
    {
        return new ErpDemoSimulator($this->directory, $this->journal(), new MockClock('2026-10-24T10:20:00Z'));
    }

    private function journal(): SimulatorJournal
    {
        return new SimulatorJournal($this->directory);
    }
}
