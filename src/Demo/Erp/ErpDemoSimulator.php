<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\AtomicFileDepositor;

/**
 * "ERP Démo (fictif)" (US-188): reads the last file of orders of the exchange directory and answers with a simulated acknowledgement
 * of receipt, a CSV `order_number;webhook_id;status;reason` with one line per line of the file (`received` or `rejected` with the
 * reason of the first failed check), deposited atomically as `ack-<name of the file>`. A file whose header is not the contract, or
 * that holds no line, is rejected as a whole: no acknowledgement, only a line of the journal. The file it read is never changed.
 * Files only: the class holds no network client.
 */
final readonly class ErpDemoSimulator
{
    private const ACK_HEADER = ['order_number', 'webhook_id', 'status', 'reason'];

    public function __construct(
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
        private SimulatorJournal $journal,
        private ClockInterface $clock,
    ) {
    }

    public function process(): SimulationResult
    {
        $source = $this->lastOrdersFile();
        if (null === $source || $this->journal->processed(basename($source))) {
            return new SimulationResult(SimulationStatus::NothingToProcess);
        }

        $name = basename($source);
        $lines = array_values(array_filter(explode("\n", str_replace("\r\n", "\n", (string) @file_get_contents($source))), static fn (string $line): bool => '' !== trim($line)));
        $header = array_shift($lines);
        if ($header !== implode(';', OrderLinesCsv::HEADER)) {
            return $this->reject($name, \count($lines), 'header');
        }
        if ([] === $lines) {
            return $this->reject($name, 0, 'empty');
        }

        $ack = [implode(';', self::ACK_HEADER)];
        $accepted = 0;
        foreach ($lines as $line) {
            $fields = str_getcsv($line, ';', '"', '');
            $reason = $this->problemOf($fields);
            $accepted += '' === $reason ? 1 : 0;
            $ack[] = implode(';', [$fields[0] ?? '', $fields[1] ?? '', '' === $reason ? 'received' : 'rejected', $reason]);
        }

        $ackName = 'ack-' . $name;
        (new AtomicFileDepositor($this->directory))->deposit($ackName, [implode("\n", $ack) . "\n"]);
        $this->journal->record(new InboundBatch($name, $ackName, $this->clock->now(), \count($lines), $accepted, \count($lines) - $accepted, null));

        return new SimulationResult(SimulationStatus::Acknowledged, $name, \count($lines), $accepted, \count($lines) - $accepted);
    }

    private function reject(string $name, int $read, string $reason): SimulationResult
    {
        $this->journal->record(new InboundBatch($name, '', $this->clock->now(), $read, 0, $read, $reason));

        return new SimulationResult(SimulationStatus::Rejected, $name, $read, 0, $read);
    }

    private function lastOrdersFile(): ?string
    {
        if (!is_dir($this->directory)) {
            return null;
        }
        $files = glob(rtrim($this->directory, '/') . '/orders-*.csv') ?: [];
        usort($files, static fn (string $a, string $b): int => [(int) filemtime($b), $b] <=> [(int) filemtime($a), $a]);

        return $files[0] ?? null;
    }

    /**
     * @param list<string|null> $fields
     *
     * @return string the code of the first failed check, '' when the line is valid
     */
    private function problemOf(array $fields): string
    {
        return match (true) {
            \count($fields) !== \count(OrderLinesCsv::HEADER) => 'columns',
            '' === trim((string) $fields[0]) => 'order_number',
            1 !== preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', (string) $fields[1]) => 'webhook_id',
            1 !== preg_match('/^[A-Z]{3}$/', (string) $fields[3]) => 'currency',
            1 !== preg_match('/^[1-9][0-9]*$/', (string) $fields[6]) => 'quantity',
            1 !== preg_match('/^[0-9]+$/', (string) $fields[7]) || 1 !== preg_match('/^[0-9]+$/', (string) $fields[8]) => 'amount',
            1 !== preg_match('/^[A-Z]{2}$/', (string) $fields[9]) => 'country',
            default => '',
        };
    }
}
