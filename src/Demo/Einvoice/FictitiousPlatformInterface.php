<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/** US-183: the "fictitious accredited platform" (PA) of the demo, in process: nothing is sent anywhere (ADR 0008, ADR 0016). */
interface FictitiousPlatformInterface
{
    /** @throws \InvalidArgumentException when the XML or the PDF is empty */
    public function deposit(InvoiceDocument $document): FictitiousPlatformAnswer;
}
