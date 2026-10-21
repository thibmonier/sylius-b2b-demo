<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Analytics;

use App\Demo\Analytics\UmamiTracker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-143: audience measurement is opt-in by configuration, https only, and never on the private or technical pages. */
final class UmamiTrackerTest extends TestCase
{
    private const ID = '0f8fad5b-d9cb-469f-a165-70867728950e';

    public function testAValidConfigurationEnablesTheMeasure(): void
    {
        $tracker = new UmamiTracker('https://cloud.umami.is/script.js', self::ID);

        self::assertTrue($tracker->isEnabled());
        self::assertSame('https://cloud.umami.is/script.js', $tracker->scriptUrl());
        self::assertSame(self::ID, $tracker->websiteId());
        self::assertSame('https://cloud.umami.is', $tracker->origin());
    }

    public function testUmamiCloudCollectsOnItsGateway(): void
    {
        self::assertSame('https://gateway.umami.is', (new UmamiTracker('https://cloud.umami.is/script.js', self::ID))->collectOrigin());
    }

    public function testASelfHostedUmamiCollectsOnTheScriptHost(): void
    {
        self::assertSame('https://stats.example.test', (new UmamiTracker('https://stats.example.test/script.js', self::ID))->collectOrigin());
    }

    public function testAnExplicitCollectionUrlWins(): void
    {
        $tracker = new UmamiTracker('https://cloud.umami.is/script.js', self::ID, 'https://collect.example.test/api/send');

        self::assertSame('https://collect.example.test', $tracker->collectOrigin());
    }

    public function testAnInvalidCollectionUrlFallsBackToTheDefault(): void
    {
        $tracker = new UmamiTracker('https://cloud.umami.is/script.js', self::ID, 'http://collect.example.test');

        self::assertSame('https://gateway.umami.is', $tracker->collectOrigin());
    }

    public function testNoCollectionOriginWhenDisabled(): void
    {
        self::assertNull((new UmamiTracker('', ''))->collectOrigin());
    }

    public function testTheOriginKeepsANonDefaultPort(): void
    {
        self::assertSame('https://stats.example.test:8443', (new UmamiTracker('https://stats.example.test:8443/u.js', self::ID))->origin());
    }

    public function testEmptyParametersDisableTheMeasure(): void
    {
        $tracker = new UmamiTracker('', '');

        self::assertFalse($tracker->isEnabled());
        self::assertNull($tracker->scriptUrl());
        self::assertNull($tracker->websiteId());
        self::assertNull($tracker->origin());
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidConfigurations(): iterable
    {
        yield 'http script' => ['http://cloud.umami.is/script.js', self::ID];
        yield 'javascript script' => ['javascript:alert(1)', self::ID];
        yield 'relative script' => ['/script.js', self::ID];
        yield 'credentials in the script URL' => ['https://user:pw@cloud.umami.is/script.js', self::ID];
        yield 'space in the script URL' => ['https://cloud.umami.is/a b.js', self::ID];
        yield 'quote in the script URL' => ['https://cloud.umami.is/a".js', self::ID];
        yield 'no website id' => ['https://cloud.umami.is/script.js', ''];
        yield 'website id not a UUID' => ['https://cloud.umami.is/script.js', 'not-a-uuid'];
        yield 'website id with markup' => ['https://cloud.umami.is/script.js', self::ID . '"><script>'];
        yield 'no script URL' => ['', self::ID];
    }

    #[DataProvider('invalidConfigurations')]
    public function testAnInvalidConfigurationDisablesTheMeasureWithoutError(string $script, string $id): void
    {
        $tracker = new UmamiTracker($script, $id);

        self::assertFalse($tracker->isEnabled());
        self::assertNull($tracker->origin());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function paths(): iterable
    {
        yield 'home' => ['/fr_FR/', true];
        yield 'product' => ['/en_US/products/stylo', true];
        yield 'plugins' => ['/fr_FR/plugins', true];
        yield 'journey list' => ['/fr_FR/guides', true];
        yield 'journey step' => ['/en_US/guides/buyer/2', true];
        yield 'simulator' => ['/demo/punchout-simulator', true];
        yield 'admin' => ['/admin/', false];
        yield 'admin sub page' => ['/admin/orders', false];
        yield 'mails' => ['/demo/mails', false];
        yield 'one mail' => ['/demo/mails/abc', false];
        yield 'order confirmation (token in the URL)' => ['/fr_FR/order/Ab12Cd34', false];
        yield 'quote page (token in the URL)' => ['/en_US/quotes/Ab12Cd34', false];
        yield 'the quote request form' => ['/fr_FR/quote-request', true];
        yield 'health' => ['/healthz', false];
        yield 'a path that only starts like admin' => ['/administration-b2b', false];
        yield 'account' => ['/fr_FR/account/dashboard', false];
        yield 'login' => ['/en_US/login', false];
        yield 'forgotten password token' => ['/fr_FR/forgotten-password/Ab12Cd', false];
        yield 'verification token' => ['/fr_FR/verify/Ab12Cd', false];
        yield 'register after checkout' => ['/fr_FR/register-after-checkout/00042', false];
        yield 'root' => ['/', false];
        yield 'checkout' => ['/fr_FR/checkout/address', false];
        yield 'taxon' => ['/fr_FR/taxons/outillage', true];
    }

    #[DataProvider('paths')]
    public function testOnlyThePublicPagesAreMeasured(string $path, bool $measured): void
    {
        $tracker = new UmamiTracker('https://cloud.umami.is/script.js', self::ID);

        self::assertSame($measured, $tracker->measures($path));
    }

    public function testNothingIsMeasuredWhenDisabled(): void
    {
        self::assertFalse((new UmamiTracker('', ''))->measures('/fr_FR/'));
    }
}
