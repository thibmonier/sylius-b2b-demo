<?php

declare(strict_types=1);

namespace App\Demo\Images\Import;

/** US-196: the photo chosen for one type of article, and how to frame it. */
final readonly class SelectedPhoto
{
    /**
     * @param list<string>                              $skus
     * @param list<array{float, float, float, float}> $blurs  areas to blur, as fractions of the original: x, y, width, height
     */
    public function __construct(
        public string $type,
        public array $skus,
        public string $file,
        public string $title,
        public string $creator,
        public string $originUrl,
        public string $source,
        public string $licenseUrl,
        public float $focusX,
        public float $focusY,
        public array $blurs,
        public string $review,
    ) {
    }
}
