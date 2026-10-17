<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageManifest;
use PHPUnit\Framework\TestCase;

final class ImageManifestTest extends TestCase
{
    private string $path = '';

    protected function setUp(): void
    {
        $this->path = \sys_get_temp_dir() . '/manifest_test_' . \uniqid('', true) . '.csv';
    }

    protected function tearDown(): void
    {
        @\unlink($this->path);
    }

    public function testAMalformedRowStopsTheLoadInsteadOfBeingIgnored(): void
    {
        \file_put_contents($this->path, "sku,file,source\nP-1,a.webp,flickr\nP-2,b.webp\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('line 3');

        ImageManifest::load($this->path);
    }

    public function testAMissingFileIsAnEmptyManifestAndBlankLinesAreIgnored(): void
    {
        self::assertSame([], ImageManifest::load($this->path)->skus());

        \file_put_contents($this->path, "sku,file\nP-1,a.webp\n\n");
        self::assertSame(['P-1'], ImageManifest::load($this->path)->skus());
    }
}
