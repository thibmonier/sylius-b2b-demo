<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\DataFixtures\ContainerFillDemoFixture;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * US-044: the container-fill data of the demo suite. Run against a database loaded with the `demo` suite
 * (app:demo:reset purges and reloads the same suite, so the counts below are those of a fresh reset).
 */
final class ContainerFillFixturesTest extends WebTestCase
{
    use DatabaseAccess;

    private const PRESET_CODES = [
        'EUR_PALLET', 'EUR_HALF_PALLET', 'UK_PALLET', 'US_PALLET', 'SEMI_13_6', 'MEGA_13_6', 'US_53FT',
        'CONTAINER_20', 'CONTAINER_40', 'CONTAINER_40HC', 'CARTON_S', 'CARTON_M', 'CARTON_L',
    ];

    private const OVERRIDE_ROLE = 'ROLE_TMONIER_CONTAINER_FILL_OVERRIDE';

    protected function tearDown(): void
    {
        if (self::$booted && $this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    public function testTheThirteenPluginPresetsAreLoadedWithFrenchAndEnglishNames(): void
    {
        self::bootKernel();

        $codes = $this->db()->fetchFirstColumn('SELECT code FROM tmonier_container_type ORDER BY code');
        $expected = self::PRESET_CODES;
        sort($expected);
        self::assertSame($expected, $codes);

        $locales = $this->db()->fetchFirstColumn('SELECT DISTINCT locale FROM tmonier_container_type_translation ORDER BY locale');
        self::assertSame(['en_US', 'fr_FR'], $locales);
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM tmonier_container_type_translation WHERE name IS NULL OR name = ''"));
    }

    public function testNoAccountOfThePluginDemoFixtureIsCreated(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM sylius_shop_user WHERE username = 'pallet@example.com'"));
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM sylius_product WHERE code LIKE 'TMONIER_CF_%'"));
    }

    public function testRulesTargetTheB2bChannelOnlyWithOneBlockingAndOneMinimumFillRule(): void
    {
        self::bootKernel();

        $rules = $this->db()->fetchAllAssociative('SELECT c.code AS channel, r.mode, r.threshold, r.enabled FROM tmonier_container_rule r JOIN sylius_channel c ON c.id = r.channel_id ORDER BY r.mode');
        self::assertCount(2, $rules);
        self::assertSame(['B2B', 'B2B'], array_column($rules, 'channel'));
        self::assertSame(['full_only', 'min_fill_percent'], array_column($rules, 'mode'));
        self::assertSame(['1', '1'], array_map(static fn (mixed $enabled): string => is_scalar($enabled) ? (string) $enabled : '', array_column($rules, 'enabled')));
    }

    public function testEveryVariantHasLogisticsAndAPalletCapacityAndTheEurPalletPlansByLayers(): void
    {
        self::bootKernel();

        self::assertSame(
            $this->intValue('SELECT COUNT(*) FROM sylius_product_variant'),
            $this->intValue('SELECT COUNT(*) FROM tmonier_container_variant_logistics'),
        );
        self::assertSame(
            $this->intValue('SELECT COUNT(*) FROM sylius_product_variant'),
            $this->intValue("SELECT COUNT(*) FROM tmonier_container_variant_capacity cap JOIN tmonier_container_type t ON t.id = cap.container_type_id WHERE t.code = 'EUR_PALLET'"),
        );
        self::assertSame(1, $this->intValue("SELECT layer_mode FROM tmonier_container_type WHERE code = 'EUR_PALLET'"));
        self::assertGreaterThan(0, $this->intValue('SELECT COUNT(*) FROM tmonier_container_variant_capacity WHERE units_per_layer IS NOT NULL'));
    }

    public function testNoCapacityIsDuplicatedForAVariantAndAContainerType(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM (SELECT 1 FROM tmonier_container_variant_capacity GROUP BY variant_id, container_type_id HAVING COUNT(*) > 1) d'));
    }

    public function testTheOverrideRoleIsGrantedToNoAdministrator(): void
    {
        self::bootKernel();
        $hierarchy = self::getContainer()->get('security.role_hierarchy');
        self::assertInstanceOf(RoleHierarchyInterface::class, $hierarchy);

        $rolesOfAdministrators = $this->db()->fetchFirstColumn('SELECT roles FROM sylius_admin_user');
        self::assertNotSame([], $rolesOfAdministrators);
        foreach ($rolesOfAdministrators as $serialized) {
            $roles = json_decode(is_string($serialized) ? $serialized : '[]', true);
            self::assertIsArray($roles);
            $roles = array_values(array_filter($roles, \is_string(...)));
            self::assertNotContains(self::OVERRIDE_ROLE, $roles);
            self::assertNotContains(self::OVERRIDE_ROLE, $hierarchy->getReachableRoleNames([...$roles, 'ROLE_ADMINISTRATION_ACCESS', 'ROLE_DEMO_ADMIN']));
        }
    }

    public function testLoadingTheFixtureAgainChangesNoCount(): void
    {
        self::bootKernel();
        $fixture = self::getContainer()->get(ContainerFillDemoFixture::class);
        self::assertInstanceOf(ContainerFillDemoFixture::class, $fixture);
        $this->db()->beginTransaction();
        $before = $this->counts();

        $started = hrtime(true);
        $fixture->load([]);
        $fixture->load([]);
        $milliseconds = (int) ((hrtime(true) - $started) / 1_000_000);
        fwrite(\STDERR, sprintf("\n[US-044] container-fill fixture, two runs on a loaded database: %d ms\n", $milliseconds));

        self::assertSame($before, $this->counts());
        self::assertSame(['types' => 13, 'rules' => 2], array_intersect_key($before, ['types' => 1, 'rules' => 1]));
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        return [
            'types' => $this->intValue('SELECT COUNT(*) FROM tmonier_container_type'),
            'logistics' => $this->intValue('SELECT COUNT(*) FROM tmonier_container_variant_logistics'),
            'capacities' => $this->intValue('SELECT COUNT(*) FROM tmonier_container_variant_capacity'),
            'rules' => $this->intValue('SELECT COUNT(*) FROM tmonier_container_rule'),
            'rule_types' => $this->intValue('SELECT COUNT(*) FROM tmonier_container_rule_type'),
        ];
    }
}
