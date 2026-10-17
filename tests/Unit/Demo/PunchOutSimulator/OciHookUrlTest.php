<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\OciHookUrl;
use App\Demo\PunchOutSimulator\SimulatorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Security\ReturnUrlValidator;

/** US-053: the HOOK_URL is one of the return domains of the buyer (deny by default), and carries the signed state. */
final class OciHookUrlTest extends TestCase
{
    public function testAnAllowedUrlCarriesTheState(): void
    {
        $url = $this->hook()->withState('https://erp.acme.example/demo/punchout-simulator/oci/return', 'signed.state', $this->buyer());

        self::assertSame('https://erp.acme.example/demo/punchout-simulator/oci/return?state=signed.state', $url);
    }

    public function testTheStateIsAddedToAnExistingQuery(): void
    {
        $url = $this->hook()->withState('https://erp.acme.example/return?lang=en', 'a.b', $this->buyer());

        self::assertSame('https://erp.acme.example/return?lang=en&state=a.b', $url);
    }

    #[DataProvider('refusedUrls')]
    public function testAnUrlOutsideTheAllowedDomainsIsRefused(string $url): void
    {
        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('hook_url_not_allowed');

        $this->hook()->withState($url, 'a.b', $this->buyer());
    }

    /** @return iterable<string, array{string}> */
    public static function refusedUrls(): iterable
    {
        yield 'foreign domain' => ['https://not-allowed.example/return'];
        yield 'plain http' => ['http://erp.acme.example/return'];
        yield 'credentials in the url' => ['https://erp.acme.example@evil.example/return'];
        yield 'loopback' => ['https://127.0.0.1/return'];
        yield 'empty' => [''];
        yield 'not a url' => ['javascript:alert(1)'];
    }

    private function hook(): OciHookUrl
    {
        return new OciHookUrl(new ReturnUrlValidator());
    }

    private function buyer(): Buyer
    {
        $buyer = new Buyer();
        $buyer->setReturnDomains(['erp.acme.example']);

        return $buyer;
    }
}
