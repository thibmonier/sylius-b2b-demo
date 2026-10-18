<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageManifestAuditor;
use PHPUnit\Framework\TestCase;

/** US-092: an image enters the demo only when it is free of rights AND a person has judged it fit for its product. */
final class ImageManifestAuditorTest extends TestCase
{
    public function testAReviewedFreeImageAndAGeneratedCategoryVisualAreAccepted(): void
    {
        self::assertSame([], (new ImageManifestAuditor())->violations([$this->row(), $this->fallback()]));
    }

    public function testANonFreeLicenceIsRefusedAndNamed(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-9', 'license' => 'by', 'license_url' => 'https://creativecommons.org/licenses/by/4.0/'])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-9', $violations[0]);
        self::assertStringContainsString('licence', $violations[0]);
    }

    public function testALicenceUrlThatIsNotHttpsIsRefused(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-7', 'license_url' => 'http://creativecommons.org/publicdomain/zero/1.0/'])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-7: license_url must be an https URL', $violations[0]);
    }

    public function testAnImageNobodyReviewedIsRefused(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-3', 'status' => 'unreviewed'])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-3', $violations[0]);
        self::assertStringContainsString('status', $violations[0]);
    }

    public function testAMissingFieldIsRefusedAndNamed(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-4', 'creator' => ''])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-4: creator is empty', $violations[0]);
    }

    public function testAFallbackMustBeAGeneratedCategoryVisualNotAPhoto(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->fallback(['sku' => 'P-5', 'source' => 'flickr'])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-5', $violations[0]);
    }

    public function testAPhotoCannotBeStoredAsACategoryVisual(): void
    {
        $violations = (new ImageManifestAuditor())->violations([$this->row(['sku' => 'P-6', 'scope' => 'category'])]);

        self::assertCount(1, $violations);
        self::assertStringContainsString('P-6', $violations[0]);
    }

    /**
     * @param array<string, string> $override
     *
     * @return array<string, string>
     */
    private function row(array $override = []): array
    {
        return $override + [
            'sku' => 'P-1', 'file' => 'a.webp', 'source' => 'flickr', 'creator' => 'Jane', 'title' => 'Hard Hat',
            'origin_url' => 'https://example.test/1', 'license' => 'cc0', 'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'fetched_at' => '2026-10-17', 'scope' => 'family', 'status' => 'validated',
        ];
    }

    /**
     * @param array<string, string> $override
     *
     * @return array<string, string>
     */
    private function fallback(array $override = []): array
    {
        return $override + $this->row(['sku' => 'P-2', 'file' => 'b.webp', 'source' => 'generated', 'scope' => 'category', 'status' => 'fallback']);
    }
}
