<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Analytics\UmamiTracker;
use App\Demo\Http\SecurityHeadersSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/** US-143: the Umami host is allowed by the CSP only when configured, and only on the measured pages. */
final class SecurityHeadersUmamiTest extends TestCase
{
    private const ID = '0f8fad5b-d9cb-469f-a165-70867728950e';

    public function testAConfiguredUmamiExtendsScriptAndConnectSources(): void
    {
        $csp = $this->csp(new UmamiTracker('https://cloud.umami.is/script.js', self::ID), '/fr_FR/');

        self::assertStringContainsString("script-src 'self' https://cloud.umami.is 'unsafe-inline'", $csp);
        // Umami Cloud serves the script from cloud.umami.is but sends the events to gateway.umami.is (2026-10-19 incident).
        self::assertStringContainsString("connect-src 'self' https://cloud.umami.is https://gateway.umami.is;", $csp);
        self::assertStringContainsString("default-src 'self';", $csp);
    }

    public function testASelfHostedUmamiCollectsOnItsOwnHost(): void
    {
        $csp = $this->csp(new UmamiTracker('https://stats.example.test/script.js', self::ID), '/fr_FR/');

        self::assertStringContainsString("connect-src 'self' https://stats.example.test;", $csp);
    }

    public function testAnExplicitCollectionUrlIsAllowed(): void
    {
        $csp = $this->csp(new UmamiTracker('https://stats.example.test/script.js', self::ID, 'https://collect.example.test/api/send'), '/fr_FR/');

        self::assertStringContainsString("connect-src 'self' https://stats.example.test https://collect.example.test;", $csp);
    }

    public function testWithoutUmamiTheCspIsUnchanged(): void
    {
        self::assertSame(SecurityHeadersSubscriber::CSP, $this->csp(new UmamiTracker('', ''), '/fr_FR/'));
    }

    public function testAnInvalidConfigurationLeavesTheCspUnchanged(): void
    {
        self::assertSame(SecurityHeadersSubscriber::CSP, $this->csp(new UmamiTracker('http://cloud.umami.is/script.js', self::ID), '/fr_FR/'));
    }

    public function testTheAdminAndTheMailPagesKeepTheStrictCsp(): void
    {
        $umami = new UmamiTracker('https://cloud.umami.is/script.js', self::ID);

        self::assertSame(SecurityHeadersSubscriber::CSP, $this->csp($umami, '/admin/orders'));
        self::assertSame(SecurityHeadersSubscriber::CSP, $this->csp($umami, '/demo/mails'));
    }

    private function csp(UmamiTracker $umami, string $path): string
    {
        $event = new ResponseEvent($this->createStub(HttpKernelInterface::class), Request::create($path), HttpKernelInterface::MAIN_REQUEST, new Response());
        (new SecurityHeadersSubscriber($umami))($event);

        return (string) $event->getResponse()->headers->get('Content-Security-Policy');
    }
}
