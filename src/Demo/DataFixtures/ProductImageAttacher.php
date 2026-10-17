<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Images\ImageManifest;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Gives a demo product the image the manifest lists for its SKU (US-047). Local files only (fixtures/images/): the
 * fixtures, the reset and the CI never call an image bank. Idempotent: a product that already has a main image keeps it.
 */
final class ProductImageAttacher
{
    private ?ImageManifest $manifest = null;

    /** @param FactoryInterface<ProductImageInterface> $productImageFactory */
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(service: 'sylius.factory.product_image')]
        private readonly FactoryInterface $productImageFactory,
        private readonly ImageUploaderInterface $imageUploader,
    ) {
    }

    public function attach(ProductInterface $product): void
    {
        $file = $this->manifest()->fileFor((string) $product->getCode());
        if ($file === null || !$product->getImagesByType('main')->isEmpty()) {
            return;
        }

        $image = $this->productImageFactory->createNew();
        $image->setType('main');
        $image->setFile(new UploadedFile($this->projectDir . '/fixtures/images/' . $file, $file, 'image/webp', null, true));
        $this->imageUploader->upload($image);
        $product->addImage($image);
    }

    private function manifest(): ImageManifest
    {
        return $this->manifest ??= ImageManifest::load($this->projectDir . '/fixtures/images/manifest.csv');
    }
}
