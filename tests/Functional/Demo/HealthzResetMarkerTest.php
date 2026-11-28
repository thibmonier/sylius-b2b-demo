<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ResetStatus;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-049, US-321: /healthz (platform health plugin, liveness) follows the marker of the nightly reset, including the one a fatal error leaves behind. A reset that
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
        $this->client = self::createClient();
        $marker = self::getContainer()->getParameter('demo.reset_marker_file');
        \assert(\is_string($marker));
        self::assertStringNotContainsString('/var/demo-reset-failed', $marker, 'The tests must never share the marker (and the lock) of the project root with another checkout or worker.');
        $this->marker = $marker;
        $this->status = new ResetStatus($this->marker);
        @unlink($this->marker);
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

        $this->assertHealth(200);
    }

    public function testAResetInProgressDoesNotRaiseTheAlert(): void
    {
        $this->status->markStarted();

        $this->assertHealth(200);
    }

    public function testAResetThatDiedWithoutAWordMakesHealthz503AfterTheGraceDelay(): void
    {
        $this->status->markStarted();
        touch($this->marker, time() - ResetStatus::MAX_RUN_SECONDS - 60);

        $this->assertHealth(503);
    }

    public function testAFailedResetMakesHealthz503ThenTheNextSuccessClearsIt(): void
    {
        $status = $this->status;
        $status->markStarted();
        $status->markFailed();
        $this->assertHealth(503);

        $status->markStarted();
        $this->assertHealth(503);
        $status->markSucceeded();
        $this->assertHealth(200);
    }

    private function assertHealth(int $status): void
    {
        \assert(null !== $this->client);
        $this->client->request('GET', '/healthz', server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);

        self::assertResponseStatusCodeSame($status);
        self::assertSame('', $this->client->getResponse()->getContent(), 'The liveness answer has an empty body.');
    }
}
