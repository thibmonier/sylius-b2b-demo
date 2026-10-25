<?php

declare(strict_types=1);

namespace App\Demo\Images;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** US-061: the credits of the demo images, one entry per file, read from the manifest (local file, no external call). */
final readonly class ImageCredits
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/fixtures/images/manifest.csv')]
        private string $manifestPath,
    ) {
    }

    /** @return list<ImageCredit> */
    public function all(): array
    {
        $credits = [];
        foreach (ImageManifest::load($this->manifestPath)->rows() as $row) {
            $file = $row['file'];
            $credits[$file] = new ImageCredit(
                $file,
                $row['title'],
                $row['creator'],
                $row['source'],
                $row['origin_url'],
                $row['license_url'],
                $row['license'],
                $generated = ImageManifest::SOURCE_GENERATED === $row['source'],
                $generated ? preg_replace('/^category-|\.webp$/', '', $file) ?? '' : '',
                ($credits[$file]->products ?? 0) + 1,
            );
        }

        return array_values($credits);
    }
}
