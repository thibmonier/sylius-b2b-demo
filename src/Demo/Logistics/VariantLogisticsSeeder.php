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
 * - Logistics: the case holds the units stated by `packaging_unit`; selling by whole cases is required
 *   for boxes and lots of 50 to 100 units only, so the small packs stay buyable one by one.
 * - Capacities: one row per container type that can hold the product (see {@see ContainerCapacityCalculator}).
 * - Layers: families of the layer list get a layer height and the units per layer on the pallet.
 */
final class VariantLogisticsSeeder
{
    private const MIN_REQUIRED_CASE = 50;

    private const MAX_REQUIRED_CASE = 100;

    /**
     * @param RepositoryInterface<ProductVariantLogisticsInterface> $logisticsRepository
     * @param FactoryInterface<ProductVariantLogisticsInterface>    $logisticsFactory
     * @param RepositoryInterface<VariantContainerCapacityInterface> $capacityRepository
     * @param FactoryInterface<VariantContainerCapacityInterface>    $capacityFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContainerCapacityCalculator $calculator,
        private readonly PackagingUnitParser $packagingParser,
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
     * @param array<string, ContainerTypeInterface> $capacityTypes container types to fill, by code (the pallet is the layer type)
     */
    public function seed(ProductVariantInterface $variant, ContainerFillRow $row, array $capacityTypes, bool $layers): void
    {
        $unit = $row->unit;
        $this->seedLogistics($variant, $row, $layers);

        foreach ($capacityTypes as $type) {
            $space = $this->spaceOf($type);
            $quantity = $this->calculator->capacity($unit, $space);
            if ($quantity < 1) {
                continue;
            }
            $perLayer = $layers && ContainerFillRow::LAYER_TYPE === $type->getCode() ? $this->calculator->unitsPerLayer($unit, $space) : 0;
            $this->seedCapacity($variant, $type, $quantity, $perLayer > 0 ? $perLayer : null);
        }
    }

    private function seedLogistics(ProductVariantInterface $variant, ContainerFillRow $row, bool $layers): void
    {
        $logistics = $this->logisticsRepository->findOneBy(['variant' => $variant]);
        if (!$logistics instanceof ProductVariantLogisticsInterface) {
            $logistics = $this->logisticsFactory->createNew();
            $logistics->setVariant($variant);
            $this->entityManager->persist($logistics);
        }

        $unitsPerCase = $this->packagingParser->unitsPerCase($row->packagingUnit);
        $logistics->setUnitsPerCase($unitsPerCase);
        $logistics->setCaseWeight($row->unit->isUsable() ? $row->unit->weightG * $unitsPerCase : null);
        $logistics->setCaseMultipleRequired($this->isSoldByWholeCases($row->packagingUnit, $unitsPerCase));
        $logistics->setStackable(true);
        $logistics->setLayerHeight($layers && $row->unit->isUsable() ? $row->unit->heightMm : null);
    }

    private function isSoldByWholeCases(?string $packagingUnit, int $unitsPerCase): bool
    {
        return PackagingUnitParser::KIND_OTHER !== $this->packagingParser->kind($packagingUnit) &&
            $unitsPerCase >= self::MIN_REQUIRED_CASE &&
            $unitsPerCase <= self::MAX_REQUIRED_CASE;
    }

    private function seedCapacity(ProductVariantInterface $variant, ContainerTypeInterface $type, int $quantity, ?int $unitsPerLayer): void
    {
        $capacity = $this->capacityRepository->findOneBy(['variant' => $variant, 'containerType' => $type]);
        if (!$capacity instanceof VariantContainerCapacityInterface) {
            $capacity = $this->capacityFactory->createNew();
            $capacity->setVariant($variant);
            $capacity->setContainerType($type);
            $this->entityManager->persist($capacity);
        }

        $capacity->setQuantity($quantity);
        $capacity->setUnitsPerLayer($unitsPerLayer);
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
