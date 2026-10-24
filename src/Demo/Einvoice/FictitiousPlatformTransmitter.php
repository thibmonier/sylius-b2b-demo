<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceTransmitterInterface;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\TransmissionResult;

/** US-183: the adapter of the demo for the transmission port of the plugin; it hands the invoice to the fictitious platform. */
final readonly class FictitiousPlatformTransmitter implements InvoiceTransmitterInterface
{
    public function __construct(private FictitiousPlatformInterface $platform)
    {
    }

    public function transmit(InvoiceDocument $document): TransmissionResult
    {
        $answer = $this->platform->deposit($document);

        return new TransmissionResult($answer->status, $answer->reason);
    }
}
