<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Demo\Theme\PreloadedVariantResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;

/** S4-R3 — the first enabled variant is read from memory once the variants are loaded, never otherwise. */
final class PreloadedVariantResolverTest extends TestCase
{
    public function testItDelegatesWhileTheVariantsAreNotLoaded(): void
    {
        $product = new Product();
        $fromCore = new ProductVariant();
        $inner = $this->createStub(ProductVariantResolverInterface::class);
        $inner->method('getVariant')->willReturn($fromCore);

        self::assertSame($fromCore, (new PreloadedVariantResolver($inner))->getVariant($product));
    }

    public function testItReadsTheFirstEnabledVariantOnceTheVariantsAreLoaded(): void
    {
        $disabled = $this->variant(false);
        $enabled = $this->variant(true);
        $product = $this->productWithLoadedVariants([$disabled, $enabled]);
        $inner = $this->createMock(ProductVariantResolverInterface::class);
        $inner->expects(self::never())->method('getVariant');

        self::assertSame($enabled, (new PreloadedVariantResolver($inner))->getVariant($product));
    }

    public function testItReturnsNullWhenNoLoadedVariantIsEnabled(): void
    {
        $product = $this->productWithLoadedVariants([$this->variant(false)]);

        self::assertNull((new PreloadedVariantResolver($this->createStub(ProductVariantResolverInterface::class)))->getVariant($product));
    }

    private function variant(bool $enabled): ProductVariant
    {
        $variant = new ProductVariant();
        $variant->setEnabled($enabled);

        return $variant;
    }

    /** @param list<ProductVariant> $variants */
    private function productWithLoadedVariants(array $variants): Product
    {
        $product = new Product();
        $collection = new PersistentCollection($this->createStub(EntityManagerInterface::class), new ClassMetadata(ProductVariant::class), new ArrayCollection($variants));
        (new \ReflectionProperty($product, 'variants'))->setValue($product, $collection);

        return $product;
    }
}
