<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Import\PhotoCropper;
use PHPUnit\Framework\TestCase;

/** US-196: every photo is cut to the 4:3 frame of the product page around the point that matters, and a brand can be blurred. */
final class PhotoCropperTest extends TestCase
{
    public function testALandscapePhotoIsCutToFourByThreeAroundTheFocus(): void
    {
        $image = $this->decode((new PhotoCropper())->crop($this->photo(1200, 600, 'left'), 0.0, 0.5, []));

        self::assertSame([800, 600], [\imagesx($image), \imagesy($image)]);
        self::assertSame($this->red(), $this->colour($image, 10, 300), 'the focus is on the left: the left part is kept');
    }

    public function testAPortraitPhotoIsCutToFourByThreeAroundTheFocus(): void
    {
        $image = $this->decode((new PhotoCropper())->crop($this->photo(600, 1200, 'top'), 0.5, 0.0, []));

        self::assertSame([600, 450], [\imagesx($image), \imagesy($image)]);
        self::assertSame($this->red(), $this->colour($image, 300, 10));
    }

    public function testTheBlurredAreaLosesItsDetailAndTheRestIsUntouched(): void
    {
        $source = $this->photo(800, 600, 'stripes');
        $cropper = new PhotoCropper();
        $plain = $this->decode($cropper->crop($source, 0.5, 0.5, []));
        $blurred = $this->decode($cropper->crop($source, 0.5, 0.5, [[0.0, 0.0, 0.5, 0.5]]));

        self::assertNotSame($this->colour($plain, 100, 100), $this->colour($blurred, 100, 100), 'the stripe is smoothed inside the area');
        self::assertSame($this->colour($plain, 700, 500), $this->colour($blurred, 700, 500), 'outside the area nothing changes');
    }

    private function red(): int
    {
        return 0xFF0000;
    }

    private function photo(int $width, int $height, string $kind): string
    {
        $image = \imagecreatetruecolor($width, $height);
        \imagefill($image, 0, 0, 0x0000FF);
        if ($kind === 'left') {
            \imagefilledrectangle($image, 0, 0, 300, $height, $this->red());
        }
        if ($kind === 'top') {
            \imagefilledrectangle($image, 0, 0, $width, 300, $this->red());
        }
        if ($kind === 'stripes') {
            for ($x = 0; $x < $width; $x += 4) {
                \imagefilledrectangle($image, $x, 0, $x + 1, $height, 0xFFFFFF);
            }
        }
        \ob_start();
        \imagepng($image);

        return (string) \ob_get_clean();
    }

    private function decode(string $bytes): \GdImage
    {
        return \imagecreatefromstring($bytes) ?: throw new \RuntimeException('not an image');
    }

    private function colour(\GdImage $image, int $x, int $y): int
    {
        $rgb = \imagecolorat($image, $x, $y);
        $channels = [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];

        return ($this->quantised($channels[0]) << 16) | ($this->quantised($channels[1]) << 8) | $this->quantised($channels[2]);
    }

    /** JPEG is lossy: the colours are compared by thirds of the scale. */
    private function quantised(int $channel): int
    {
        return $channel > 170 ? 0xFF : ($channel > 85 ? 0x80 : 0);
    }
}
