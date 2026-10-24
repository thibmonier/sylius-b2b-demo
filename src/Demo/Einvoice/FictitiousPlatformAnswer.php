<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

/** US-183: what the fictitious platform answers to a deposit. The status is a plain string: the journal of the plugin checks it. */
final readonly class FictitiousPlatformAnswer
{
    public function __construct(public string $status, public ?string $reason)
    {
    }
}
