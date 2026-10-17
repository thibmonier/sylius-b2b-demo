<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

/** Shrinks a downloaded image to the repository budget: WebP, 800 px on the long side, at most 80 KB. */
final class ImageProcessor
{
    public const MAX_SIDE = 800;

    public const MAX_BYTES = 80_000;

    private const MIN_SIDE = 300;

    private const QUALITIES = [80, 70, 60, 50, 40, 30];

    /** @return string|null the WebP bytes, or null when the bytes are not a usable image */
    public function toWebp(string $bytes): ?string
    {
        $source = @\imagecreatefromstring($bytes);
        if ($source === false) {
            return null;
        }
        $width = \imagesx($source);
        $height = \imagesy($source);
        if (\max($width, $height) < self::MIN_SIDE) {
            return null;
        }

        for ($side = self::MAX_SIDE; $side >= 480; $side -= 160) {
            $webp = $this->encode($this->flattened($source, $width, $height, $side));
            if ($webp !== null) {
                return $webp;
            }
        }

        return null;
    }

    private function flattened(\GdImage $source, int $width, int $height, int $side): \GdImage
    {
        $ratio = \min(1, $side / \max($width, $height));
        $targetWidth = \max(1, (int) \round($width * $ratio));
        $targetHeight = \max(1, (int) \round($height * $ratio));
        $target = \imagecreatetruecolor($targetWidth, $targetHeight);
        \imagefill($target, 0, 0, (int) \imagecolorallocate($target, 255, 255, 255));
        \imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $target;
    }

    private function encode(\GdImage $image): ?string
    {
        foreach (self::QUALITIES as $quality) {
            \ob_start();
            \imagewebp($image, null, $quality);
            $webp = (string) \ob_get_clean();
            if (\strlen($webp) <= self::MAX_BYTES) {
                return $webp;
            }
        }

        return null;
    }
}
