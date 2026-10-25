<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** The colour family of a badge or of a card of "My account": what it says is always in words too, never by colour alone. */
enum AccountTone: string
{
    case Good = 'good';
    case Waiting = 'waiting';
    case Late = 'late';
    case Neutral = 'neutral';
    case Info = 'info';
}
