<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use Tmonier\SyliusEinvoiceTransmissionPlugin\Journal\TransmissionJournalInterface;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Status\TransmissionStatusCatalog;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/** Same contract as the DBAL journal of the plugin (one line per invoice, statuses checked by the catalog), without a database. */
final class InMemoryTransmissionJournal implements TransmissionJournalInterface
{
    /** @var array<string, array{status: string, message: ?string}> */
    public array $lines = [];

    public function __construct(private readonly TransmissionStatusCatalog $catalog)
    {
    }

    public function open(InvoiceDocument $document): bool
    {
        if (isset($this->lines[$document->invoiceId])) {
            return false;
        }
        $this->lines[$document->invoiceId] = ['status' => 'not_transmitted', 'message' => null];

        return true;
    }

    public function record(string $invoiceId, string $status, ?string $message): void
    {
        $this->catalog->assertKnown($status);
        $this->lines[$invoiceId] = ['status' => $status, 'message' => $message];
    }
}
