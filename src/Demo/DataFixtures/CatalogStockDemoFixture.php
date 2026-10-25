<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\DBAL\Connection;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;

/**
 * US-198 (T-198-12): the stock of the catalogue is tracked, so the product list can say "in stock", "low" or "on order" (the cards only
 * show the stock of a tracked variant). Quantities come from {@see DemoStockLevel}, a function of the SKU: no random draw.
 *
 * Runs after the order history (-60), the carts and the draft orders (-72): the variants are tracked from here on, the orders already
 * placed neither held nor sold any stock (nothing is on hold). Before the ERP exchange (-90), whose stock file carries these quantities.
 * Priority: -85.
 */
final class CatalogStockDemoFixture extends AbstractFixture
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function load(array $options): void
    {
        /** @var list<string> $codes */
        $codes = $this->connection->fetchFirstColumn('SELECT code FROM sylius_product_variant');
        foreach ($codes as $code) {
            $this->connection->executeStatement(
                'UPDATE sylius_product_variant SET tracked = 1, on_hand = ?, on_hold = 0 WHERE code = ?',
                [DemoStockLevel::forSku($code), $code],
            );
        }
    }

    public function getName(): string
    {
        return 'demo_catalog_stock';
    }
}
