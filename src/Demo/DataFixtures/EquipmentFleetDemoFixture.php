<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\CustomerAsset;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModel;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModelInterface;

/**
 * Equipment models of the customer-asset-management plugin, each compatible with a few variants of the catalogue
 * (tools and fasteners), and the fleet of Claire (buyer of Acme Industries). The plugin shares it with the members
 * of her company. Fictional machines and serial numbers only, no real brand.
 *
 * Depends on: demo_products_csv (-30), demo_customers (-15)
 * Priority: -45 (declared in config/packages/demo_fixtures.yaml)
 */
final class EquipmentFleetDemoFixture extends AbstractFixture
{
    private const OWNER_EMAIL = 'claire@acme.example';

    private const LOCALES = ['fr_FR', 'en_US'];

    /**
     * Model code => [names per locale, variant codes of the catalogue CSV the model is compatible with].
     * The tools overlap between the forklift and the pallet truck, so that a filter on one machine differs from the whole fleet.
     */
    public const MODELS = [
        'forklift-c20' => [
            'names' => ['fr_FR' => 'Chariot élévateur C20', 'en_US' => 'C20 forklift truck'],
            'variants' => ['OUTI-001', 'OUTI-002', 'OUTI-003', 'OUTI-004', 'OUTI-005', 'OUTI-006', 'OUTI-007', 'OUTI-008', 'OUTI-009', 'OUTI-010', 'OUTI-011', 'OUTI-012'],
        ],
        'pallet-truck-t2' => [
            'names' => ['fr_FR' => 'Transpalette électrique T2', 'en_US' => 'T2 electric pallet truck'],
            'variants' => ['OUTI-009', 'OUTI-010', 'OUTI-011', 'OUTI-012', 'OUTI-013', 'OUTI-014', 'OUTI-015', 'OUTI-016', 'OUTI-017', 'OUTI-018'],
        ],
        'drill-press-dp40' => [
            'names' => ['fr_FR' => 'Perceuse à colonne DP40', 'en_US' => 'DP40 drill press'],
            'variants' => ['FIXA-001', 'FIXA-002', 'FIXA-003', 'FIXA-004', 'FIXA-005', 'FIXA-006', 'FIXA-007', 'FIXA-008'],
        ],
    ];

    /** Machines of Claire: model code, label, serial number (fictional). */
    public const FLEET = [
        ['forklift-c20', 'Chariot atelier A', 'DEMO-0001'],
        ['pallet-truck-t2', 'Transpalette quai de chargement', 'DEMO-0002'],
        ['drill-press-dp40', 'Perceuse maintenance', 'DEMO-0003'],
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function load(array $options): void
    {
        $owner = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => self::OWNER_EMAIL]);
        if (!$owner instanceof CustomerInterface || $this->entityManager->getRepository(EquipmentModel::class)->count([]) > 0) {
            return;
        }

        $models = [];
        foreach (self::MODELS as $code => $definition) {
            $models[$code] = $this->createModel($code, $definition['names'], $definition['variants']);
        }
        foreach (self::FLEET as [$modelCode, $label, $identifier]) {
            $asset = new CustomerAsset();
            $asset->setCustomer($owner);
            $asset->setEquipmentModel($models[$modelCode]);
            $asset->setLabel($label);
            $asset->setIdentifier($identifier);
            $this->entityManager->persist($asset);
        }
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_equipment_fleet';
    }

    /**
     * @param array<string, string> $names
     * @param list<string>          $variantCodes
     */
    private function createModel(string $code, array $names, array $variantCodes): EquipmentModelInterface
    {
        $model = new EquipmentModel();
        $model->setCode($code);
        foreach (self::LOCALES as $locale) {
            $model->setCurrentLocale($locale);
            $model->setName($names[$locale]);
        }
        /** @var list<ProductVariantInterface> $variants */
        $variants = $this->entityManager->getRepository(ProductVariantInterface::class)->findBy(['code' => $variantCodes]);
        foreach ($variants as $variant) {
            $model->addCompatibleVariant($variant);
        }
        $this->entityManager->persist($model);

        return $model;
    }
}
