<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** What the page "Flat-file exchange" can say about the last file of the exchange directory (US-193). */
enum ExchangeFileStatus: string
{
    case Shown = 'shown';
    case NoFile = 'no_file';
    case Empty = 'empty';
    case OtherChannel = 'other_channel';
    case Unavailable = 'unavailable';
}
