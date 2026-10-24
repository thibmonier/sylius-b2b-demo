<?php

declare(strict_types=1);

namespace App\Demo\Home;

/** US-186: one size of a photo of the home (a WebP file of assets/shop/images/home/). */
final readonly class HomePhotoFile
{
    public function __construct(
        public string $file,
        public int $width,
        public int $height,
    ) {
    }
}
