<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageCredits;
use App\Demo\Images\ImageManifest;
use PHPUnit\Framework\TestCase;

/** US-061: the credits page lists every image of the manifest once, whatever the number of products that show it. */
final class ImageCreditsTest extends TestCase
{
    private string $path = '';

    protected function setUp(): void
    {
        $this->path = \sys_get_temp_dir() . '/credits_test_' . \uniqid('', true) . '.csv';
    }

    protected function tearDown(): void
    {
        @\unlink($this->path);
    }

    public function testAnImageSharedByTwoProductsIsCreditedOnceWithItsProductCount(): void
    {
        $this->manifest([
            $this->row('P-1', 'a.webp', 'Jane', 'Hard Hat'),
            $this->row('P-2', 'a.webp', 'Jane', 'Hard Hat'),
            $this->row('P-3', 'b.webp', 'John', 'Wrench'),
        ]);

        $credits = (new ImageCredits($this->path))->all();

        self::assertCount(2, $credits);
        self::assertSame(['a.webp', 'Jane', 'Hard Hat', 2], [$credits[0]->file, $credits[0]->creator, $credits[0]->title, $credits[0]->products]);
        self::assertSame(1, $credits[1]->products);
    }

    public function testAGeneratedVisualIsFlaggedAsMadeForTheDemo(): void
    {
        $this->manifest([$this->row('P-1', 'c.webp', 'Thibaut Monier', 'Neutral visual', 'generated')]);

        $credit = (new ImageCredits($this->path))->all()[0];

        self::assertTrue($credit->generated);
    }

    public function testAnEmptyManifestGivesNoCredit(): void
    {
        self::assertSame([], (new ImageCredits($this->path))->all());
    }

    /** @param list<array<string, string>> $rows */
    private function manifest(array $rows): void
    {
        $handle = \fopen($this->path, 'w');
        \assert(false !== $handle);
        \fputcsv($handle, ImageManifest::FIELDS, ',', '"', '');
        foreach ($rows as $row) {
            \fputcsv($handle, \array_map(static fn (string $field): string => $row[$field], ImageManifest::FIELDS), ',', '"', '');
        }
        \fclose($handle);
    }

    /** @return array<string, string> */
    private function row(string $sku, string $file, string $creator, string $title, string $source = 'flickr'): array
    {
        return [
            'sku' => $sku, 'file' => $file, 'source' => $source, 'creator' => $creator, 'title' => $title,
            'origin_url' => 'https://example.test/' . $file, 'license' => 'cc0', 'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'fetched_at' => '2026-10-17', 'scope' => 'family', 'status' => 'validated',
        ];
    }
}
