<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Render;

use App\Demo\Render\RenderedThumbnails;
use PHPUnit\Framework\TestCase;

/**
 * T-TECH-31-04: bin/render-page generates the thumbnails (LiipImagine) of a rendered page and links them as /media/cache/...
 * It used to find the absolute URLs only (http://127.0.0.1/...), so a card with a relative URL kept an empty thumbnail.
 */
final class RenderedThumbnailsTest extends TestCase
{
    public function testItFindsAbsoluteAndRelativeUrlsWithOrWithoutResolve(): void
    {
        $html = <<<'HTML'
            <img src="http://127.0.0.1/media/cache/resolve/b2b_photo/a.jpg">
            <img src="http://127.0.0.1/media/cache/b2b_photo_small/b.jpg">
            <img src="/media/cache/resolve/b2b_photo/c.jpg">
            <img src="/media/cache/b2b_photo_small/d.jpg">
            <img srcset="/media/cache/b2b_photo/e.jpg 300w, /media/cache/b2b_photo/f.jpg 600w">
            HTML;

        self::assertSame(
            ['b2b_photo/a.jpg', 'b2b_photo_small/b.jpg', 'b2b_photo/c.jpg', 'b2b_photo_small/d.jpg', 'b2b_photo/e.jpg', 'b2b_photo/f.jpg'],
            RenderedThumbnails::references($html),
        );
    }

    public function testAReferenceFoundTwiceIsReturnedOnce(): void
    {
        $html = '<img src="/media/cache/resolve/x/a.jpg"><img src="http://127.0.0.1/media/cache/x/a.jpg">';

        self::assertSame(['x/a.jpg'], RenderedThumbnails::references($html));
    }

    public function testItLinksTheThumbnailAsALocalPathWhateverTheOriginalForm(): void
    {
        $html = '<img src="http://127.0.0.1/media/cache/resolve/x/a.jpg"><img src="/media/cache/resolve/x/a.jpg"><img src="/media/cache/x/a.jpg">';

        self::assertSame(
            '<img src="/media/cache/x/a.jpg"><img src="/media/cache/x/a.jpg"><img src="/media/cache/x/a.jpg">',
            RenderedThumbnails::relink($html, 'x/a.jpg'),
        );
    }

    public function testItLeavesOtherUrlsAlone(): void
    {
        $html = '<img src="/build/app/shop/images/home/hero-800.webp"><a href="https://example.test/media/cache/x/a.jpg">';

        self::assertSame([], RenderedThumbnails::references($html));
        self::assertSame($html, RenderedThumbnails::relink($html, 'x/a.jpg'));
    }
}
