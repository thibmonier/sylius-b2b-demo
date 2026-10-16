<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-040 / M6: the health probes are outside the private gate, so they are limited per visitor IP. A monitor that polls
 * at a normal frequency is never refused. Asserted on the HTTP answers.
 */
final class HealthzTest extends WebTestCase
{
    use EnvironmentSwitch;

    public function testABurstFromOneVisitorIsLimitedAndAnotherVisitorIsNot(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_HEALTH' => '3']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($call = 1; $call <= 3; ++$call) {
            $client->request('GET', '/healthz', server: $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Call ' . $call);
        }
        $client->request('GET', '/healthz', server: $visitor);
        self::assertResponseStatusCodeSame(429);
        self::assertNotNull($client->getResponse()->headers->get('Retry-After'));
        $client->request('GET', '/health', server: $visitor);
        self::assertResponseStatusCodeSame(429, 'Both paths share the visitor budget.');

        $client->request('GET', '/healthz', server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);
        self::assertNotSame(429, $client->getResponse()->getStatusCode());
    }

    public function testAMonitorAtANormalFrequencyIsNeverRefused(): void
    {
        // The default budget of the image, not the one of the test suite.
        $this->setEnvironment(['DEMO_RATE_LIMIT_HEALTH' => '30']);
        $client = self::createClient();
        $monitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        // One call every ten seconds for a minute is six calls inside the default window.
        for ($call = 1; $call <= 6; ++$call) {
            $client->request('GET', '/healthz', server: $monitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Call ' . $call);
        }
    }
}
