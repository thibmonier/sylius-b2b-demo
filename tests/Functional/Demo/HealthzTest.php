<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ResetStatus;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-321: /healthz is the entry point of the platform health plugin (liveness for Railway and Docker, tokens for the
 * supervision), outside the private gate, with the two checks of the demo among its probes. /health is gone.
 */
final class HealthzTest extends WebTestCase
{
    use EnvironmentSwitch;

    private ?string $marker = null;

    protected function tearDown(): void
    {
        if (null !== $this->marker) {
            @unlink($this->marker);
        }
        parent::tearDown();
    }

    public function testRailwayGetsA200WithAnEmptyBodyOnAHealthyDeployment(): void
    {
        $client = self::createClient();

        $client->request('GET', '/healthz');

        self::assertResponseStatusCodeSame(200);
        self::assertSame('', $client->getResponse()->getContent());
    }

    public function testAFailedNightlyResetMakesItA503AndTheResetCheckCritical(): void
    {
        $client = self::createClient();
        $marker = self::getContainer()->getParameter('demo.reset_marker_file');
        \assert(\is_string($marker));
        $this->marker = $marker;
        new ResetStatus($marker)->markFailed();

        $client->request('GET', '/healthz');
        self::assertResponseStatusCodeSame(503);
        self::assertSame('', $client->getResponse()->getContent());

        self::assertSame('critical', $this->detail($client)['demo.reset'] ?? null);
    }

    public function testTheDetailTokenListsTheProbesIncludingTheTwoOfTheDemo(): void
    {
        $client = self::createClient();

        $checks = $this->detail($client);

        self::assertSame('ok', $checks['demo.configuration'] ?? null);
        self::assertSame('ok', $checks['demo.reset'] ?? null);
        self::assertArrayHasKey('data.connection', $checks);
    }

    public function testTheGlobalTokenAnswersTheStatusWithoutTheProbes(): void
    {
        $client = self::createClient();

        $client->request('GET', '/healthz', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . self::token('HEALTH_HTTP_TOKEN')]);

        self::assertResponseStatusCodeSame(200);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertSame(['generated_at', 'status'], self::sortedKeys($payload));
    }

    public function testTheFormerHealthPathIsGone(): void
    {
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '0']);
        $client = self::createClient();

        $client->request('GET', '/health');

        self::assertResponseStatusCodeSame(404);
    }

    public function testWithoutAnyTokenTheEntryPointIsInactive(): void
    {
        $this->setEnvironment(['HEALTH_HTTP_TOKEN' => '', 'HEALTH_HTTP_DETAIL_TOKEN' => '']);
        $client = self::createClient();

        $client->request('GET', '/healthz');

        self::assertResponseStatusCodeSame(404);
    }

    /** @return array<string, string> status of each probe, by identifier */
    private function detail(KernelBrowser $client): array
    {
        $client->request('GET', '/healthz', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . self::token('HEALTH_HTTP_DETAIL_TOKEN')]);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertIsArray($payload['checks'] ?? null);

        $statuses = [];
        foreach ($payload['checks'] as $check) {
            self::assertIsArray($check);
            self::assertIsString($check['identifier'] ?? null);
            self::assertIsString($check['status'] ?? null);
            $statuses[$check['identifier']] = $check['status'];
        }

        return $statuses;
    }

    private static function token(string $name): string
    {
        $token = $_SERVER[$name] ?? null;
        \assert(\is_string($token) && '' !== $token);

        return $token;
    }

    /**
     * @param array<array-key, mixed> $payload
     *
     * @return list<array-key>
     */
    private static function sortedKeys(array $payload): array
    {
        $keys = array_keys($payload);
        sort($keys);

        return $keys;
    }
}
