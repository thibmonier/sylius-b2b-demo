<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Demo\Account\DemoAccountCatalog;
use App\Tests\Support\Typed;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Latent findings of the sprint 2 review (S2-R8 to S2-R10), each checked on the loaded demo data. A check that fails
 * is a bug to fix; a check that passes records the finding as closed.
 */
final class FixturesConsistencyTest extends WebTestCase
{
    /**
     * The fixtures that share a priority, as verified by loading the suite with every group in the reverse order:
     * same exit status and same row count in all 157 tables. A new fixture in a group, or a new group, fails this
     * test until its independence from the others is checked again (then update the list).
     */
    private const VERIFIED_SHARED_PRIORITIES = [
        -5 => ['demo_po_reference_channel', 'tmonier_container_fill_container_types'],
        -15 => ['demo_companies', 'demo_customers'],
        -40 => ['demo_trade_credit', 'demo_tier_pricing', 'demo_purchase_approval', 'demo_company_wishlists', 'demo_company_addresses'],
        -50 => ['demo_quotes', 'demo_gpsr', 'demo_punchout', 'tmonier_agentic_feeds', 'demo_digital_passports'],
        -60 => ['demo_orders', 'demo_hidden_prices_feeds'],
    ];

    /** S2-R8: the accounts page and the loaded accounts both come from the catalogue, so they cannot drift apart. */
    public function testEveryCatalogueAccountIsLoadedAndListedOnTheAccountsPage(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/accounts');
        self::assertResponseIsSuccessful();
        $page = (string) $client->getResponse()->getContent();
        $connection = $client->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);
        $catalog = $client->getContainer()->get(DemoAccountCatalog::class);
        self::assertInstanceOf(DemoAccountCatalog::class, $catalog);

        $missing = [];
        foreach ($catalog->shopAccounts() as $account) {
            if (1 !== $this->rowsWhere($connection, 'sylius_shop_user', 'username', $account->email) || !str_contains($page, $account->email)) {
                $missing[] = $account->email;
            }
        }
        foreach ($catalog->adminUsers() as $account) {
            if (1 !== $this->rowsWhere($connection, 'sylius_admin_user', 'email', $account->email) || !str_contains($page, $account->email)) {
                $missing[] = $account->email;
            }
        }

        self::assertSame([], $missing, 'Accounts of the catalogue that are not loaded or not listed on /demo/accounts.');
    }

    /** S2-R9: the fixtures with the same priority are independent (verified); the groups must not change unnoticed. */
    public function testFixturesSharingAPriorityAreTheVerifiedOnes(): void
    {
        $groups = [];
        foreach ($this->fixturePriorities() as $name => $priority) {
            $groups[$priority][] = $name;
        }
        $shared = array_filter($groups, static fn (array $names, int $priority): bool => $priority < 0 && \count($names) > 1, \ARRAY_FILTER_USE_BOTH);
        ksort($shared);
        $verified = self::VERIFIED_SHARED_PRIORITIES;
        ksort($verified);
        foreach ($shared as &$names) {
            sort($names);
        }
        unset($names);
        foreach ($verified as &$names) {
            sort($names);
        }
        unset($names);

        self::assertSame($verified, $shared, 'Fixtures sharing a priority changed: check that they are still independent, then update the verified list.');
    }

    /** S2-R10: the demo attributes are translatable, so one value per locale is the right shape; otherwise a single one. */
    public function testAttributeValuesMatchTheTranslatableFlagOfTheirAttribute(): void
    {
        $connection = self::createClient()->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        $wrong = $connection->fetchFirstColumn(
            'SELECT a.code FROM sylius_product_attribute a
             JOIN sylius_product_attribute_value v ON v.attribute_id = a.id
             GROUP BY a.id, a.code, a.translatable
             HAVING (a.translatable = 1 AND COUNT(DISTINCT v.locale_code) < 2)
                 OR (a.translatable = 0 AND COUNT(DISTINCT v.locale_code) > 1)',
        );

        self::assertSame([], $wrong, 'Attributes whose values do not match their translatable flag.');
    }

    /** @return array<string, int> priority of each fixture of the demo suite (0 when none is configured) */
    private function fixturePriorities(): array
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 2) . '/config/packages/demo_fixtures.yaml');
        self::assertIsArray($config);
        $fixtures = Typed::at($config, 'sylius_fixtures', 'suites', 'demo', 'fixtures');
        self::assertIsArray($fixtures);

        $priorities = [];
        foreach ($fixtures as $name => $definition) {
            $priority = \is_array($definition) ? ($definition['priority'] ?? 0) : 0;
            self::assertIsInt($priority);
            $priorities[(string) $name] = $priority;
        }

        return $priorities;
    }

    private function rowsWhere(Connection $connection, string $table, string $column, string $value): int
    {
        $count = $connection->fetchOne(\sprintf('SELECT COUNT(*) FROM %s WHERE %s = ?', $table, $column), [$value]);
        self::assertIsNumeric($count);

        return (int) $count;
    }
}
