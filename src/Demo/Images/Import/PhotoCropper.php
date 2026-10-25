<?php

declare(strict_types=1);

namespace App\Demo\Images\Import;

/** US-196: cuts a photo to the 4:3 frame of the product page around the point that matters, and blurs what must not be read. */
final class PhotoCropper
{
    private const MAX_WIDTH = 1600;

    private const JPEG_QUALITY = 92;

    /**
     * @param list<array{float, float, float, float}> $blurs areas to blur, as fractions of the original photo
     *
     * @return string JPEG bytes
     */
    public function crop(string $bytes, float $focusX, float $focusY, array $blurs): string
    {
        $source = \imagecreatefromstring($bytes) ?: throw new \RuntimeException('The photo is not a readable image.');
        $width = \imagesx($source);
        $height = \imagesy($source);
        $cropWidth = $width * 3 >= $height * 4 ? (int) \round($height * 4 / 3) : $width;
        $cropHeight = $width * 3 >= $height * 4 ? $height : (int) \round($width * 3 / 4);
        $left = (int) \max(0, \min($width - $cropWidth, \round($focusX * $width - $cropWidth / 2)));
        $top = (int) \max(0, \min($height - $cropHeight, \round($focusY * $height - $cropHeight / 2)));

        $image = \imagecrop($source, ['x' => $left, 'y' => $top, 'width' => $cropWidth, 'height' => $cropHeight]) ?: throw new \RuntimeException('The photo cannot be cut.');
        $scale = \min(1.0, self::MAX_WIDTH / $cropWidth);
        if ($scale < 1.0) {
            $image = $this->reduced($image, \max(1, (int) \round($cropWidth * $scale)), \max(1, (int) \round($cropHeight * $scale)));
        }
        foreach ($blurs as [$x, $y, $areaWidth, $areaHeight]) {
            $this->blur($image, (int) \round(($x * $width - $left) * $scale), (int) \round(($y * $height - $top) * $scale), (int) \round($areaWidth * $width * $scale), (int) \round($areaHeight * $height * $scale));
        }

        \ob_start();
        \imagejpeg($image, null, self::JPEG_QUALITY);

        return (string) \ob_get_clean();
    }

    /**
     * @param int<1, max> $width
     * @param int<1, max> $height
     */
    private function reduced(\GdImage $image, int $width, int $height): \GdImage
    {
        $target = \imagecreatetruecolor($width, $height);
        \imagecopyresampled($target, $image, 0, 0, 0, 0, $width, $height, \imagesx($image), \imagesy($image));

        return $target;
    }

    private function blur(\GdImage $image, int $x, int $y, int $width, int $height): void
    {
        $x = \max(0, $x);
        $y = \max(0, $y);
        $width = \min($width, \imagesx($image) - $x);
        $height = \min($height, \imagesy($image) - $y);
        if ($width < 1 || $height < 1) {
            return;
        }
        $area = \imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if ($area === false) {
            return;
        }
        \imagefilter($area, \IMG_FILTER_PIXELATE, \max(8, (int) ($width / 8)), true);
        \imagecopy($image, $area, $x, $y, 0, 0, $width, $height);
    }
}
