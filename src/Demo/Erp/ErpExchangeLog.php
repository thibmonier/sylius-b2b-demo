<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** US-188: the journal of the exchanges, read only: the files sent (journal of the export plugin) and the answers of the simulator, most recent first. */
final readonly class ErpExchangeLog
{
    public function __construct(
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
        private OutboundBatches $outbound,
        private SimulatorJournal $simulator,
    ) {
    }

    /** @return list<ExchangeBatch> */
    public function batches(): array
    {
        $batches = [];
        foreach ($this->outbound->all() as $sent) {
            $batches[] = new ExchangeBatch('out', $sent->file, $sent->file, $sent->at, $this->linesOf($sent), null, null, null);
        }
        foreach ($this->simulator->all() as $answer) {
            $batches[] = new ExchangeBatch('in', $answer->name, $answer->source, $answer->at, $answer->read, $answer->accepted, $answer->rejected, $answer->reason);
        }
        usort($batches, static fn (ExchangeBatch $a, ExchangeBatch $b): int => $b->at <=> $a->at);

        return $batches;
    }

    /** The lines of the file when it is still there, the number of orders it held otherwise. */
    private function linesOf(OutboundBatch $sent): int
    {
        $content = is_file(rtrim($this->directory, '/') . '/' . $sent->file) ? @file_get_contents(rtrim($this->directory, '/') . '/' . $sent->file) : false;
        if (false === $content) {
            return $sent->orders;
        }

        return max(0, \count(array_filter(explode("\n", $content), static fn (string $line): bool => '' !== trim($line))) - 1);
    }
}
