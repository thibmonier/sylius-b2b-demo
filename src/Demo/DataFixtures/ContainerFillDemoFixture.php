<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Infrastructure\Csv\CsvProductReader;
use App\Demo\Logistics\ContainerFillRow;
use App\Demo\Logistics\VariantLogisticsSeeder;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusContainerFillPlugin\Entity\ContainerRuleInterface;
use Tmonier\SyliusContainerFillPlugin\Entity\ContainerTypeInterface;
use Tmonier\SyliusContainerFillPlugin\Repository\ContainerTypeRepositoryInterface;

/**
 * US-044: container-fill data of the demo, derived from the catalogue CSV (same files as demo_products_csv).
 * Needs the products (-30) and the plugin presets (tmonier_container_fill_container_types, -5). It runs last
 * (-70, after demo_orders at -60): the demo orders complete through the plugin's workflow guard, which would
 * refuse the order history (a few packs, no full pallet) as soon as the B2B rules exist.
 *
 * Rules apply to the B2B channel only; the B2C channel has none, so no gauge shows there.
 */
final class ContainerFillDemoFixture extends AbstractFixture
{
    private const CSV_FILES = ['epi.csv', 'quincaillerie.csv', 'fournitures.csv'];

    /** Container types holding catalogue products; the EUR pallet is also the layer type. */
    private const CAPACITY_TYPES = ['EUR_PALLET', 'CARTON_S', 'CARTON_M', 'CARTON_L'];

    /** Families whose pallets are planned by layers. */
    private const LAYER_FAMILIES = ['emballage'];

    private const CHANNEL_CODE = 'B2B';

    private const MIN_FILL_PERCENT = 20;

    /**
     * @param RepositoryInterface<ChannelInterface>        $channelRepository
     * @param RepositoryInterface<TaxonInterface>          $taxonRepository
     * @param RepositoryInterface<ProductVariantInterface> $variantRepository
     * @param RepositoryInterface<ContainerRuleInterface>  $ruleRepository
     * @param FactoryInterface<ContainerRuleInterface>     $ruleFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CsvProductReader $csvProductReader,
        private readonly VariantLogisticsSeeder $seeder,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(service: 'tmonier_container_fill.repository.container_type')]
        private readonly ContainerTypeRepositoryInterface $containerTypeRepository,
        #[Autowire(service: 'tmonier_container_fill.repository.container_rule')]
        private readonly RepositoryInterface $ruleRepository,
        #[Autowire(service: 'tmonier_container_fill.factory.container_rule')]
        private readonly FactoryInterface $ruleFactory,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.taxon')]
        private readonly RepositoryInterface $taxonRepository,
        #[Autowire(service: 'sylius.repository.product_variant')]
        private readonly RepositoryInterface $variantRepository,
    ) {
    }

    public function getName(): string
    {
        return 'demo_container_fill';
    }

    public function load(array $options): void
    {
        $types = $this->capacityTypes();
        $types['EUR_PALLET']->setLayerMode(true);

        foreach ($this->rows() as $row) {
            $variant = $this->variantRepository->findOneBy(['code' => $row->sku]);
            if ($variant instanceof ProductVariantInterface) {
                $this->seeder->seed($variant, $row, $types, \in_array($row->taxon, self::LAYER_FAMILIES, true));
            }
        }

        $this->entityManager->flush();

        $this->seedRules();
        $this->entityManager->flush();
    }

    /** @return iterable<ContainerFillRow> */
    private function rows(): iterable
    {
        $csvDir = $this->projectDir . '/fixtures/csv/';
        $files = \array_map(static fn (string $name): string => $csvDir . $name, self::CSV_FILES);

        foreach ($this->csvProductReader->readAll($files) as $rows) {
            foreach ($rows as $row) {
                yield ContainerFillRow::fromCsv($row);
            }
        }
    }

    private function seedRules(): void
    {
        $this->seedRule(
            'Emballage : palettes complètes ou camion complet (Packaging: full pallets or full truck)',
            'emballage',
            ContainerRuleInterface::MODE_FULL_ONLY,
            null,
            ['EUR_PALLET', 'SEMI_13_6'],
        );
        $this->seedRule(
            'Visserie : cartons remplis à 20 % au moins (Fasteners: cartons at least 20 % full)',
            'visserie',
            ContainerRuleInterface::MODE_MIN_FILL_PERCENT,
            self::MIN_FILL_PERCENT,
            ['CARTON_S', 'CARTON_M', 'CARTON_L'],
        );
    }

    /** @param list<string> $typeCodes */
    private function seedRule(string $name, string $taxonCode, string $mode, ?int $threshold, array $typeCodes): void
    {
        $channel = $this->channelRepository->findOneBy(['code' => self::CHANNEL_CODE]);
        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if (null === $channel || null === $taxon) {
            throw new \RuntimeException(\sprintf('Channel "%s" and taxon "%s" must exist before the container-fill rules.', self::CHANNEL_CODE, $taxonCode));
        }

        $rule = $this->ruleRepository->findOneBy(['name' => $name, 'channel' => $channel]);
        if (!$rule instanceof ContainerRuleInterface) {
            $rule = $this->ruleFactory->createNew();
            $rule->setName($name);
            $rule->setChannel($channel);
            $this->entityManager->persist($rule);
        }

        $rule->setScope(ContainerRuleInterface::SCOPE_TAXON);
        $rule->setTaxon($taxon);
        $rule->setMode($mode);
        $rule->setThreshold($threshold);
        $rule->setEnabled(true);
        foreach ($rule->getContainerTypes()->toArray() as $current) {
            $rule->removeContainerType($current);
        }
        foreach ($typeCodes as $code) {
            $rule->addContainerType($this->type($code));
        }
    }

    /** @return array<string, ContainerTypeInterface> */
    private function capacityTypes(): array
    {
        $types = [];
        foreach (self::CAPACITY_TYPES as $code) {
            $types[$code] = $this->type($code);
        }

        return $types;
    }

    private function type(string $code): ContainerTypeInterface
    {
        return $this->containerTypeRepository->findOneByCode($code)
            ?? throw new \RuntimeException(\sprintf('Container type "%s" is missing: load "tmonier_container_fill_container_types" first.', $code));
    }
}
