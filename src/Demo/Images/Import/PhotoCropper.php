<?php

declare(strict_types=1);

namespace App\Demo\Images\Import;

/** US-196: cuts a photo to the 4:3 frame of the product page around the point that matters. A photo is never retouched. */
final class PhotoCropper
{
    private const MAX_WIDTH = 1600;

    private const JPEG_QUALITY = 92;

    /** @return string JPEG bytes */
    public function crop(string $bytes, float $focusX, float $focusY): string
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
}
