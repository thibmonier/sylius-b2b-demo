<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Infrastructure\Csv\CsvProductReader;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Imports the industrial catalogue from three CSV files (epi.csv, quincaillerie.csv, fournitures.csv).
 *
 * The fixture is idempotent: running it a second time updates existing products
 * instead of creating duplicates. Products are looked up by their code (= SKU).
 *
 * Validation is performed upfront for ALL files before any product is persisted:
 *   - Every required CSV column must be present.
 *   - No two rows in the same file may share the same SKU.
 */
final class ProductCsvFixture extends AbstractFixture
{
    private const CSV_FILES = ['epi.csv', 'quincaillerie.csv', 'fournitures.csv'];

    /**
     * @param FactoryInterface<ProductInterface>            $productFactory
     * @param FactoryInterface<ProductVariantInterface>     $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface>     $channelPricingFactory
     * @param FactoryInterface<ProductAttributeValueInterface> $attributeValueFactory
     * @param RepositoryInterface<ProductInterface>         $productRepository
     * @param RepositoryInterface<TaxonInterface>           $taxonRepository
     * @param RepositoryInterface<ProductAttributeInterface> $attributeRepository
     * @param RepositoryInterface<ChannelInterface>         $channelRepository
     */
    public function __construct(
        private readonly CsvProductReader $csvProductReader,
        private readonly EntityManagerInterface $objectManager,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(service: 'sylius.factory.product')]
        private readonly FactoryInterface $productFactory,
        #[Autowire(service: 'sylius.factory.product_variant')]
        private readonly FactoryInterface $productVariantFactory,
        #[Autowire(service: 'sylius.factory.channel_pricing')]
        private readonly FactoryInterface $channelPricingFactory,
        #[Autowire(service: 'sylius.factory.product_attribute_value')]
        private readonly FactoryInterface $attributeValueFactory,
        #[Autowire(service: 'sylius.repository.product')]
        private readonly RepositoryInterface $productRepository,
        #[Autowire(service: 'sylius.repository.taxon')]
        private readonly RepositoryInterface $taxonRepository,
        #[Autowire(service: 'sylius.repository.product_attribute')]
        private readonly RepositoryInterface $attributeRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        $csvDir = $this->projectDir . '/fixtures/csv/';

        // Phase 1: Validate all CSV files upfront — no product is touched yet.
        /** @var array<string, list<array<string, string>>> $allRows */
        $allRows = [];
        foreach (self::CSV_FILES as $fileName) {
            $allRows[$fileName] = $this->csvProductReader->read($csvDir . $fileName);
        }

        // Phase 2: Import all validated rows.
        $i = 0;
        foreach ($allRows as $rows) {
            foreach ($rows as $row) {
                $this->importProduct($row);
                ++$i;
                if ($i % 25 === 0) {
                    $this->objectManager->flush();
                    $this->objectManager->clear();
                }
            }
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
    }

    public function getName(): string
    {
        return 'demo_products_csv';
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /** @param array<string, string> $row */
    private function importProduct(array $row): void
    {
        $sku = \trim($row['sku']);

        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $sku]);

        if ($product === null) {
            /** @var ProductInterface $product */
            $product = $this->productFactory->createNew();
            $product->setCode($sku);
        }

        $product->setEnabled(true);

        // Translations
        $slug = \strtolower(\str_replace(['_', ' '], '-', $sku));
        foreach (['fr_FR' => 'name_fr', 'en_US' => 'name_en'] as $locale => $nameKey) {
            $descKey = ($locale === 'fr_FR') ? 'description_fr' : 'description_en';
            $product->setCurrentLocale($locale);
            $product->setFallbackLocale($locale);
            $product->setName(\trim($row[$nameKey]));
            $product->setSlug($slug . '-' . ($locale === 'fr_FR' ? 'fr' : 'en'));
            $description = \trim($row[$descKey] ?? '');
            if ($description !== '') {
                $product->setDescription($description);
                $product->setShortDescription(\mb_strimwidth($description, 0, 255, '…'));
            }
        }

        // Main taxon
        $taxonCode = \trim($row['taxon']);
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if ($taxon !== null) {
            $product->setMainTaxon($taxon);
        }

        // Channels
        $this->assignChannels($product);

        // Variant (one per product in this catalogue)
        $this->createOrUpdateVariant($product, $row);

        // Attributes
        $this->assignAttributes($product, $row);

        $this->objectManager->persist($product);
    }

    private function assignChannels(ProductInterface $product): void
    {
        /** @var list<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            if (!$product->hasChannel($channel)) {
                $product->addChannel($channel);
            }
        }
    }

    /** @param array<string, string> $row */
    private function createOrUpdateVariant(ProductInterface $product, array $row): void
    {
        $sku = \trim($row['sku']);

        // Try to find an existing variant by code.
        // $product->getVariants() yields the base ProductVariantInterface from the
        // Product component; the actual runtime type is always the Core variant,
        // so assert to narrow PHPStan's view to the Core interface (no @var workaround).
        $variant = null;
        foreach ($product->getVariants() as $v) {
            if ($v->getCode() === $sku) {
                \assert($v instanceof ProductVariantInterface);
                $variant = $v;

                break;
            }
        }

        if ($variant === null) {
            $variant = $this->productVariantFactory->createNew();
            $variant->setCode($sku);
            $product->addVariant($variant);
        }

        // Translations
        foreach (['fr_FR' => 'name_fr', 'en_US' => 'name_en'] as $locale => $nameKey) {
            $variant->setCurrentLocale($locale);
            $variant->setFallbackLocale($locale);
            $variant->setName(\trim($row[$nameKey]));
        }

        // Physical dimensions
        $weight = (float) ($row['weight_kg'] ?? 0);
        if ($weight > 0) {
            $variant->setWeight($weight);
        }
        $width = (float) ($row['width_cm'] ?? 0);
        if ($width > 0) {
            $variant->setWidth($width);
        }
        $height = (float) ($row['height_cm'] ?? 0);
        if ($height > 0) {
            $variant->setHeight($height);
        }
        $depth = (float) ($row['depth_cm'] ?? 0);
        if ($depth > 0) {
            $variant->setDepth($depth);
        }

        // Channel pricing
        $priceHt = (float) ($row['price_eur_ht'] ?? 0);
        $priceInCents = (int) \round($priceHt * 100);

        /** @var list<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $channelCode = $channel->getCode();
            if ($channelCode === null) {
                continue;
            }

            $existingPricing = $variant->getChannelPricingForChannel($channel);
            if ($existingPricing !== null) {
                $existingPricing->setPrice($priceInCents);
            } else {
                /** @var ChannelPricingInterface $pricing */
                $pricing = $this->channelPricingFactory->createNew();
                $pricing->setChannelCode($channelCode);
                $pricing->setPrice($priceInCents);
                $variant->addChannelPricing($pricing);
            }
        }

        $this->objectManager->persist($variant);
    }

    /** @param array<string, string> $row */
    private function assignAttributes(ProductInterface $product, array $row): void
    {
        $jsonRaw = \trim($row['attributes_json'] ?? '');
        if ($jsonRaw === '' || $jsonRaw === '{}') {
            return;
        }

        /** @var array<string, string>|null $attrs */
        $attrs = \json_decode($jsonRaw, true);
        if (!\is_array($attrs)) {
            return;
        }

        foreach ($attrs as $code => $value) {
            /** @var ProductAttributeInterface|null $attribute */
            $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
            if ($attribute === null) {
                continue;
            }

            foreach (['fr_FR', 'en_US'] as $locale) {
                $existingValue = $product->getAttributeByCodeAndLocale($code, $locale);
                if ($existingValue !== null) {
                    $existingValue->setValue($value);
                } else {
                    /** @var ProductAttributeValueInterface $attrValue */
                    $attrValue = $this->attributeValueFactory->createNew();
                    $attrValue->setAttribute($attribute);
                    $attrValue->setLocaleCode($locale);
                    $attrValue->setValue($value);
                    $product->addAttribute($attrValue);
                    $this->objectManager->persist($attrValue);
                }
            }
        }
    }
}
