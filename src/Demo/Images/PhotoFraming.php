<?php

declare(strict_types=1);

namespace App\Demo\Images;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-197: where to cut the 4:3 photo of a product when the frame is not 4:3 (the portrait card) or when the subject sits on a side.
 * The point focal comes from the manifest (local file, read once); a product without one is framed as before, centered. The photo is never retouched.
 */
final class PhotoFraming
{
    private ?ImageManifest $manifest = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%/fixtures/images/manifest.csv')]
        private readonly string $manifestPath,
    ) {
    }

    /** @return string|null the value of `object-position` (e.g. "100% 50%"), null for the default framing */
    public function objectPosition(?string $sku): ?string
    {
        $focal = $sku === null ? null : $this->manifest()->focalFor($sku);

        return $focal === null ? null : \sprintf('%s%% %s%%', $this->percent($focal[0]), $this->percent($focal[1]));
    }

    private function percent(float $value): string
    {
        return \rtrim(\rtrim(\number_format($value, 2, '.', ''), '0'), '.');
    }

    private function manifest(): ImageManifest
    {
        return $this->manifest ??= ImageManifest::load($this->manifestPath);
    }
}
