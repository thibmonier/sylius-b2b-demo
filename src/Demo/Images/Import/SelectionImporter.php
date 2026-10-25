<?php

declare(strict_types=1);

namespace App\Demo\Images\Import;

use App\Demo\Images\Fetch\ImageProcessor;
use App\Demo\Images\ImageManifest;

/**
 * US-196: turns the photos of the selection, supplied as local files, into the WebP files of fixtures/images and the rows of
 * the manifest. No network: the originals were downloaded by hand, with the GO of the owner, and are not versioned.
 */
final readonly class SelectionImporter
{
    public function __construct(
        private ImageProcessor $processor,
        private PhotoCropper $cropper,
    ) {
    }

    /** @return array{photos: int, skus: int, removed: list<string>} */
    public function import(PhotoSelection $selection, string $sourceDir, string $imagesDir, ImageManifest $manifest, string $date): array
    {
        $this->assertUsableSource($sourceDir);
        foreach ($selection->photos() as $photo) {
            if (!\is_file($sourceDir . '/' . $photo->file)) {
                throw new \RuntimeException(\sprintf('The original "%s" of the type "%s" is missing from "%s".', $photo->file, $photo->type, $sourceDir));
            }
        }

        $skus = 0;
        foreach ($selection->photos() as $photo) {
            $file = $this->write($photo, $sourceDir, $imagesDir);
            foreach ($photo->skus as $sku) {
                $manifest->add($this->row($photo, $sku, $file, $date));
                ++$skus;
            }
        }

        return ['photos' => \count($selection->photos()), 'skus' => $skus, 'removed' => $this->removeOrphans($imagesDir, $manifest)];
    }

    /** A source that is empty, missing or the root of the disk is refused before anything is read or written. */
    private function assertUsableSource(string $sourceDir): void
    {
        $real = $sourceDir === '' ? false : \realpath($sourceDir);
        if ($real === false || !\is_dir($real) || \dirname($real) === $real) {
            throw new \RuntimeException(\sprintf('The source folder "%s" is empty, missing or the root of the disk: give the folder of the original photos.', $sourceDir));
        }
    }

    private function write(SelectedPhoto $photo, string $sourceDir, string $imagesDir): string
    {
        $cropped = $this->cropper->crop((string) \file_get_contents($sourceDir . '/' . $photo->file), $photo->focusX, $photo->focusY);
        $webp = $this->processor->toWebp($cropped) ?? throw new \RuntimeException(\sprintf('The photo of the type "%s" does not fit the image budget.', $photo->type));
        $file = \substr(\sha1($webp), 0, 12) . '.webp';
        \file_put_contents($imagesDir . '/' . $file, $webp);

        return $file;
    }

    /** @return array<string, string> */
    private function row(SelectedPhoto $photo, string $sku, string $file, string $date): array
    {
        return [
            'sku' => $sku, 'file' => $file, 'source' => \strtolower($photo->source), 'creator' => $photo->creator, 'title' => $photo->title,
            'origin_url' => $photo->originUrl, 'license' => \strtolower($photo->source), 'license_url' => $photo->licenseUrl, 'fetched_at' => $date,
            'scope' => \count($photo->skus) > 1 ? 'family' : 'own', 'status' => ImageManifest::STATUS_VALIDATED,
        ];
    }

    /** @return list<string> */
    private function removeOrphans(string $imagesDir, ImageManifest $manifest): array
    {
        $listed = $manifest->files();
        $removed = [];
        foreach (\glob($imagesDir . '/*.webp') ?: [] as $path) {
            if (!\in_array(\basename($path), $listed, true)) {
                \unlink($path);
                $removed[] = \basename($path);
            }
        }
        \sort($removed);

        return $removed;
    }
}
