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

    public function testTheLastStepReachedIsRemembered(): void
    {
        $progress = new JourneyProgress($this->requests);

        $progress->advance('buyer', 3, 'aaa');

        self::assertSame(3, $progress->step('buyer'));
        self::assertTrue($progress->hasStarted('buyer'));
        self::assertSame(1, $progress->step('approver'), 'Journeys do not share their progress.');
    }

    public function testTheFirstFingerprintIsNotAReset(): void
    {
        $progress = new JourneyProgress($this->requests);

        self::assertFalse($progress->hasBeenReset(''));
        self::assertFalse($progress->hasBeenReset(''));
    }

    public function testAResetIsSeenWithoutWritingAnythingAndStaysSeenUntilTheVisitorActs(): void
    {
        $progress = new JourneyProgress($this->requests);
        $progress->advance('buyer', 3, 'aaa');
        $before = $this->requests->getSession()->all();

        self::assertTrue($progress->hasBeenReset('bbb'));
        self::assertTrue($progress->hasBeenReset('bbb'), 'Reading does not consume the notice.');

        self::assertSame($before, $this->requests->getSession()->all(), 'A read leaves the session as it was.');
    }

    public function testActingAfterAResetDropsTheOldProgressAndAcknowledgesIt(): void
    {
        $progress = new JourneyProgress($this->requests);
        $progress->advance('buyer', 3, 'aaa');
        $progress->advance('approver', 2, 'aaa');

        $progress->advance('buyer', 2, 'bbb');

        self::assertSame(2, $progress->step('buyer'));
        self::assertFalse($progress->hasStarted('approver'), 'The progress of before the reset is gone.');
        self::assertFalse($progress->hasBeenReset('bbb'));
    }

    public function testAResetWithoutProgressIsSilent(): void
    {
        $progress = new JourneyProgress($this->requests);

        self::assertFalse($progress->hasBeenReset('bbb'));
    }

    public function testWithoutSessionNothingIsRemembered(): void
    {
        $progress = new JourneyProgress(new RequestStack());

        $progress->advance('buyer', 3, 'aaa');

        self::assertSame(1, $progress->step('buyer'));
        self::assertFalse($progress->hasBeenReset('bbb'));
    }

    public function testReadingNeverStartsASessionForAVisitorWithoutOne(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $requests = new RequestStack();
        $requests->push($request);
        $progress = new JourneyProgress($requests);

        self::assertSame(1, $progress->step('buyer'));
        self::assertFalse($progress->hasBeenReset('aaa'));

        self::assertFalse($session->isStarted());
    }
}
