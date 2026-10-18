<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

use App\Demo\Images\ImageManifest;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches one CC0 image per product (manual script only, never at runtime). One search per query, not per product:
 * the anonymous Openverse quota is 200 requests a day. Products of the same query take distinct results; when the
 * results run out, the product shares an image of its query (scope "family") or, failing that, of its category.
 * Resumable: a product already in the manifest with its file on disk is never fetched again.
 */
final class ProductImageFetcher
{
    private const MAX_DOWNLOAD_BYTES = 15_000_000;

    private const MAX_ATTEMPTS_PER_PRODUCT = 8;

    /** @var array<string, list<array<string, string>>> rows by query already placed in this run */
    private array $placed = [];

    /** @var array<string, int> */
    private array $familyCursor = [];

    /** @var array<string, list<ImageCandidate>> */
    private array $pools = [];

    /**
     * @param \Closure(float): void                                $pause  waits between two downloads
     * @param \Closure(array<string, string>): array{string,string} $queries the search terms (specific, category) of a product
     */
    public function __construct(
        private readonly OpenverseClient $openverse,
        private readonly ImageProcessor $processor,
        private readonly HttpClientInterface $httpClient,
        private readonly string $userAgent,
        private readonly string $imagesDir,
        private readonly \Closure $pause,
        private readonly \Closure $queries,
    ) {
    }

    /**
     * @param list<array<string, string>> $products rows with at least sku, name_en, taxon
     *
     * @return array{own: int, family: int, missing: list<string>}
     */
    public function fetch(array $products, ImageManifest $manifest): array
    {
        $report = ['own' => 0, 'family' => 0, 'missing' => []];
        foreach ($products as $product) {
            if ($this->isDone($product['sku'], $manifest)) {
                continue;
            }
            [$specific, $category] = ($this->queries)($product);
            $row = $this->ownImage($product['sku'], $specific, $manifest) ?? $this->familyImage($product['sku'], $specific, $category, $manifest);
            if ($row === null) {
                $report['missing'][] = $product['sku'];

                continue;
            }
            $manifest->add($row);
            ++$report[$row['scope'] === 'own' ? 'own' : 'family'];
            if ($row['scope'] === 'own') {
                $this->placed[$specific][] = $row;
            }
        }

        return $report;
    }

    private function isDone(string $sku, ImageManifest $manifest): bool
    {
        $file = $manifest->fileFor($sku);

        return $file !== null && \is_file($this->imagesDir . '/' . $file);
    }

    /** @return array<string, string>|null */
    private function ownImage(string $sku, string $query, ImageManifest $manifest): ?array
    {
        $attempts = 0;
        foreach ($this->pool($query) as $candidate) {
            if (\in_array($candidate->fileName(), $manifest->files(), true)) {
                continue;
            }
            if (++$attempts > self::MAX_ATTEMPTS_PER_PRODUCT) {
                break;
            }
            if ($this->store($candidate)) {
                return $this->row($sku, $candidate, 'own');
            }
        }

        return null;
    }

    /** @return array<string, string>|null */
    private function familyImage(string $sku, string $query, string $category, ImageManifest $manifest): ?array
    {
        $key = isset($this->placed[$query]) ? $query : $category;
        if (!isset($this->placed[$key])) {
            $fallback = $this->ownImage($sku, $category, $manifest);
            if ($fallback === null) {
                return null;
            }
            $this->placed[$category][] = $fallback;

            return ['scope' => 'family'] + $fallback;
        }
        $siblings = $this->placed[$key];
        $shared = $siblings[($this->familyCursor[$key] ?? 0) % \count($siblings)];
        $this->familyCursor[$key] = ($this->familyCursor[$key] ?? 0) + 1;

        return ['sku' => $sku, 'scope' => 'family'] + $shared;
    }

    /** @return list<ImageCandidate> */
    private function pool(string $query): array
    {
        return $this->pools[$query] ??= $this->openverse->search($query);
    }

    private function store(ImageCandidate $candidate): bool
    {
        try {
            $response = $this->httpClient->request('GET', $candidate->url, ['headers' => ['User-Agent' => $this->userAgent], 'max_duration' => 60]);
            $bytes = 200 === $response->getStatusCode() ? $response->getContent() : null;
        } catch (ExceptionInterface) {
            $bytes = null;
        } finally {
            ($this->pause)(1.2);
        }
        $webp = $bytes !== null && \strlen($bytes) <= self::MAX_DOWNLOAD_BYTES ? $this->processor->toWebp($bytes) : null;
        if ($webp === null) {
            return false;
        }
        \file_put_contents($this->imagesDir . '/' . $candidate->fileName(), $webp);

        return true;
    }

    /** @return array<string, string> */
    private function row(string $sku, ImageCandidate $candidate, string $scope): array
    {
        return [
            'sku' => $sku,
            'file' => $candidate->fileName(),
            'source' => $candidate->source !== '' ? $candidate->source : 'openverse',
            'creator' => $candidate->creator !== '' ? $candidate->creator : 'not specified',
            'title' => $candidate->title !== '' ? $candidate->title : 'untitled',
            'origin_url' => $candidate->originUrl,
            'license' => $candidate->license,
            'license_url' => $candidate->licenseUrl,
            'fetched_at' => \date('Y-m-d'),
            'scope' => $scope,
            'status' => ImageManifest::STATUS_UNREVIEWED,
        ];
    }
}
