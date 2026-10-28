<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\ImageProcessor;
use App\Demo\Images\Fetch\OpenverseClient;
use App\Demo\Images\Fetch\ProductImageFetcher;
use App\Demo\Images\ImageManifest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * US-062: the minor points of the image script. Answers are simulated (no network call): a 429 is waited for and retried
 * within a bound, an image that is too heavy (declared length, streamed body without length, or pixels) is refused and
 * reported, and a product already in the manifest keeps its image and its curated fields.
 */
final class ProductImageFetcherLimitsTest extends TestCase
{
    private const ID = 'aaaa1111-0000-0000-0000-000000000000';

    private string $dir = '';

    /** @var list<string> */
    private array $downloads = [];

    /** @var list<float> */
    private array $pauses = [];

    /** @var list<MockResponse> answers to the next downloads, in order; the last one repeats */
    private array $answers = [];

    protected function setUp(): void
    {
        $this->dir = \sys_get_temp_dir() . '/fetcher_limits_' . \uniqid('', true);
        \mkdir($this->dir . '/images', 0o700, true);
        \mkdir($this->dir . '/cache', 0o700, true);
    }

    protected function tearDown(): void
    {
        foreach (\glob($this->dir . '/{images,cache}/*', \GLOB_BRACE) ?: [] as $file) {
            \unlink($file);
        }
        @\unlink($this->dir . '/manifest.csv');
        @\rmdir($this->dir . '/images');
        @\rmdir($this->dir . '/cache');
        @\rmdir($this->dir);
    }

    public function testAnImageWithinTheLimitsIsStoredAsBefore(): void
    {
        $this->answers = [new MockResponse($this->png(), ['response_headers' => ['content-length: ' . \strlen($this->png())]])];

        $report = $this->fetcher()->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(1, $report['own']);
        self::assertSame([], $report['rejected']);
        self::assertFileExists($this->dir . '/images/aaaa11110000.webp');
    }

    public function testATooManyRequestsAnswerIsWaitedForThenRetried(): void
    {
        $this->answers = [new MockResponse('', ['http_code' => 429, 'response_headers' => ['retry-after: 2']]), new MockResponse($this->png())];

        $report = $this->fetcher()->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(1, $report['own']);
        self::assertCount(2, $this->downloads);
        self::assertContains(2.0, $this->pauses, 'the delay asked by the source is respected');
    }

    public function testTheWaitIsBoundedAndTheRetriesToo(): void
    {
        $this->answers = [new MockResponse('', ['http_code' => 429, 'response_headers' => ['retry-after: 86400']])];

        $report = $this->fetcher(['category fallback' => []])->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(['P-1'], $report['missing']);
        self::assertLessThanOrEqual(3, \count($this->downloads), 'one try and two retries at most');
        self::assertLessThanOrEqual(60.0, \max([0.0, ...$this->pauses]), 'never waits a whole day');
    }

    public function testADeclaredLengthOverTheLimitIsRefusedAndReported(): void
    {
        $this->answers = [new MockResponse(\str_repeat('x', 20_000), ['response_headers' => ['content-length: 20000']])];

        $report = $this->fetcher(['category fallback' => []], maxBytes: 10_000)->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(['P-1'], $report['missing']);
        self::assertCount(1, $report['rejected']);
        self::assertStringContainsString('too large', $report['rejected'][0]);
        self::assertFileDoesNotExist($this->dir . '/images/aaaa11110000.webp');
    }

    public function testABodyWithoutLengthIsReadUpToTheLimitOnly(): void
    {
        $chunks = static function (): \Generator {
            for ($i = 0; $i < 5; ++$i) {
                yield \str_repeat('x', 4_000);
            }
        };
        $this->answers = [new MockResponse($chunks())];

        $report = $this->fetcher(['category fallback' => []], maxBytes: 10_000)->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(['P-1'], $report['missing']);
        self::assertCount(1, $report['rejected']);
        self::assertStringContainsString('too large', $report['rejected'][0]);
    }

    public function testAnImageOverThePixelLimitIsRefusedAndReported(): void
    {
        $this->answers = [new MockResponse($this->png())];

        $report = $this->fetcher(['category fallback' => []], maxPixels: 1_000_000)->fetch([$this->product('P-1')], $this->manifest());

        self::assertSame(['P-1'], $report['missing']);
        self::assertCount(1, $report['rejected']);
        self::assertStringContainsString('pixels', $report['rejected'][0]);
    }

    public function testAProductAlreadyInTheManifestKeepsItsImageAndItsCuratedFields(): void
    {
        \file_put_contents($this->dir . '/images/kept00000000.webp', 'webp');
        \file_put_contents($this->dir . '/manifest.csv', "sku,file,source,creator,title,origin_url,license,license_url,fetched_at,scope,status,focal\nP-1,kept00000000.webp,flickr,Jane,Photo,https://example.test/p,cc0,https://creativecommons.org/publicdomain/zero/1.0/,2026-01-01,own,validated,40 60\n");
        $this->answers = [new MockResponse($this->png())];

        $manifest = $this->manifest();
        $this->fetcher()->fetch([$this->product('P-1'), $this->product('P-2')], $manifest);

        $row = $this->manifest()->row('P-1');
        self::assertSame('kept00000000.webp', $row['file'] ?? null);
        self::assertSame('validated', $row['status'] ?? null);
        self::assertSame('40 60', $row['focal'] ?? null);
        self::assertNotNull($this->manifest()->row('P-2'));
        self::assertSame('webp', \file_get_contents($this->dir . '/images/kept00000000.webp'));
    }

    /** @param array<string, list<array<string, mixed>>> $hitsByQuery */
    private function fetcher(array $hitsByQuery = [], int $maxBytes = 15_000_000, int $maxPixels = 40_000_000): ProductImageFetcher
    {
        $client = new MockHttpClient(function (string $method, string $url) use ($hitsByQuery): ResponseInterface {
            if (\str_starts_with($url, 'https://api.openverse.org/v1/images/')) {
                \parse_str((string) \parse_url($url, \PHP_URL_QUERY), $query);
                $term = \is_string($query['q'] ?? null) ? $query['q'] : '';
                $results = $hitsByQuery[$term] ?? [$this->hit()];

                return new MockResponse((string) \json_encode(['result_count' => \count($results), 'results' => $results]));
            }
            $this->downloads[] = $url;
            $position = \count($this->downloads) - 1;

            return $this->answers[\min($position, \count($this->answers) - 1)];
        });

        return new ProductImageFetcher(
            new OpenverseClient($client, 'test-agent/1.0', $this->dir . '/cache'),
            new ImageProcessor(),
            $client,
            'test-agent/1.0',
            $this->dir . '/images',
            function (float $seconds): void {
                $this->pauses[] = $seconds;
            },
            static fn (array $product): array => ['specific', 'category fallback'],
            maxDownloadBytes: $maxBytes,
            maxPixels: $maxPixels,
        );
    }

    private function manifest(): ImageManifest
    {
        return ImageManifest::load($this->dir . '/manifest.csv');
    }

    /** @return array{sku: string, name_en: string, taxon: string} */
    private function product(string $sku): array
    {
        return ['sku' => $sku, 'name_en' => 'Thing ' . $sku, 'taxon' => 'cat'];
    }

    /** @return array<string, mixed> */
    private function hit(): array
    {
        return [
            'id' => self::ID,
            'title' => 'A specific fallback photo',
            'creator' => 'Jane Doe',
            'license' => 'cc0',
            'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'url' => 'https://img.test/' . self::ID . '.png',
            'foreign_landing_url' => 'https://example.test/photo/' . self::ID,
            'source' => 'flickr',
            'filetype' => 'png',
            'mature' => false,
        ];
    }

    private function png(): string
    {
        $image = \imagecreatetruecolor(1200, 900);
        \imagefilledrectangle($image, 0, 0, 1199, 899, (int) \imagecolorallocate($image, 200, 120, 40));
        \ob_start();
        \imagepng($image);

        return (string) \ob_get_clean();
    }
}
