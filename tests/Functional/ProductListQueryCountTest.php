<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\QueryCounter;
use Doctrine\DBAL\Connection;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * S4-R3: the number of SQL statements of a product list (cards, "from" prices included) does not depend on the
 * number of products it shows, and the "from" prices do not change with the page size.
 */
final class ProductListQueryCountTest extends WebTestCase
{
    private const LIMITS = [5, 20, 50];

    public function testQueryCountDoesNotGrowWithTheNumberOfProducts(): void
    {
        $counts = [];
        foreach (self::LIMITS as $limit) {
            $client = $this->signedInClient();
            $client->request('GET', $this->url($limit));
            QueryCounter::reset();
            $client->request('GET', $this->url($limit));
            self::assertResponseIsSuccessful();
            // US-048 / T-048-07: the wishlist lookups of the cards are counted too (they were the one N+1 left).
            $counts[$limit] = QueryCounter::count();
            self::ensureKernelShutdown();
        }

        self::assertSame($counts[5], $counts[20], 'Statements for 5 and 20 products: ' . json_encode($counts));
        self::assertSame($counts[20], $counts[50], 'Statements for 20 and 50 products: ' . json_encode($counts));
    }

    /**
     * Claire has a starting cart of 5 lines (US-096). The header loads each line's variant, product, translation and
     * images one by one (Sylius core cart query, no fetch-join: overriding the core repository for a demo is not worth
     * it). The cost is bounded by the lines of the cart (4 statements each), not by the page size.
     */
    public function testACartAddsAtMostFourStatementsPerLineWhateverThePageSize(): void
    {
        $counts = [];
        foreach ([5, 50] as $limit) {
            $client = $this->signedInClient('claire@acme.example');
            $client->request('GET', $this->url($limit));
            QueryCounter::reset();
            $client->request('GET', $this->url($limit));
            self::assertResponseIsSuccessful();
            $counts[$limit] = QueryCounter::count();
            self::ensureKernelShutdown();
        }

        self::assertLessThanOrEqual(5 * 4, abs($counts[5] - $counts[50]), 'Statements for 5 and 50 products: ' . json_encode($counts));
    }

    public function testFromPricesDoNotDependOnThePageSize(): void
    {
        $byLimit = [];
        foreach (self::LIMITS as $limit) {
            $client = $this->signedInClient();
            $client->request('GET', $this->url($limit));
            self::assertResponseIsSuccessful();
            $byLimit[$limit] = $this->fromPricesBySku((string) $client->getResponse()->getContent());
            self::ensureKernelShutdown();
        }

        self::assertNotEmpty($byLimit[50], 'The first 50 products of the catalogue include products with volume tiers.');
        foreach ($byLimit[5] + $byLimit[20] as $sku => $price) {
            self::assertSame($price, $byLimit[50][$sku] ?? null, 'The "from" price of ' . $sku . ' differs between page sizes.');
        }
    }

    private function url(int $limit): string
    {
        return '/fr_FR/taxons/catalogue?limit=' . $limit;
    }

    /**
     * Isabelle, in the Acme group like Claire: Claire has a starting cart (US-096), whose lines the header loads one by
     * one when they are not among the listed products, which would make the count depend on the page size.
     */
    private function signedInClient(string $email = 'isabelle@acme.example'): KernelBrowser
    {
        $client = self::createClient();
        $connection = $client->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);
        $id = $connection->fetchOne('SELECT u.id FROM sylius_shop_user u JOIN sylius_customer c ON c.id = u.customer_id WHERE c.email = ?', [$email]);
        $repository = $client->getContainer()->get('sylius.repository.shop_user');
        self::assertInstanceOf(RepositoryInterface::class, $repository);
        $user = $repository->find($id);
        self::assertInstanceOf(ShopUserInterface::class, $user);
        $client->loginUser($user, 'shop');

        return $client;
    }

    /** @return array<string, string> "from" price text by product reference, for the cards that show one */
    private function fromPricesBySku(string $html): array
    {
        $prices = [];
        $sku = null;
        preg_match_all('/data-test-b2b-product-(sku|from-price)="">([^<]*)</u', $html, $matches, \PREG_SET_ORDER);
        foreach ($matches as [, $kind, $text]) {
            if ('sku' === $kind) {
                $sku = trim($text);

                continue;
            }
            self::assertNotNull($sku);
            $prices[$sku] = trim($text);
        }

        return $prices;
    }
}
