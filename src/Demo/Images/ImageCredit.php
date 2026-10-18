<?php

declare(strict_types=1);

namespace App\Demo\Images;

/** US-061: one credited image (a file may be shown by several products). */
final readonly class ImageCredit
{
    public function __construct(
        public string $file,
        public string $title,
        public string $creator,
        public string $source,
        public string $originUrl,
        public string $licenseUrl,
        public bool $generated,
        /** Taxon of a generated neutral visual (empty for a photo), to translate its title. */
        public string $category,
        public int $products,
    ) {
    }
}
