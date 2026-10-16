<?php

declare(strict_types=1);

namespace App\Demo\Reset;

use Symfony\Component\Clock\ClockInterface;

/**
 * When the next nightly reset runs (T-033-05). The schedule is the one of docker/crontab: 03:00 Paris time, daylight
 * saving time included, whatever the zone of the server clock.
 */
final readonly class NextResetProvider
{
    private const TIME_ZONE = 'Europe/Paris';

    private const HOUR = 3;

    public function __construct(private ClockInterface $clock)
    {
    }

    public function next(): NextReset
    {
        $now = $this->clock->now()->setTimezone(new \DateTimeZone(self::TIME_ZONE));
        $today = $now->setTime(self::HOUR, 0);
        // At 03:00 sharp the reset is running: the announced one is tomorrow's.
        if ($today > $now) {
            return new NextReset($today, true);
        }

        // "+1 day" keeps the wall-clock time, so 03:00 stays 03:00 across the clock changes.
        return new NextReset($today->modify('+1 day'), false);
    }
}
