<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * US-028: the public demo mailbox. Mailpit is unreachable here (unused local port), so the page
 * must degrade to a clear message; the routes must be read-only.
 */
final class DemoMailboxTest extends WebTestCase
{
    public function testTheMailboxIsNotFoundWhenTheApiIsNotConfigured(): void
    {
        $this->withApiUrl('', function (): void {
            $client = self::createClient();
            $client->request('GET', '/demo/mails');
            self::assertResponseStatusCodeSame(404);
        });
    }

    public function testAnUnreachableMailpitShowsAClearMessageWithoutTechnicalDetails(): void
    {
        $this->withApiUrl('http://127.0.0.1:9', function (): void {
            $client = self::createClient();
            $client->request('GET', '/demo/mails');

            self::assertResponseStatusCodeSame(503);
            $content = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('data-demo-mails-unavailable', $content);
            self::assertStringNotContainsString('127.0.0.1', $content);
            self::assertStringNotContainsString('mailpit', strtolower($content));
        });
    }

    public function testTheMailboxRoutesAreGetOnly(): void
    {
        $client = self::createClient();
        $router = $client->getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        $found = 0;
        foreach ($router->getRouteCollection() as $name => $route) {
            if (str_starts_with($name, 'app_demo_mail')) {
                ++$found;
                self::assertSame(['GET'], $route->getMethods(), $name);
            }
        }
        self::assertSame(2, $found);

        foreach (['POST', 'DELETE', 'PUT'] as $method) {
            $client->request($method, '/demo/mails/abc123');
            self::assertResponseStatusCodeSame(405, $method);
        }
    }

    private function withApiUrl(string $url, callable $test): void
    {
        $previous = [$_SERVER['DEMO_MAILPIT_API_URL'] ?? null, $_ENV['DEMO_MAILPIT_API_URL'] ?? null, getenv('DEMO_MAILPIT_API_URL')];
        $_SERVER['DEMO_MAILPIT_API_URL'] = $_ENV['DEMO_MAILPIT_API_URL'] = $url;
        putenv('DEMO_MAILPIT_API_URL=' . $url);
        self::ensureKernelShutdown();

        try {
            $test();
        } finally {
            self::restore($_SERVER, $previous[0]);
            self::restore($_ENV, $previous[1]);
            putenv(false === $previous[2] ? 'DEMO_MAILPIT_API_URL' : 'DEMO_MAILPIT_API_URL=' . $previous[2]);
            self::ensureKernelShutdown();
        }
    }

    /** @param array<string, mixed> $bag */
    private static function restore(array &$bag, mixed $value): void
    {
        if (null === $value) {
            unset($bag['DEMO_MAILPIT_API_URL']);

            return;
        }
        $bag['DEMO_MAILPIT_API_URL'] = $value;
    }
}
