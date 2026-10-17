<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Logistics\ContainerFillRow;
use App\Demo\Logistics\LogisticUnit;
use App\Demo\Logistics\VariantLogisticsSeeder;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusContainerFillPlugin\Entity\ContainerTypeInterface;
use Tmonier\SyliusContainerFillPlugin\Repository\ContainerTypeRepositoryInterface;

/**
 * US-048 / T-048-08: the seeder is an upsert, so a variant that no longer fits a container loses the capacity row
 * it had. Run against a database loaded with the `demo` suite; every test rolls its changes back.
 */
final class VariantLogisticsSeederTest extends KernelTestCase
{
    use DatabaseAccess;

    private const FITS = [100, 100, 100, 100];

    private const FITS_NOWHERE = [100_000, 100_000, 100_000, 5_000_000];

    protected function tearDown(): void
    {
        if (self::$booted && $this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    public function testACapacityIsRemovedWhenTheQuantityFallsBelowOne(): void
    {
        [$variant, $type] = $this->prepare();

        $this->seed($variant, $type, self::FITS);
        self::assertSame(1, $this->capacities($variant, $type), 'A product that fits has a capacity.');

        $this->seed($variant, $type, self::FITS_NOWHERE);
        self::assertSame(0, $this->capacities($variant, $type), 'The capacity of a product that no longer fits is stale and must go.');
    }

    public function testACapacityIsNotCreatedForAProductThatFitsNowhere(): void
    {
        [$variant, $type] = $this->prepare();
        $this->seed($variant, $type, self::FITS_NOWHERE);
        $this->seed($variant, $type, self::FITS_NOWHERE);

        self::assertSame(0, $this->capacities($variant, $type));
    }

    public function testSeedingTheSameRowTwiceKeepsOneCapacity(): void
    {
        [$variant, $type] = $this->prepare();
        $this->seed($variant, $type, self::FITS);
        $this->seed($variant, $type, self::FITS);

        self::assertSame(1, $this->capacities($variant, $type));
    }

    public function testABoxOrALotIsTheSoldUnitNotAPieceInACase(): void
    {
        foreach (['boîte de 100', 'lot de 25', 'rouleau de 500', 'unité'] as $packaging) {
            [$variant, $type] = $this->prepare();
            $this->seed($variant, $type, [80, 50, 50, 350], $packaging);

            $id = (int) $variant->getId();
            $column = static fn (string $name): string => 'SELECT ' . $name . ' FROM tmonier_container_variant_logistics WHERE variant_id = ?';
            self::assertSame(1, $this->intValue($column('units_per_case'), [$id]), $packaging . ': the CSV row describes one sold unit.');
            self::assertSame(350, $this->intValue($column('case_weight'), [$id]), $packaging . ': the case weighs what the sold unit weighs.');
            self::assertSame(0, $this->intValue($column('case_multiple_required'), [$id]), $packaging . ': no whole-case purchase.');
            $this->db()->rollBack();
        }
    }

    /** @return array{ProductVariantInterface, ContainerTypeInterface} */
    private function prepare(): array
    {
        self::bootKernel();
        $this->db()->beginTransaction();

        $variant = $this->entityManager()->getRepository(ProductVariantInterface::class)->findOneBy([], ['id' => 'ASC']);
        self::assertInstanceOf(ProductVariantInterface::class, $variant);
        $types = self::getContainer()->get('tmonier_container_fill.repository.container_type');
        self::assertInstanceOf(ContainerTypeRepositoryInterface::class, $types);
        $type = $types->findOneByCode('CARTON_L');
        self::assertInstanceOf(ContainerTypeInterface::class, $type);

        // Start from a variant without a row for this type.
        $this->db()->executeStatement('DELETE FROM tmonier_container_variant_capacity WHERE variant_id = ? AND container_type_id = ?', [(int) $variant->getId(), (int) $type->getId()]);
        $this->entityManager()->clear();
        $variant = $this->entityManager()->find(ProductVariantInterface::class, $variant->getId());
        $type = $this->entityManager()->find(ContainerTypeInterface::class, $type->getId());
        self::assertInstanceOf(ProductVariantInterface::class, $variant);
        self::assertInstanceOf(ContainerTypeInterface::class, $type);

        return [$variant, $type];
    }

    /** @param array{int, int, int, int} $dimensions width, height, depth (mm) and weight (g) */
    private function seed(ProductVariantInterface $variant, ContainerTypeInterface $type, array $dimensions, string $packaging = 'unité'): void
    {
        $seeder = self::getContainer()->get(VariantLogisticsSeeder::class);
        self::assertInstanceOf(VariantLogisticsSeeder::class, $seeder);

        $row = new ContainerFillRow((string) $variant->getCode(), 'visserie', $packaging, new LogisticUnit(...$dimensions));
        $seeder->seed($variant, $row, ['CARTON_L' => $type], false);
        $seeder->flush();
    }

    private function capacities(ProductVariantInterface $variant, ContainerTypeInterface $type): int
    {
        return $this->intValue('SELECT COUNT(*) FROM tmonier_container_variant_capacity WHERE variant_id = ? AND container_type_id = ?', [(int) $variant->getId(), (int) $type->getId()]);
    }

    private function entityManager(): EntityManagerInterface
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }
}
