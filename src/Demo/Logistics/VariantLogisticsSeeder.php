<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusContainerFillPlugin\Entity\ContainerTypeInterface;
use Tmonier\SyliusContainerFillPlugin\Entity\ProductVariantLogisticsInterface;
use Tmonier\SyliusContainerFillPlugin\Entity\VariantContainerCapacityInterface;

/**
 * Writes the container-fill data of one catalogue variant (upsert: running it twice changes nothing).
 *
 * - Logistics: the CSV row (weight, size, price) describes the sold unit, a "boîte de 100" being one box and not a piece
 *   sold by cartons of 100. Nothing in the catalogue says how many sold units go in a shipping case, so a case holds one
 *   (one box = one case, weighing what the box weighs) and nothing is sold by whole cases.
 * - Capacities: one row per container type that can hold the product (see {@see ContainerCapacityCalculator}).
 * - Layers: families of the layer list get a layer height and the units per layer on the pallet.
 *
 * Built for a whole catalogue (US-048 / T-048-06): {@see self::preload()} reads the rows that exist for all variants at
 * once, the new rows are held back and {@see self::flush()} writes them with multi-row INSERTs, so the number of
 * statements does not grow with the number of variants. The mapping has no lifecycle callback, so the rows are the same
 * as the ORM would write.
 */
final class VariantLogisticsSeeder
{
    /** Sold units in one shipping case: the catalogue does not describe the case, the sold unit is the box, lot or roll itself. */
    private const UNITS_PER_CASE = 1;

    /** Variants per IN() list, and rows per multi-row INSERT (7 columns at most: well under the 65 535 parameter limit). */
    private const CHUNK = 250;

    // The rows known for the variants loaded so far, by variant id and by "variant id:type id". The ones not yet in the
    // database are in $newLogistics and $newCapacities too.
    /** @var array<int, ProductVariantLogisticsInterface> */
    private array $logistics = [];

    /** @var array<string, VariantContainerCapacityInterface> */
    private array $capacities = [];

    /** @var array<int, true> variant ids whose rows are in the maps above */
    private array $loaded = [];

    /** @var array<int, ProductVariantLogisticsInterface> */
    private array $newLogistics = [];

    /** @var array<string, VariantContainerCapacityInterface> */
    private array $newCapacities = [];

    /**
     * @param RepositoryInterface<ProductVariantLogisticsInterface> $logisticsRepository
     * @param FactoryInterface<ProductVariantLogisticsInterface>    $logisticsFactory
     * @param RepositoryInterface<VariantContainerCapacityInterface> $capacityRepository
     * @param FactoryInterface<VariantContainerCapacityInterface>    $capacityFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContainerCapacityCalculator $calculator,
        #[Autowire(service: 'tmonier_container_fill.repository.variant_logistics')]
        private readonly RepositoryInterface $logisticsRepository,
        #[Autowire(service: 'tmonier_container_fill.factory.variant_logistics')]
        private readonly FactoryInterface $logisticsFactory,
        #[Autowire(service: 'tmonier_container_fill.repository.variant_capacity')]
        private readonly RepositoryInterface $capacityRepository,
        #[Autowire(service: 'tmonier_container_fill.factory.variant_capacity')]
        private readonly FactoryInterface $capacityFactory,
    ) {
    }

    /**
     * Reads the existing rows of all these variants in a few queries, instead of one lookup per row.
     *
     * @param iterable<ProductVariantInterface> $variants
     */
    public function preload(iterable $variants): void
    {
        $this->reset();
        $this->load($variants);
    }

    /** Writes what {@see self::seed()} prepared: updates and removals through the ORM, new rows with multi-row INSERTs. */
    public function flush(): void
    {
        $this->entityManager->flush();
        $this->insert($this->newLogistics, [
            'units_per_case' => static fn (ProductVariantLogisticsInterface $row): int => $row->getUnitsPerCase(),
            'case_weight' => static fn (ProductVariantLogisticsInterface $row): ?int => $row->getCaseWeight(),
            'stackable' => static fn (ProductVariantLogisticsInterface $row): int => (int) $row->isStackable(),
            'layer_height' => static fn (ProductVariantLogisticsInterface $row): ?int => $row->getLayerHeight(),
            'own_pallet' => static fn (ProductVariantLogisticsInterface $row): int => (int) $row->isOwnPallet(),
            'case_multiple_required' => static fn (ProductVariantLogisticsInterface $row): int => (int) $row->isCaseMultipleRequired(),
            'variant_id' => static fn (ProductVariantLogisticsInterface $row): mixed => $row->getVariant()?->getId(),
        ]);
        $this->insert($this->newCapacities, [
            'quantity' => static fn (VariantContainerCapacityInterface $row): int => $row->getQuantity(),
            'units_per_layer' => static fn (VariantContainerCapacityInterface $row): ?int => $row->getUnitsPerLayer(),
            'variant_id' => static fn (VariantContainerCapacityInterface $row): mixed => $row->getVariant()?->getId(),
            'container_type_id' => static fn (VariantContainerCapacityInterface $row): mixed => $row->getContainerType()?->getId(),
        ]);
        $this->reset();
    }

    /**
     * @param array<string, ContainerTypeInterface> $capacityTypes container types to fill, by code (the pallet is the layer type)
     */
    public function seed(ProductVariantInterface $variant, ContainerFillRow $row, array $capacityTypes, bool $layers): void
    {
        $unit = $row->unit;
        $this->load([$variant]);
        $this->seedLogistics($variant, $row, $layers);

        foreach ($capacityTypes as $type) {
            $space = $this->spaceOf($type);
            $quantity = $this->calculator->capacity($unit, $space);
            if ($quantity < 1) {
                $this->removeCapacity($variant, $type);

                continue;
            }
            $perLayer = $layers && ContainerFillRow::LAYER_TYPE === $type->getCode() ? $this->calculator->unitsPerLayer($unit, $space) : 0;
            $this->seedCapacity($variant, $type, $quantity, $perLayer > 0 ? $perLayer : null);
        }
    }

    private function seedLogistics(ProductVariantInterface $variant, ContainerFillRow $row, bool $layers): void
    {
        $logistics = $this->logistics[(int) $variant->getId()] ?? null;
        if (!$logistics instanceof ProductVariantLogisticsInterface) {
            $logistics = $this->logisticsFactory->createNew();
            $logistics->setVariant($variant);
            $this->logistics[(int) $variant->getId()] = $logistics;
            $this->newLogistics[(int) $variant->getId()] = $logistics;
        }

        $logistics->setUnitsPerCase(self::UNITS_PER_CASE);
        $logistics->setCaseWeight($row->unit->isUsable() ? $row->unit->weightG * self::UNITS_PER_CASE : null);
        $logistics->setCaseMultipleRequired(false);
        $logistics->setStackable(true);
        $logistics->setLayerHeight($layers && $row->unit->isUsable() ? $row->unit->heightMm : null);
    }

    private function seedCapacity(ProductVariantInterface $variant, ContainerTypeInterface $type, int $quantity, ?int $unitsPerLayer): void
    {
        $key = $this->capacityKey($variant, $type);
        $capacity = $this->capacities[$key] ?? null;
        if (!$capacity instanceof VariantContainerCapacityInterface) {
            $capacity = $this->capacityFactory->createNew();
            $capacity->setVariant($variant);
            $capacity->setContainerType($type);
            $this->capacities[$key] = $capacity;
            $this->newCapacities[$key] = $capacity;
        }

        $capacity->setQuantity($quantity);
        $capacity->setUnitsPerLayer($unitsPerLayer);
    }

    /** A product that fits nowhere has no capacity: the row an earlier run wrote for that container type is stale. */
    private function removeCapacity(ProductVariantInterface $variant, ContainerTypeInterface $type): void
    {
        $key = $this->capacityKey($variant, $type);
        $capacity = $this->capacities[$key] ?? null;
        if (!$capacity instanceof VariantContainerCapacityInterface) {
            return;
        }

        if (!isset($this->newCapacities[$key])) {
            $this->entityManager->remove($capacity);
        }
        unset($this->capacities[$key], $this->newCapacities[$key]);
    }

    private function capacityKey(?ProductVariantInterface $variant, ?ContainerTypeInterface $type): string
    {
        return $variant?->getId() . ':' . $type?->getId();
    }

    /** @param iterable<ProductVariantInterface> $variants */
    private function load(iterable $variants): void
    {
        $missing = [];
        foreach ($variants as $variant) {
            $id = (int) $variant->getId();
            if (!isset($this->loaded[$id])) {
                $this->loaded[$id] = true;
                $missing[] = $variant;
            }
        }

        foreach (\array_chunk($missing, self::CHUNK) as $chunk) {
            foreach ($this->logisticsRepository->findBy(['variant' => $chunk]) as $logistics) {
                $this->logistics[(int) $logistics->getVariant()?->getId()] = $logistics;
            }
            foreach ($this->capacityRepository->findBy(['variant' => $chunk]) as $capacity) {
                $this->capacities[$this->capacityKey($capacity->getVariant(), $capacity->getContainerType())] = $capacity;
            }
        }
    }

    /**
     * @template T of object
     *
     * @param array<array-key, T>                   $rows
     * @param array<string, \Closure(T): mixed>     $columns column name => value of the row
     */
    private function insert(array $rows, array $columns): void
    {
        if ([] === $rows) {
            return;
        }

        $table = $this->entityManager->getClassMetadata(\reset($rows)::class)->getTableName();
        $names = \implode(', ', \array_keys($columns));
        $tuple = '(' . \implode(', ', \array_fill(0, \count($columns), '?')) . ')';
        foreach (\array_chunk(\array_values($rows), self::CHUNK) as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                foreach ($columns as $value) {
                    $values[] = $value($row);
                }
            }
            $this->entityManager->getConnection()->executeStatement(
                \sprintf('INSERT INTO %s (%s) VALUES %s', $table, $names, \implode(', ', \array_fill(0, \count($chunk), $tuple))),
                $values,
            );
        }
    }

    private function reset(): void
    {
        $this->logistics = $this->capacities = $this->loaded = $this->newLogistics = $this->newCapacities = [];
    }

    private function spaceOf(ContainerTypeInterface $type): ContainerSpace
    {
        return new ContainerSpace(
            (int) $type->getInnerLength(),
            (int) $type->getInnerWidth(),
            (int) ($type->getMaxLoadHeight() ?? $type->getInnerHeight()),
            $type->getMaxPayload(),
            $type->getVolumeEfficiency(),
        );
    }
}
