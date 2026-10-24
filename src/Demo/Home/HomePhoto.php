<?php

declare(strict_types=1);

namespace App\Demo\Home;

/** US-186: a photo of the home with its sizes (smallest first) and its credit (source page, author, licence). */
final readonly class HomePhoto
{
    /** @param non-empty-list<HomePhotoFile> $files */
    public function __construct(
        public string $slot,
        public array $files,
        public string $source,
        public string $originUrl,
        public string $creator,
        public string $creatorUrl,
        public string $license,
        public string $licenseUrl,
    ) {
    }

    public function smallest(): HomePhotoFile
    {
        return $this->files[0];
    }

    public function largest(): HomePhotoFile
    {
        return $this->files[\count($this->files) - 1];
    }
}
