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

    /**
     * @param list<string> $only US-197: the types to import (the others keep their rows and files); empty = the whole selection
     *
     * @return array{photos: int, skus: int, removed: list<string>}
     */
    public function import(PhotoSelection $selection, string $sourceDir, string $imagesDir, ImageManifest $manifest, string $date, array $only = []): array
    {
        $this->assertUsableSource($sourceDir);
        $photos = $this->photosToImport($selection, $only);
        foreach ($photos as $photo) {
            if (!\is_file($sourceDir . '/' . $photo->file)) {
                throw new \RuntimeException(\sprintf('The original "%s" of the type "%s" is missing from "%s".', $photo->file, $photo->type, $sourceDir));
            }
        }

        $neutral = $only === [] ? $selection->neutral() : [];
        foreach ($neutral as $type) {
            if (!\is_file($imagesDir . '/' . $type['file'])) {
                throw new \RuntimeException(\sprintf('The neutral visual "%s" of the type "%s" is missing from "%s".', $type['file'], $type['type'], $imagesDir));
            }
        }

        $skus = 0;
        foreach ($photos as $photo) {
            $file = $this->write($photo, $sourceDir, $imagesDir);
            foreach ($photo->skus as $sku) {
                $manifest->add($this->row($photo, $sku, $file, $date));
                ++$skus;
            }
        }

        foreach ($neutral as $type) {
            foreach ($type['skus'] as $sku) {
                $manifest->add($this->neutralRow($sku, $type['file'], $date));
            }
        }

        return ['photos' => \count($photos), 'skus' => $skus, 'removed' => $this->removeOrphans($imagesDir, $manifest)];
    }

    /**
     * @param list<string> $only
     *
     * @return list<SelectedPhoto>
     */
    private function photosToImport(PhotoSelection $selection, array $only): array
    {
        if ($only === []) {
            return $selection->photos();
        }
        $known = \array_map(static fn (SelectedPhoto $photo): string => $photo->type, $selection->photos());
        $unknown = \array_diff($only, $known);
        if ($unknown !== []) {
            throw new \RuntimeException(\sprintf('Unknown photo type(s) "%s": expected one of %s.', \implode('", "', $unknown), \implode(', ', $known)));
        }

        return \array_values(\array_filter($selection->photos(), static fn (SelectedPhoto $photo): bool => \in_array($photo->type, $only, true)));
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
            'scope' => \count($photo->skus) > 1 ? 'family' : 'own', 'status' => ImageManifest::STATUS_VALIDATED, ImageManifest::FOCAL => $photo->focal,
        ];
    }

    /** @return array<string, string> a neutral visual of the category, drawn for the demo (CC0) */
    private function neutralRow(string $sku, string $file, string $date): array
    {
        return [
            'sku' => $sku, 'file' => $file, 'source' => ImageManifest::SOURCE_GENERATED, 'creator' => 'Thibaut Monier',
            'title' => 'Neutral visual: ' . (\preg_replace('/^category-|\.webp$/', '', $file) ?? ''), 'origin_url' => 'https://github.com/thibmonier/sylius-b2b-demo',
            'license' => 'cc0', 'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/', 'fetched_at' => $date,
            'scope' => 'category', 'status' => ImageManifest::STATUS_FALLBACK,
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
