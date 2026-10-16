<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Category pages list products (regression: the CSV fixture only set the main
 * taxon, and shop listings query product_taxon, so every category was empty).
 * Requires a database loaded with the `demo` fixture suite.
 */
final class TaxonListingSmokeTest extends WebTestCase
{
    public function testEveryCatalogueProductIsAssignedToItsCategory(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        $unassigned = $connection->fetchOne(
            'SELECT COUNT(*) FROM sylius_product p
             WHERE p.main_taxon_id IS NOT NULL
               AND NOT EXISTS (
                   SELECT 1 FROM sylius_product_taxon pt
                   WHERE pt.product_id = p.id AND pt.taxon_id = p.main_taxon_id
               )',
        );

        self::assertIsNumeric($unassigned);
        self::assertSame(0, (int) $unassigned, 'Each product must be listed under its main taxon.');
    }

    public function testACategoryPageListsItsProducts(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/taxons/protection-tete');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(
            0,
            $client->getCrawler()->filter('[data-test-product]')->count()
                + substr_count((string) $client->getResponse()->getContent(), 'EPI-CASK-'),
            'The head-protection category must list its helmets.',
        );
    }
}
