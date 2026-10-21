<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Journey;

use App\Demo\Journey\JourneyProgress;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/** US-105: the progress lives in the session and is dropped, with one notice, when the demo has been reset. */
final class JourneyProgressTest extends TestCase
{
    private RequestStack $requests;

    protected function setUp(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requests = new RequestStack();
        $this->requests->push($request);
    }

    public function testAJourneyStartsAtStepOne(): void
    {
        $progress = new JourneyProgress($this->requests);

        self::assertSame(1, $progress->step('buyer'));
        self::assertFalse($progress->hasStarted('buyer'));
    }

    public function testTheLastStepVisitedIsRemembered(): void
    {
        $progress = new JourneyProgress($this->requests);

        $progress->moveTo('buyer', 3);

        self::assertSame(3, $progress->step('buyer'));
        self::assertTrue($progress->hasStarted('buyer'));
        self::assertSame(1, $progress->step('approver'), 'Journeys do not share their progress.');
    }

    public function testTheFirstFingerprintIsNotAReset(): void
    {
        $progress = new JourneyProgress($this->requests);

        self::assertFalse($progress->syncWithReset(''));
        self::assertFalse($progress->syncWithReset(''));
        self::assertFalse($progress->consumeResetNotice());
    }

    public function testAResetDropsTheProgressAndTheNoticeIsShownOnce(): void
    {
        $progress = new JourneyProgress($this->requests);
        $progress->syncWithReset('aaa');
        $progress->moveTo('buyer', 3);

        self::assertTrue($progress->syncWithReset('bbb'));

        self::assertSame(1, $progress->step('buyer'));
        self::assertTrue($progress->consumeResetNotice());
        self::assertFalse($progress->consumeResetNotice());
        self::assertFalse($progress->syncWithReset('bbb'), 'The same reset is not announced twice.');
    }

    public function testAResetWithoutProgressIsSilent(): void
    {
        $progress = new JourneyProgress($this->requests);
        $progress->syncWithReset('aaa');

        self::assertFalse($progress->syncWithReset('bbb'));
        self::assertFalse($progress->consumeResetNotice());
    }

    public function testWithoutSessionNothingIsRemembered(): void
    {
        $progress = new JourneyProgress(new RequestStack());

        $progress->moveTo('buyer', 3);

        self::assertSame(1, $progress->step('buyer'));
    }
}
