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

final class ProductImageFetcherTest extends TestCase
{
    private string $dir = '';

    /** @var list<string> */
    private array $requested = [];

    protected function setUp(): void
    {
        $this->dir = \sys_get_temp_dir() . '/fetcher_test_' . \uniqid('', true);
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

    public function testAResultUnderAnotherLicenceThanCc0IsRejectedEvenIfTheApiReturnsIt(): void
    {
        $manifest = $this->manifest();
        $fetcher = $this->fetcher([$this->hit('aaaa1111-0000-0000-0000-000000000000', 'by'), $this->hit('bbbb2222-0000-0000-0000-000000000000', 'cc0')]);

        $fetcher->fetch([$this->product('P-1')], $manifest);

        $row = $manifest->row('P-1');
        self::assertNotNull($row);
        self::assertSame('cc0', $row['license']);
        self::assertSame('bbbb22220000.webp', $row['file']);
        self::assertNotContains('https://img.test/aaaa1111-0000-0000-0000-000000000000.png', $this->requested, 'A non-CC0 image is never even downloaded.');
    }

    public function testTheManifestTracesTheSourceTheAuthorAndTheLicenceOfEveryImage(): void
    {
        $manifest = $this->manifest();

        $this->fetcher([$this->hit('cccc3333-0000-0000-0000-000000000000', 'cc0')])->fetch([$this->product('P-1')], $manifest);

        $row = $this->manifest()->row('P-1');
        self::assertNotNull($row);
        foreach (['sku', 'file', 'source', 'creator', 'title', 'origin_url', 'license', 'license_url', 'fetched_at', 'scope'] as $field) {
            self::assertNotSame('', $row[$field], $field . ' must be filled');
        }
        self::assertSame('own', $row['scope']);
        self::assertFileExists($this->dir . '/images/' . $row['file']);
        self::assertLessThanOrEqual(ImageProcessor::MAX_BYTES, \filesize($this->dir . '/images/' . $row['file']));
    }

    public function testARelaunchOnlyFetchesTheMissingImages(): void
    {
        $hits = [$this->hit('dddd4444-0000-0000-0000-000000000000', 'cc0'), $this->hit('eeee5555-0000-0000-0000-000000000000', 'cc0')];
        $this->fetcher($hits)->fetch([$this->product('P-1')], $this->manifest());
        $downloads = \count($this->downloads());

        $this->requested = [];
        $this->fetcher($hits)->fetch([$this->product('P-1'), $this->product('P-2')], $this->manifest());

        self::assertSame(1, $downloads);
        self::assertCount(1, $this->downloads(), 'Only the missing product is downloaded again.');
        self::assertSame(['P-1', 'P-2'], $this->manifest()->skus(), 'No duplicate row.');
    }

    public function testAFamilyWithFewerResultsThanProductsSharesAnImageAndSaysSo(): void
    {
        $manifest = $this->manifest();

        $this->fetcher([$this->hit('ffff6666-0000-0000-0000-000000000000', 'cc0')])->fetch([$this->product('P-1'), $this->product('P-2')], $manifest);

        self::assertSame('own', $manifest->row('P-1')['scope'] ?? null);
        self::assertSame('family', $manifest->row('P-2')['scope'] ?? null);
        self::assertSame($manifest->row('P-1')['file'] ?? null, $manifest->row('P-2')['file'] ?? null);
    }

    public function testWithoutAnyCc0ResultTheProductFallsBackOnTheImageOfItsCategory(): void
    {
        $manifest = $this->manifest();
        $fetcher = $this->fetcher([], ['category fallback' => [$this->hit('99991111-0000-0000-0000-000000000000', 'cc0')]]);

        $fetcher->fetch([$this->product('P-1')], $manifest);

        self::assertSame('family', $manifest->row('P-1')['scope'] ?? null);
    }

    /**
     * @param list<array<string, mixed>>               $hits
     * @param array<string, list<array<string, mixed>>> $hitsByQuery
     */
    private function fetcher(array $hits, array $hitsByQuery = []): ProductImageFetcher
    {
        $client = new MockHttpClient(function (string $method, string $url) use ($hits, $hitsByQuery): ResponseInterface {
            $this->requested[] = $url;
            if (\str_starts_with($url, 'https://api.openverse.org/v1/images/')) {
                \parse_str((string) \parse_url($url, \PHP_URL_QUERY), $query);
                $term = \is_string($query['q'] ?? null) ? $query['q'] : '';
                $results = $hitsByQuery[$term] ?? $hits;

                return new MockResponse((string) \json_encode(['result_count' => \count($results), 'results' => $results]));
            }

            return new MockResponse($this->png(), ['response_headers' => ['content-type: image/png']]);
        });

        return new ProductImageFetcher(
            new OpenverseClient($client, 'test-agent/1.0', $this->dir . '/cache'),
            new ImageProcessor(),
            $client,
            'test-agent/1.0',
            $this->dir . '/images',
            static function (): void {},
            static fn (array $product): array => [$product['taxon'] === 'cat' && $hitsByQuery !== [] ? 'no result' : 'specific', 'category fallback'],
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
    private function hit(string $id, string $license): array
    {
        return [
            'id' => $id,
            'title' => 'A specific fallback photo',
            'creator' => 'Jane Doe',
            'license' => $license,
            'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'url' => 'https://img.test/' . $id . '.png',
            'foreign_landing_url' => 'https://example.test/photo/' . $id,
            'source' => 'flickr',
            'filetype' => 'png',
            'mature' => false,
        ];
    }

    /** @return list<string> */
    private function downloads(): array
    {
        return \array_values(\array_filter($this->requested, static fn (string $url): bool => \str_starts_with($url, 'https://img.test/')));
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
