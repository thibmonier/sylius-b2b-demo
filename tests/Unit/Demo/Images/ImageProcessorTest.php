<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\ImageProcessor;
use PHPUnit\Framework\TestCase;

final class ImageProcessorTest extends TestCase
{
    public function testALargeNoisyImageEndsUnderTheByteBudgetAndTheSizeLimit(): void
    {
        $image = \imagecreatetruecolor(3000, 2000);
        \mt_srand(7);
        for ($i = 0; $i < 40000; ++$i) {
            \imagefilledrectangle($image, $x = \mt_rand(0, 2990), $y = \mt_rand(0, 1990), $x + 10, $y + 10, (int) \imagecolorallocate($image, \mt_rand(0, 255), \mt_rand(0, 255), \mt_rand(0, 255)));
        }
        \ob_start();
        \imagejpeg($image, null, 95);
        $source = (string) \ob_get_clean();

        $webp = (new ImageProcessor())->toWebp($source);

        self::assertNotNull($webp);
        self::assertLessThanOrEqual(ImageProcessor::MAX_BYTES, \strlen($webp));
        $size = \getimagesizefromstring($webp);
        self::assertIsArray($size);
        self::assertLessThanOrEqual(ImageProcessor::MAX_SIDE, \max($size[0], $size[1]));
    }

    public function testBytesThatAreNotAnImageAreRefused(): void
    {
        self::assertNull((new ImageProcessor())->toWebp('<html>not an image</html>'));
    }

    public function testATinyImageIsRefused(): void
    {
        $image = \imagecreatetruecolor(100, 100);
        \ob_start();
        \imagepng($image);

        self::assertNull((new ImageProcessor())->toWebp((string) \ob_get_clean()));
    }
}
