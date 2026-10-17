<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\DataFixtures\ContainerFillDemoFixture;
use App\Tests\Support\QueryCounter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-048 / T-048-06: the container-fill fixture does not look rows up one by one. The targets come from the profile
 * of docs/remise-a-zero-demo.md (was 1 211 SELECT and 993 INSERT on a purged database), and the data it writes
 * does not change. Run against a database loaded with the `demo` suite; the test rolls its changes back.
 */
final class ContainerFillFixtureQueryCountTest extends KernelTestCase
{
    use DatabaseAccess;

    private const MAX_SELECT = 40;

    private const MAX_INSERT = 60;

    protected function tearDown(): void
    {
        if (self::$booted && $this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    public function testALoadAfterAPurgeSendsFewStatementsAndWritesTheSameData(): void
    {
        self::bootKernel();
        $fixture = self::getContainer()->get(ContainerFillDemoFixture::class);
        self::assertInstanceOf(ContainerFillDemoFixture::class, $fixture);
        $this->db()->beginTransaction();
        $expected = $this->snapshot();
        self::assertNotSame([], $expected['capacities']);

        // What the nightly reset starts from: the purger has emptied the tables.
        $this->db()->executeStatement('DELETE FROM tmonier_container_variant_capacity');
        $this->db()->executeStatement('DELETE FROM tmonier_container_variant_logistics');
        $this->entityManager()->clear();

        QueryCounter::reset();
        $fixture->load([]);
        $kinds = $this->kinds(QueryCounter::statements());

        self::assertLessThanOrEqual(self::MAX_SELECT, $kinds['select'], 'SELECT statements: ' . json_encode($kinds));
        self::assertLessThanOrEqual(self::MAX_INSERT, $kinds['insert'], 'INSERT statements: ' . json_encode($kinds));
        self::assertSame($expected, $this->snapshot(), 'The fixture writes the same logistics and capacities.');
    }

    public function testASecondLoadOnLoadedDataChangesNothingAndStaysCheap(): void
    {
        self::bootKernel();
        $fixture = self::getContainer()->get(ContainerFillDemoFixture::class);
        self::assertInstanceOf(ContainerFillDemoFixture::class, $fixture);
        $this->db()->beginTransaction();
        $expected = $this->snapshot();

        QueryCounter::reset();
        $fixture->load([]);
        $kinds = $this->kinds(QueryCounter::statements());

        self::assertLessThanOrEqual(self::MAX_SELECT, $kinds['select'], 'SELECT statements: ' . json_encode($kinds));
        self::assertSame(0, $kinds['insert'], 'Nothing to insert: ' . json_encode($kinds));
        self::assertSame($expected, $this->snapshot());
    }

    /** @return array{logistics: list<array<string, mixed>>, capacities: list<array<string, mixed>>} */
    private function snapshot(): array
    {
        return [
            'logistics' => $this->db()->fetchAllAssociative(
                'SELECT v.code, l.units_per_case, l.case_weight, l.stackable, l.layer_height, l.own_pallet, l.case_multiple_required
                 FROM tmonier_container_variant_logistics l JOIN sylius_product_variant v ON v.id = l.variant_id ORDER BY v.code',
            ),
            'capacities' => $this->db()->fetchAllAssociative(
                'SELECT v.code, t.code AS type, c.quantity, c.units_per_layer
                 FROM tmonier_container_variant_capacity c
                 JOIN sylius_product_variant v ON v.id = c.variant_id JOIN tmonier_container_type t ON t.id = c.container_type_id
                 ORDER BY v.code, t.code',
            ),
        ];
    }

    /**
     * @param list<string> $statements
     *
     * @return array{select: int, insert: int, update: int, delete: int}
     */
    private function kinds(array $statements): array
    {
        $kinds = ['select' => 0, 'insert' => 0, 'update' => 0, 'delete' => 0];
        foreach ($statements as $sql) {
            $kind = strtolower(strtok(ltrim($sql), " \t\n(") ?: '');
            if (isset($kinds[$kind])) {
                ++$kinds[$kind];
            }
        }

        return $kinds;
    }

    private function entityManager(): EntityManagerInterface
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }
}
