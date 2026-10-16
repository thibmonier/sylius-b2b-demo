<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Reset;

use App\Demo\Reset\NextResetProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/** US-041 / T-033-05: the banner announces the next nightly reset, 03:00 Paris time (docker/crontab). */
final class NextResetProviderTest extends TestCase
{
    /** @return iterable<string, array{string, string, bool}> now (any zone) => next reset in Paris time, same Paris day? */
    public static function schedule(): iterable
    {
        yield 'before 03:00 it is today' => ['2026-10-16 02:30:00 Europe/Paris', '2026-10-16T03:00:00+02:00', true];
        yield 'at 03:00 the reset is running, the next one is tomorrow' => ['2026-10-16 03:00:00 Europe/Paris', '2026-10-17T03:00:00+02:00', false];
        yield 'after 03:00 it is tomorrow' => ['2026-10-16 15:00:00 Europe/Paris', '2026-10-17T03:00:00+02:00', false];
        yield 'a server clock in UTC gives the Paris answer' => ['2026-10-16 00:30:00 UTC', '2026-10-16T03:00:00+02:00', true];
        yield 'the evening before the spring change, 03:00 is 7 hours later in UTC' => ['2027-03-27 20:00:00 Europe/Paris', '2027-03-28T03:00:00+02:00', false];
        yield 'the day of the autumn change the reset is at 03:00 winter time' => ['2026-10-24 12:00:00 Europe/Paris', '2026-10-25T03:00:00+01:00', false];
        yield 'just after midnight it is still today' => ['2026-10-25 00:10:00 Europe/Paris', '2026-10-25T03:00:00+01:00', true];
    }

    #[DataProvider('schedule')]
    public function testTheNextResetIsAt0300ParisTime(string $now, string $expected, bool $today): void
    {
        $next = (new NextResetProvider(new MockClock($now)))->next();

        self::assertSame($expected, $next->at->format('c'));
        self::assertSame($today, $next->today);
    }
}
