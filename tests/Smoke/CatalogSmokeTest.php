<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Catalogue smoke tests (US-008).
 * Require a database loaded with the `demo` fixture suite.
 *
 * Run: bin/console sylius:fixtures:load --suite=demo --no-interaction
 *      then: vendor/bin/phpunit tests/Smoke/CatalogSmokeTest.php
 */
final class CatalogSmokeTest extends KernelTestCase
{
    public function testCatalogHas150To250Products(): void
    {
        self::bootKernel();

        /** @var ProductRepositoryInterface $productRepo */
        $productRepo = self::getContainer()->get('sylius.repository.product');

        $count = \count($productRepo->findAll());

        self::assertGreaterThanOrEqual(
            150,
            $count,
            'The catalogue must contain at least 150 products (US-008 requirement).',
        );
        self::assertLessThanOrEqual(
            250,
            $count,
            'The catalogue must not exceed 250 products (US-008 requirement).',
        );
    }

    public function testAllProductsHaveFrAndEnTranslations(): void
    {
        self::bootKernel();

        /** @var ProductRepositoryInterface $productRepo */
        $productRepo = self::getContainer()->get('sylius.repository.product');

        $products = $productRepo->findAll();
        self::assertNotEmpty($products, 'No products found — run the demo fixture suite first.');

        $missingTranslations = [];
        foreach ($products as $product) {
            $code = $product->getCode() ?? '(no code)';

            $product->setCurrentLocale('fr_FR');
            $product->setFallbackLocale('fr_FR');
            if (empty($product->getName())) {
                $missingTranslations[] = \sprintf('%s: missing fr_FR name', $code);
            }

            $product->setCurrentLocale('en_US');
            $product->setFallbackLocale('en_US');
            if (empty($product->getName())) {
                $missingTranslations[] = \sprintf('%s: missing en_US name', $code);
            }
        }

        self::assertEmpty(
            $missingTranslations,
            'Products with missing translations: ' . \implode(', ', \array_slice($missingTranslations, 0, 10)),
        );
    }

    public function testNoProductLabelContainsLoremIpsum(): void
    {
        self::bootKernel();

        /** @var ProductRepositoryInterface $productRepo */
        $productRepo = self::getContainer()->get('sylius.repository.product');

        $products = $productRepo->findAll();
        $flagged = [];

        $forbiddenPatterns = ['lorem', 'ipsum', 'placeholder', 'test product', 'example product'];

        foreach ($products as $product) {
            foreach (['fr_FR', 'en_US'] as $locale) {
                $product->setCurrentLocale($locale);
                $product->setFallbackLocale($locale);

                $name = \strtolower($product->getName() ?? '');
                $description = \strtolower($product->getDescription() ?? '');

                foreach ($forbiddenPatterns as $pattern) {
                    if (\str_contains($name, $pattern) || \str_contains($description, $pattern)) {
                        $flagged[] = \sprintf('%s (%s): contains "%s"', $product->getCode(), $locale, $pattern);
                    }
                }
            }
        }

        self::assertEmpty(
            $flagged,
            'Products containing placeholder text: ' . \implode('; ', \array_slice($flagged, 0, 5)),
        );
    }

    public function testCatalogRootTaxonsExist(): void
    {
        self::bootKernel();

        /** @var TaxonRepositoryInterface $taxonRepo */
        $taxonRepo = self::getContainer()->get('sylius.repository.taxon');

        $expectedCodes = [
            'catalogue',
            'epi',
            'quincaillerie',
            'fournitures-industrielles',
            'protection-tete',
            'protection-mains',
            'chaussures-securite',
            'protection-corps',
            'visserie',
            'outillage',
            'fixations',
            'emballage',
            'signaletique',
            'papeterie',
        ];

        $missing = [];
        foreach ($expectedCodes as $code) {
            if ($taxonRepo->findOneBy(['code' => $code]) === null) {
                $missing[] = $code;
            }
        }

        self::assertEmpty(
            $missing,
            'Missing taxons: ' . \implode(', ', $missing),
        );
    }
}
