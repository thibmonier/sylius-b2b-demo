<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Demo\Theme\PriceBreakRow;
use App\Demo\Theme\PriceBreaks;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * US-090 / US-091: what the buyer reads on the product page, on a card and in the cart summary.
 */
final class PriceBreaksTemplatesTest extends TestCase
{
    public function testTheProductPageListsAtLeastThreeTiersWithAccessibleHeaders(): void
    {
        $html = $this->productPage($this->publicBreaks());

        self::assertSame(4, substr_count($html, 'data-test-tmonier-tier-row'));
        self::assertStringContainsString('<caption class="visually-hidden">app.theme_b2b.price_breaks.caption</caption>', $html);
        self::assertSame(3, substr_count($html, '<th scope="col"'));
        self::assertSame(4, substr_count($html, '<th scope="row"'));
        self::assertStringNotContainsString('b2b-negotiated"', $html);
        self::assertStringContainsString('data-controller="b2b-price-breaks"', $html);
    }

    public function testTheCompanyPriceAndItsGapWithTheCatalogueArePresented(): void
    {
        $html = $this->productPage($this->companyBreaks());

        self::assertStringContainsString('data-test-b2b-negotiated-price', $html);
        self::assertStringContainsString('negotiated_title|%company%=Acme Industries', $html);
        self::assertStringContainsString('%price%=16.28', $html);
        self::assertStringContainsString('%percent%=12 %', $html);
        self::assertStringContainsString('%base%=18.5', $html);
        self::assertSame(3, substr_count($html, 'class="b2b-badge-negotiated'));
    }

    public function testAProductWithoutTierHasNoTable(): void
    {
        self::assertSame('', trim($this->productPage(null)));
    }

    public function testTheCardShowsTheCompanyPriceOnItsOwnLine(): void
    {
        $html = $this->render('product_card_from_price.html.twig', ['product' => 'p'], $this->companyBreaks(), fromPrice: 1517);

        self::assertStringContainsString('data-test-b2b-product-negotiated-price', $html);
        self::assertStringContainsString('card_negotiated|%company%=Acme Industries', $html);
        self::assertStringContainsString('data-test-b2b-product-from-price', $html);
    }

    public function testTheCardShowsNothingForAProductWithoutTier(): void
    {
        self::assertSame('', trim($this->render('product_card_from_price.html.twig', ['product' => 'p'], null, fromPrice: null)));
    }

    private function publicBreaks(): PriceBreaks
    {
        return new PriceBreaks([new PriceBreakRow(1, 1850, 0.0, false), new PriceBreakRow(10, 1757, 5.0, false), new PriceBreakRow(50, 1665, 10.0, false), new PriceBreakRow(200, 1573, 15.0, false)]);
    }

    private function companyBreaks(): PriceBreaks
    {
        return new PriceBreaks([new PriceBreakRow(1, 1850, 0.0, false), new PriceBreakRow(2, 1628, 12.0, true), new PriceBreakRow(10, 1628, 12.0, true), new PriceBreakRow(50, 1517, 18.0, true)], 'Acme Industries');
    }

    private function productPage(?PriceBreaks $breaks): string
    {
        $form = ['vars' => ['value' => ['cartItem' => ['variant' => 'variant']]]];

        return $this->render('product_price_breaks.html.twig', ['form' => $form], $breaks);
    }

    /** @param array<string, mixed> $context */
    private function render(string $template, array $context, mixed $subject, ?int $fromPrice = null): string
    {
        $twig = new Environment(new ChainLoader([
            new ArrayLoader(['@SyliusShop/shared/macro/money.html.twig' => '{% macro convertAndFormat(amount) %}{{ amount / 100 }}{% endmacro %}']),
            new FilesystemLoader(\dirname(__DIR__, 3) . '/templates/shop/theme_b2b'),
        ]), ['strict_variables' => true]);
        $twig->addGlobal('sylius', ['localeCode' => 'fr_FR', 'currencyCode' => 'EUR', 'channel' => ['baseCurrency' => ['code' => 'EUR']]]);
        $twig->addFunction(new TwigFunction('app_b2b_percent', static fn (float $rate, string $locale): string => $rate . ' %'));
        $twig->addFilter(new TwigFilter('sylius_convert_money', static fn (int $amount): int => $amount));
        $twig->addFilter(new TwigFilter('trans', static fn (string $id, array $params = []): string => [] === $params ? $id : $id . '|' . implode('|', array_map(static fn ($k, $v): string => $k . '=' . $v, array_keys($params), $params))));
        $twig->addFunction(new TwigFunction('app_b2b_price_breaks', static fn (): ?PriceBreaks => $subject instanceof PriceBreaks ? $subject : null));
        $twig->addFunction(new TwigFunction('app_b2b_tier_from_price', static fn (): ?int => $fromPrice));
        $twig->addFunction(new TwigFunction('tmonier_tier_pricing_tax_convention', static fn (): string => 'excluded'));
        $twig->addFunction(new TwigFunction('sylius_test_html_attribute', static fn (string $name, mixed $value = null): string => 'data-test-' . $name, ['is_safe' => ['html']]));

        return $twig->render($template, ['hookable_metadata' => ['context' => $context]]);
    }
}
