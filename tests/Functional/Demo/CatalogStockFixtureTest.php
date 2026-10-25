<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** US-198 (T-198-12): the `demo` fixtures track the stock of every variant, with a few shortages and low stocks, and nothing on hold. */
final class CatalogStockFixtureTest extends KernelTestCase
{
    use DatabaseAccess;

    public function testEveryVariantOfTheCatalogueIsTracked(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM sylius_product_variant WHERE tracked = 0'));
    }

    public function testSomeVariantsAreOutOfStockButMostAreAvailable(): void
    {
        self::bootKernel();
        $total = $this->intValue('SELECT COUNT(*) FROM sylius_product_variant');
        $out = $this->intValue('SELECT COUNT(*) FROM sylius_product_variant WHERE on_hand - on_hold <= 0');

        self::assertGreaterThanOrEqual(1, $out);
        self::assertLessThanOrEqual((int) ($total * 0.10), $out);
    }

    public function testSomeVariantsHaveALowStock(): void
    {
        self::bootKernel();

        self::assertGreaterThanOrEqual(1, $this->intValue('SELECT COUNT(*) FROM sylius_product_variant WHERE on_hand BETWEEN 1 AND 9'));
    }
}
