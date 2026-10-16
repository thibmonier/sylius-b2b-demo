<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Demo\Theme\B2bThemeExtension;
use App\Demo\Theme\PackagingUnitProvider;
use App\Demo\Theme\PackagingUnitRows;
use App\Demo\Theme\TierFromPriceProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceCalculatorInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceRow;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceTable;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * US-013 — dense B2B catalogue data (packaging, stock, "from" tier price) and the templates that
 * show it: present data is shown, missing data leaves no empty label and no error.
 */
final class B2bThemeTest extends TestCase
{
    public function testCardShowsPackagingStockAndFromPrice(): void
    {
        $product = $this->product(1, tracked: true, onHand: 10);
        $twig = $this->twig(rows: [['productId' => 1, 'locale' => 'fr_FR', 'text' => 'Boîte de 100']], table: $this->tiers());

        $meta = $twig->render('product_card_meta.html.twig', $this->context($product));
        $from = $twig->render('product_card_from_price.html.twig', $this->context($product));

        self::assertStringContainsString('Boîte de 100', $meta);
        self::assertStringContainsString('b2b-stock--in', $meta);
        self::assertStringContainsString('from 7', $from);
        self::assertStringContainsString('b2b-product-from-price', $from);
    }

    public function testFromPriceIsTheLowestTierPrice(): void
    {
        $provider = $this->fromPrice($this->tiers());

        self::assertSame(700, $provider->fromPrice(new ProductVariant()));
    }

    public function testNoTierMeansNoFromPrice(): void
    {
        $noTier = new TierPriceTable([new TierPriceRow(1, 1000, 0.0, false)]);
        $twig = $this->twig(rows: [], table: $noTier);

        self::assertNull($this->fromPrice($noTier)->fromPrice(new ProductVariant()));
        self::assertSame('', trim($twig->render('product_card_from_price.html.twig', $this->context($this->product(1)))));
    }

    public function testNoChannelMeansNoFromPrice(): void
    {
        $channels = $this->createStub(ChannelContextInterface::class);
        $channels->method('getChannel')->willThrowException(new ChannelNotFoundException());
        $provider = new TierFromPriceProvider($this->createStub(TierPriceCalculatorInterface::class), $channels, $this->createStub(CustomerContextInterface::class));

        self::assertNull($provider->fromPrice(new ProductVariant()));
    }

    public function testProductWithoutVariantHasNoFromPrice(): void
    {
        self::assertNull($this->fromPrice($this->tiers())->fromPrice(null));
    }

    public function testProductWithoutPackagingOmitsTheZone(): void
    {
        $product = $this->product(2, tracked: false);
        $twig = $this->twig(rows: [['productId' => 1, 'locale' => 'fr_FR', 'text' => 'Boîte de 100']], table: $this->tiers());

        $meta = $twig->render('product_card_meta.html.twig', $this->context($product));
        $reference = $twig->render('product_reference.html.twig', $this->context($product));

        self::assertStringNotContainsString('b2b-product-packaging', $meta);
        self::assertStringNotContainsString('Packaging', $reference);
        self::assertStringContainsString('SKU-2', $reference);
    }

    public function testBlankPackagingValueIsIgnored(): void
    {
        $twig = $this->twig(rows: [['productId' => 3, 'locale' => 'fr_FR', 'text' => '  ']], table: $this->tiers());

        $reference = $twig->render('product_reference.html.twig', $this->context($this->product(3)));

        self::assertStringNotContainsString('Packaging', $reference);
    }

    public function testOutOfStockTrackedVariantIsOnOrder(): void
    {
        $twig = $this->twig(rows: [], table: $this->tiers());

        $meta = $twig->render('product_card_meta.html.twig', $this->context($this->product(4, tracked: true, onHand: 0)));

        self::assertStringContainsString('b2b-stock--out', $meta);
    }

    public function testPackagingFallsBackToAnyLocale(): void
    {
        $provider = new PackagingUnitProvider($this->rows([['productId' => 5, 'locale' => 'en_US', 'text' => 'Pack of 10']]), $this->locale('fr_FR'));

        self::assertSame('Pack of 10', $provider->forProductId(5));
        self::assertNull($provider->forProductId(null));
        self::assertNull($provider->forProductId(99));
    }

    public function testPackagingRowsAreReadOnceForAllProducts(): void
    {
        $source = new class() implements PackagingUnitRows {
            public int $calls = 0;

            public function rows(): array
            {
                ++$this->calls;

                return [['productId' => 1, 'locale' => 'fr_FR', 'text' => 'A'], ['productId' => 2, 'locale' => 'fr_FR', 'text' => 'B']];
            }
        };
        $provider = new PackagingUnitProvider($source, $this->locale('fr_FR'));

        foreach ([1, 2, 1, 3] as $id) {
            $provider->forProductId($id);
        }

        self::assertSame(1, $source->calls);
    }

    /** @return array<string, mixed> */
    private function context(Product $product): array
    {
        return ['hookable_metadata' => ['context' => ['product' => $product]]];
    }

    private function product(int $id, bool $tracked = false, int $onHand = 0): Product
    {
        $product = new class() extends Product {
            public function __construct(public ?int $fakeId = null)
            {
                parent::__construct();
            }

            public function getId(): ?int
            {
                return $this->fakeId;
            }
        };
        $product->fakeId = $id;
        $product->setCode('SKU-' . $id);
        $variant = new ProductVariant();
        $variant->setCode('V-' . $id);
        $variant->setTracked($tracked);
        $variant->setOnHand($onHand);
        $product->addVariant($variant);

        return $product;
    }

    private function tiers(): TierPriceTable
    {
        return new TierPriceTable([new TierPriceRow(1, 1000, 0.0, false), new TierPriceRow(10, 850, 15.0, false), new TierPriceRow(100, 700, 30.0, false)]);
    }

    private function fromPrice(TierPriceTable $table): TierFromPriceProvider
    {
        $calculator = $this->createStub(TierPriceCalculatorInterface::class);
        $calculator->method('table')->willReturn($table);
        $channels = $this->createStub(ChannelContextInterface::class);
        $channels->method('getChannel')->willReturn(new Channel());
        $customers = $this->createStub(CustomerContextInterface::class);
        $customers->method('getCustomer')->willReturn(null);

        return new TierFromPriceProvider($calculator, $channels, $customers);
    }

    /** @param list<array{productId: int, locale: string, text: string|null}> $rows */
    private function rows(array $rows): PackagingUnitRows
    {
        $source = $this->createStub(PackagingUnitRows::class);
        $source->method('rows')->willReturn($rows);

        return $source;
    }

    private function locale(string $code): LocaleContextInterface
    {
        $locale = $this->createStub(LocaleContextInterface::class);
        $locale->method('getLocaleCode')->willReturn($code);

        return $locale;
    }

    /** @param list<array{productId: int, locale: string, text: string|null}> $rows */
    private function twig(array $rows, TierPriceTable $table): Environment
    {
        $twig = new Environment(new ChainLoader([
            new ArrayLoader(['@SyliusShop/shared/macro/money.html.twig' => '{% macro convertAndFormat(amount) %}{{ amount / 100 }}{% endmacro %}']),
            new FilesystemLoader(\dirname(__DIR__, 3) . '/templates/shop/theme_b2b'),
        ]), ['strict_variables' => true]);
        $twig->addExtension(new B2bThemeExtension(
            new PackagingUnitProvider($this->rows($rows), $this->locale('fr_FR')),
            $this->fromPrice($table),
        ));
        $twig->addFilter(new TwigFilter('trans', static fn (string $id, array $params = []): string => match ($id) {
            'app.theme_b2b.from_price' => 'from ' . ($params['%price%'] ?? ''),
            'app.theme_b2b.packaging' => 'Packaging',
            'app.theme_b2b.reference' => 'Reference',
            default => $id,
        }));
        $twig->addFunction(new TwigFunction('sylius_test_html_attribute', static fn (string $name): string => 'data-test-' . $name . ' class-marker-' . $name, ['is_safe' => ['html']]));

        return $twig;
    }
}
