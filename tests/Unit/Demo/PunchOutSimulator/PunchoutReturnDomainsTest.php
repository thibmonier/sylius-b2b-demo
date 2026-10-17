<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-027: the public HTTPS host of the demo is a return domain of the PunchOut buyer, so the simulator can receive the cart. */
final class PunchoutReturnDomainsTest extends TestCase
{
    public function testThePublicHttpsHostIsAdded(): void
    {
        self::assertSame(
            ['erp.acme.example', 'demo.example.org'],
            PunchoutDemoFixture::returnDomains('https://Demo.Example.org/'),
        );
    }

    /** @return iterable<string, array{string}> */
    public static function hostsTheBuyerMustNotTrust(): iterable
    {
        yield 'local HTTP URI' => ['http://localhost:8080'];
        yield 'HTTP public URI' => ['http://demo.example.org'];
        yield 'empty' => [''];
        yield 'not a URL' => ['https://'];
        yield 'already the ERP domain' => ['https://erp.acme.example'];
    }

    #[DataProvider('hostsTheBuyerMustNotTrust')]
    public function testOnlyTheErpDomainOtherwise(string $defaultUri): void
    {
        self::assertSame(['erp.acme.example'], PunchoutDemoFixture::returnDomains($defaultUri));
    }

    public function testThePublicSubdomainIsAddedAlongsideTheRailwayDomain(): void
    {
        self::assertSame(
            ['erp.acme.example', 'demo.example.org', 'sylius-b2b.site.example'],
            PunchoutDemoFixture::returnDomains('https://demo.example.org', 'Sylius-B2B.Site.example'),
        );
    }

    public function testAnInvalidOrRepeatedPublicHostAddsNothing(): void
    {
        self::assertSame(['erp.acme.example', 'demo.example.org'], PunchoutDemoFixture::returnDomains('https://demo.example.org', '^.*$'));
        self::assertSame(['erp.acme.example', 'demo.example.org'], PunchoutDemoFixture::returnDomains('https://demo.example.org', 'demo.example.org'));
        self::assertSame(['erp.acme.example', 'sylius-b2b.site.example'], PunchoutDemoFixture::returnDomains('http://localhost:8080', 'sylius-b2b.site.example'));
    }
}
