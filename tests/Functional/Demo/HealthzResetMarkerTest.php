<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ResetStatus;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-049: /healthz follows the marker of the nightly reset, including the one a fatal error leaves behind. A reset that
 * is running (marker "running", younger than the grace delay) is not an alert; one that ended badly or died is.
 */
final class HealthzResetMarkerTest extends WebTestCase
{
    use EnvironmentSwitch;

    private string $marker;

    private ResetStatus $status;

    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $this->marker = \dirname(__DIR__, 3) . '/var/demo-reset-failed';
        $this->status = new ResetStatus($this->marker);
        @unlink($this->marker);
        $this->client = null;
    }

    protected function tearDown(): void
    {
        // markStarted() registers a shutdown function that would write a failure marker when PHPUnit exits.
        $this->status->markSucceeded();
        @unlink($this->marker);
        parent::tearDown();
    }

    public function testASuccessfulResetLeavesHealthzAt200(): void
    {
        $status = $this->status;
        $status->markStarted();
        $status->markSucceeded();

        $this->assertHealth(200, 'ok');
    }

    public function testAResetInProgressDoesNotRaiseTheAlert(): void
    {
        $this->status->markStarted();

        $this->assertHealth(200, 'ok');
    }

    public function testAResetThatDiedWithoutAWordMakesHealthz503AfterTheGraceDelay(): void
    {
        $this->status->markStarted();
        touch($this->marker, time() - ResetStatus::MAX_RUN_SECONDS - 60);

        $this->assertHealth(503, 'reset failed');
    }

    public function testAFailedResetMakesHealthz503ThenTheNextSuccessClearsIt(): void
    {
        $status = $this->status;
        $status->markStarted();
        $status->markFailed();
        $this->assertHealth(503, 'reset failed');

        $status->markStarted();
        $this->assertHealth(503, 'reset failed');
        $status->markSucceeded();
        $this->assertHealth(200, 'ok');
    }

    private function assertHealth(int $status, string $body): void
    {
        $this->client ??= self::createClient();
        $this->client->request('GET', '/healthz', server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);

        self::assertResponseStatusCodeSame($status);
        self::assertSame($body, $this->client->getResponse()->getContent());
    }
}
