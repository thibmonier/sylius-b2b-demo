<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use App\Demo\Images\PhotoFraming;
use PHPUnit\Framework\TestCase;

/**
 * US-197 (tranche 1): the point focal of a photo, kept in the manifest, tells the card (3:4) and the product page (4:3) which part of
 * the 4:3 file to keep. A photo is never retouched, and one without a point focal is framed as before (centered).
 */
final class PhotoFramingTest extends TestCase
{
    private string $path = '';

    protected function setUp(): void
    {
        $this->path = \sys_get_temp_dir() . '/framing_test_' . \uniqid('', true) . '.csv';
    }

    protected function tearDown(): void
    {
        @\unlink($this->path);
    }

    public function testAProductWithAFocalPointIsFramedOnIt(): void
    {
        $this->manifest(['P-1' => '100 50', 'P-2' => '12.5 40']);

        $framing = new PhotoFraming($this->path);

        self::assertSame('100% 50%', $framing->objectPosition('P-1'));
        self::assertSame('12.5% 40%', $framing->objectPosition('P-2'));
    }

    public function testAProductWithoutAFocalPointKeepsTheDefaultFraming(): void
    {
        $this->manifest(['P-1' => '']);

        $framing = new PhotoFraming($this->path);

        self::assertNull($framing->objectPosition('P-1'));
        self::assertNull($framing->objectPosition('P-UNKNOWN'));
        self::assertNull($framing->objectPosition(null));
    }

    public function testAManifestWithoutTheFocalColumnStillLoads(): void
    {
        \file_put_contents($this->path, "sku,file\nP-1,a.webp\n");

        self::assertNull((new PhotoFraming($this->path))->objectPosition('P-1'));
        self::assertNull(ImageManifest::load($this->path)->focalFor('P-1'));
    }

    public function testAMissingManifestFileFramesNothingInsteadOfFailing(): void
    {
        self::assertNull((new PhotoFraming($this->path))->objectPosition('P-1'));
    }

    public function testTheFocalPointIsReadAsTwoPercentages(): void
    {
        $this->manifest(['P-1' => '72 55']);

        self::assertSame([72.0, 55.0], ImageManifest::load($this->path)->focalFor('P-1'));
    }

    public function testAFocalPointOutsideTheFrameIsRefusedAndNamedByTheAudit(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-7', 'focal' => '140 50']), $this->row(['sku' => 'P-8', 'focal' => 'left'])]);

        self::assertCount(2, $violations);
        self::assertStringContainsString('P-7: focal', $violations[0]);
        self::assertStringContainsString('P-8: focal', $violations[1]);
    }

    public function testAnEmptyOrValidFocalPointPassesTheAudit(): void
    {
        self::assertSame([], (new ImageManifestAuditor())->violations([$this->row(['focal' => '']), $this->row(['sku' => 'P-2', 'focal' => '0 100']), $this->row(['sku' => 'P-3', 'focal' => '37.5 50'])]));
    }

    public function testTheFocalPointsOfTheDemoCatalogueAreAllValid(): void
    {
        $manifest = ImageManifest::load(\dirname(__DIR__, 4) . '/fixtures/images/manifest.csv');

        self::assertSame([], (new ImageManifestAuditor())->violations($manifest->rows()));
        $framed = \array_filter($manifest->skus(), static fn (string $sku): bool => $manifest->focalFor($sku) !== null);
        self::assertNotEmpty($framed, 'The photos cut by the portrait card carry a point focal.');
    }

    /** @param array<string, string> $focals focal point by SKU */
    private function manifest(array $focals): void
    {
        $lines = ['sku,file,focal'];
        foreach ($focals as $sku => $focal) {
            $lines[] = \sprintf('%s,%s.webp,"%s"', $sku, \strtolower($sku), $focal);
        }
        \file_put_contents($this->path, \implode("\n", $lines) . "\n");
    }

    /**
     * @param array<string, string> $override
     *
     * @return array<string, string>
     */
    private function row(array $override = []): array
    {
        return $override + [
            'sku' => 'P-1', 'file' => 'a.webp', 'source' => 'pexels', 'creator' => 'Jane', 'title' => 'A photo', 'origin_url' => 'https://www.pexels.com/photo/1/',
            'license' => 'pexels', 'license_url' => 'https://www.pexels.com/license/', 'fetched_at' => '2026-10-25', 'scope' => 'own', 'status' => 'validated',
        ];
    }
}
