<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** The excerpt to show: the CSV text (header and a few lines, e-mails masked) when the status is Shown, empty otherwise. No path, ever. */
final readonly class ExchangeFileExcerpt
{
    public function __construct(public ExchangeFileStatus $status, public string $csv = '')
    {
    }
}
