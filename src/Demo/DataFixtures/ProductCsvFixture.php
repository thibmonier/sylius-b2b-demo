<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Infrastructure\Csv\CsvProductReader;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Imports the industrial catalogue from three CSV files (epi.csv, quincaillerie.csv, fournitures.csv).
 *
 * The fixture is idempotent: running it a second time updates existing products
 * instead of creating duplicates. Products are looked up by their code (= SKU).
 *
 * Validation is performed upfront for ALL files before any product is persisted (see CsvProductReader):
 *   - Every required CSV column must be present, every row complete, the text UTF-8, `attributes_json` valid.
 *   - No two rows, in the same file or in two files, may share the same SKU.
 */
final class ProductCsvFixture extends AbstractFixture
{
    private const CSV_FILES = ['epi.csv', 'quincaillerie.csv', 'fournitures.csv'];

    private const BATCH_SIZE = 25;

    /** US-202: the prices of the catalogue are excluding tax; the shop adds the VAT of this category (20 %, zone FR). */
    private const TAX_CATEGORY = 'standard';

    /**
     * Lookups shared by the products of a batch (S2-R7): the entity manager is cleared after each batch, which
     * detaches these entities, so they are dropped with it and read again once per batch instead of once per product.
     *
     * @var list<ChannelInterface>|null
     */
    private ?array $channels = null;

    /** @var array<string, TaxonInterface|null> */
    private array $taxons = [];

    /** @var array<string, ProductAttributeInterface|null> */
    private array $attributes = [];

    private ?TaxCategoryInterface $taxCategory = null;

    /**
     * @param FactoryInterface<ProductInterface>            $productFactory
     * @param FactoryInterface<ProductVariantInterface>     $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface>     $channelPricingFactory
     * @param FactoryInterface<ProductAttributeValueInterface> $attributeValueFactory
     * @param FactoryInterface<ProductTaxonInterface>       $productTaxonFactory
     * @param RepositoryInterface<ProductInterface>         $productRepository
     * @param RepositoryInterface<TaxonInterface>           $taxonRepository
     * @param RepositoryInterface<ProductAttributeInterface> $attributeRepository
     * @param RepositoryInterface<ChannelInterface>         $channelRepository
     * @param RepositoryInterface<TaxCategoryInterface>     $taxCategoryRepository
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
        #[Autowire(service: 'sylius.factory.product_taxon')]
        private readonly FactoryInterface $productTaxonFactory,
        private readonly ProductImageAttacher $imageAttacher,
        #[Autowire(service: 'sylius.repository.tax_category')]
        private readonly RepositoryInterface $taxCategoryRepository,
    ) {
    }

    public function load(array $options): void
    {
        $csvDir = $this->projectDir . '/fixtures/csv/';

        // Phase 1: Validate all CSV files upfront — no product is touched yet.
        /** @var array<string, list<array<string, string>>> $allRows */
        $allRows = $this->csvProductReader->readAll(\array_map(static fn (string $fileName): string => $csvDir . $fileName, self::CSV_FILES));

        // Phase 2: Import all validated rows, one batch (one flush) at a time.
        foreach (\array_chunk(\array_merge(...\array_values($allRows)), self::BATCH_SIZE) as $rows) {
            $products = $this->existingProducts($rows);
            foreach ($rows as $row) {
                $this->importProduct($row, $products[\trim($row['sku'])] ?? null);
            }
            $this->objectManager->flush();
            $this->objectManager->clear();
            $this->channels = null;
            $this->taxons = [];
            $this->attributes = [];
            $this->taxCategory = null;
        }
    }

    public function getName(): string
    {
        return 'demo_products_csv';
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * @param list<array<string, string>> $rows
     *
     * @return array<string, ProductInterface> the products of the batch that already exist, by code (one query)
     */
    private function existingProducts(array $rows): array
    {
        $codes = \array_map(static fn (array $row): string => \trim($row['sku']), $rows);
        $products = [];
        foreach ($this->productRepository->findBy(['code' => $codes]) as $product) {
            $products[(string) $product->getCode()] = $product;
        }

        return $products;
    }

    /** @param array<string, string> $row */
    private function importProduct(array $row, ?ProductInterface $product): void
    {
        $sku = \trim($row['sku']);

        if ($product === null) {
            /** @var ProductInterface $product */
            $product = $this->productFactory->createNew();
            $product->setCode($sku);
        }

        $product->setEnabled(true);

        $this->translateProduct($product, $row, $sku);
        $this->assignMainTaxon($product, \trim($row['taxon']));

        // Channels
        $this->assignChannels($product);

        // Variant (one per product in this catalogue)
        $this->createOrUpdateVariant($product, $row);

        // Attributes
        $this->assignAttributes($product, $row);

        $this->imageAttacher->attach($product);

        $this->objectManager->persist($product);
    }

    /** @param array<string, string> $row */
    private function translateProduct(ProductInterface $product, array $row, string $sku): void
    {
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
    }

    private function assignMainTaxon(ProductInterface $product, string $taxonCode): void
    {
        $taxon = $this->taxon($taxonCode);
        if ($taxon === null) {
            return;
        }

        $product->setMainTaxon($taxon);
        // Shop taxon listings query product_taxon, not the main taxon.
        if (!$product->hasTaxon($taxon)) {
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($taxon);
            $product->addProductTaxon($productTaxon);
        }
    }

    private function assignChannels(ProductInterface $product): void
    {
        foreach ($this->channels() as $channel) {
            if (!$product->hasChannel($channel)) {
                $product->addChannel($channel);
            }
        }
    }

    /** @param array<string, string> $row */
    private function createOrUpdateVariant(ProductInterface $product, array $row): void
    {
        $variant = $this->variantOf($product, \trim($row['sku']));

        // Translations
        foreach (['fr_FR' => 'name_fr', 'en_US' => 'name_en'] as $locale => $nameKey) {
            $variant->setCurrentLocale($locale);
            $variant->setFallbackLocale($locale);
            $variant->setName(\trim($row[$nameKey]));
        }

        $variant->setTaxCategory($this->taxCategory());
        $this->applyDimensions($variant, $row);
        $this->applyPrice($variant, (int) \round((float) ($row['price_eur_ht'] ?? 0) * 100));

        $this->objectManager->persist($variant);
    }

    private function variantOf(ProductInterface $product, string $sku): ProductVariantInterface
    {
        // Try to find an existing variant by code.
        // $product->getVariants() yields the base ProductVariantInterface from the
        // Product component; the actual runtime type is always the Core variant,
        // so assert to narrow PHPStan's view to the Core interface (no @var workaround).
        foreach ($product->getVariants() as $variant) {
            if ($variant->getCode() === $sku) {
                \assert($variant instanceof ProductVariantInterface);

                return $variant;
            }
        }

        $variant = $this->productVariantFactory->createNew();
        $variant->setCode($sku);
        $product->addVariant($variant);

        return $variant;
    }

    /** @param array<string, string> $row */
    private function applyDimensions(ProductVariantInterface $variant, array $row): void
    {
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
    }

    private function applyPrice(ProductVariantInterface $variant, int $priceInCents): void
    {
        // Channel pricing
        foreach ($this->channels() as $channel) {
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
            $attribute = $this->attribute($code);
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

    /** @return list<ChannelInterface> */
    private function channels(): array
    {
        if (null === $this->channels) {
            /** @var list<ChannelInterface> $channels */
            $channels = $this->channelRepository->findAll();
            $this->channels = $channels;
        }

        return $this->channels;
    }

    private function taxCategory(): TaxCategoryInterface
    {
        $category = $this->taxCategory ??= $this->taxCategoryRepository->findOneBy(['code' => self::TAX_CATEGORY]);
        if (!$category instanceof TaxCategoryInterface) {
            throw new \LogicException(\sprintf('Tax category "%s" must exist before the products (fixtures "tax_category").', self::TAX_CATEGORY));
        }

        return $category;
    }

    private function taxon(string $code): ?TaxonInterface
    {
        if (!\array_key_exists($code, $this->taxons)) {
            /** @var TaxonInterface|null $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $code]);
            $this->taxons[$code] = $taxon;
        }

        return $this->taxons[$code];
    }

    private function attribute(string $code): ?ProductAttributeInterface
    {
        if (!\array_key_exists($code, $this->attributes)) {
            /** @var ProductAttributeInterface|null $attribute */
            $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
            $this->attributes[$code] = $attribute;
        }

        return $this->attributes[$code];
    }
}
