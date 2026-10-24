<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\FictitiousPlatformAnswer;
use App\Demo\Einvoice\FictitiousPlatformInterface;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;

/** Answers a fixed answer and counts the deposits it received. */
final class SpyFictitiousPlatform implements FictitiousPlatformInterface
{
    public int $deposits = 0;

    public function __construct(private readonly FictitiousPlatformAnswer $answer)
    {
    }

    public function deposit(InvoiceDocument $document): FictitiousPlatformAnswer
    {
        ++$this->deposits;

        return $this->answer;
    }
}
