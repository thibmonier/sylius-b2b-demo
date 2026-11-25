<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Refresh;

use App\Demo\Proof\Refresh\HttpPackagistApi;
use App\Demo\Proof\Refresh\SourceUnavailable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * The refresh reads the Composer v2 metadata of a package (repo.packagist.org/p2), the source `composer install` resolves
 * from, updated within a minute of a release. The packagist.org/packages/*.json API sits behind a CDN cache of 12 hours
 * (s-maxage=43200): v0.2.0, v0.2.1 and v0.3.0 of the platform health plugin each waited half a day before the proof saw
 * them. The p2 file is minified: every version after the first lists only what changed, "__unset" removes a key.
 */
final class HttpPackagistApiTest extends TestCase
{
    private const NAME = 'tmonier/sylius-platform-health-plugin';

    public function testTheComposerMetadataIsRead(): void
    {
        $requested = null;
        $client = new MockHttpClient(static function (string $method, string $url) use (&$requested): MockResponse {
            $requested = $method . ' ' . $url;

            return self::json(['minified' => 'composer/2.0', 'packages' => [self::NAME => [['version' => 'v0.3.0']]]]);
        });

        (new HttpPackagistApi($client))->package(self::NAME);

        self::assertSame('GET https://repo.packagist.org/p2/' . self::NAME . '.json', $requested);
    }

    public function testTheMinifiedVersionsAreExpandedAndKeyedByVersion(): void
    {
        $client = new MockHttpClient(self::json(['minified' => 'composer/2.0', 'packages' => [self::NAME => [
            ['version' => 'v0.3.0', 'require' => ['php' => '^8.2', 'sylius/sylius' => '^2.2.9 || ^2.3'], 'suggest' => ['foo/bar' => 'x']],
            ['version' => 'v0.2.1', 'suggest' => '__unset'],
            ['version' => 'v0.1.0', 'require' => ['php' => '^8.2', 'sylius/sylius' => '^2.2']],
        ]]]));

        $package = (new HttpPackagistApi($client))->package(self::NAME);

        self::assertIsArray($package);
        self::assertSame(['v0.3.0', 'v0.2.1', 'v0.1.0'], array_keys($package['versions']));
        self::assertSame('^2.2.9 || ^2.3', $package['versions']['v0.2.1']['require']['sylius/sylius'], 'inherited from the version before');
        self::assertArrayNotHasKey('suggest', $package['versions']['v0.2.1'], '"__unset" removes the key');
        self::assertSame('^2.2', $package['versions']['v0.1.0']['require']['sylius/sylius']);
    }

    public function testAnUnknownPackageIsNull(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 404]));

        self::assertNull((new HttpPackagistApi($client))->package(self::NAME));
    }

    public function testAServerErrorIsASourceFailure(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 503]));

        $this->expectException(SourceUnavailable::class);
        (new HttpPackagistApi($client))->package(self::NAME);
    }

    public function testAnAnswerWithoutThePackageIsASourceFailure(): void
    {
        $client = new MockHttpClient(self::json(['minified' => 'composer/2.0', 'packages' => []]));

        $this->expectException(SourceUnavailable::class);
        (new HttpPackagistApi($client))->package(self::NAME);
    }

    /** @param array<string, mixed> $body */
    private static function json(array $body): MockResponse
    {
        return new MockResponse(json_encode($body, \JSON_THROW_ON_ERROR), ['response_headers' => ['content-type' => 'application/json']]);
    }
}
