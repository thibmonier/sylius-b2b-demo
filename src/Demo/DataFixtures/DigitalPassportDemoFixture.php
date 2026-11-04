<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\DataFixtures\Passport\PassportSheet;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Adds digital product passports for 3 representative EPI products (helmets) and 2 tools (socket set, torque wrench).
 * All sustainability data is fictional for demo purposes.
 *
 * Depends on: demo_products_csv (-30)
 * Priority: -50
 */
final class DigitalPassportDemoFixture extends AbstractFixture
{
    /** Product code => equipment model code of the customer-asset-management plugin (demo_equipment_fleet), or null. */
    private const PRODUCT_CODES = [
        'EPI-CASK-001' => null,
        'EPI-CASK-002' => null,
        'EPI-CASK-003' => null,
        'OUTI-026' => null,
        'OUTI-028' => 'torque-wrench-tw200',
    ];

    /**
     * Product code => sustainability data (fictional). A missing field fails the load, see PassportSheet.
     *
     * @var array<string, array<string, string>>
     */
    private const SHEETS = [
        'EPI-CASK-001' => [
            'originMaterials' => 'Polyéthylène haute densité (HDPE) : France (Rhône-Alpes). Suspension textile : Portugal.',
            'recyclability' => 'Coque HDPE recyclable (code 2). Suspension textile : filière textile spécialisée recommandée. Taux de recyclabilité estimé : 85%.',
            'repairability' => 'Suspension remplaçable (réf. SUS-CASK-001). Vis standard. Indice de réparabilité : 7/10. Manuel de réparation disponible sur demande.',
        ],
        'EPI-CASK-002' => [
            'originMaterials' => 'Polyéthylène haute densité (HDPE) : France (Rhône-Alpes). Suspension textile : Espagne. Bandes réfléchissantes : Belgique.',
            'recyclability' => 'Coque HDPE recyclable (code 2). Bandes réfléchissantes : retrait conseillé avant recyclage. Taux de recyclabilité estimé : 80%.',
            'repairability' => 'Suspension et bandes réfléchissantes remplaçables. Indice de réparabilité : 8/10. Pièces détachées disponibles 10 ans.',
        ],
        'EPI-CASK-003' => [
            'originMaterials' => 'Polyéthylène (PE) : France (Rhône-Alpes). Système de ventilation : Allemagne. Frein de nuque : Italie.',
            'recyclability' => 'Coque PE recyclable (code 2). Système de ventilation : pièces détachables pour tri. Taux de recyclabilité estimé : 90%.',
            'repairability' => 'Tous composants remplaçables individuellement. Indice de réparabilité : 9/10. Kit de réparation disponible (réf. REP-CASK-003).',
        ],
        'OUTI-026' => [
            'originMaterials' => 'Acier chrome-vanadium (douilles et cliquet) : Allemagne. Coffret en polypropylène : France (Normandie). Rallonge et cardan : Slovénie.',
            'recyclability' => 'Douilles et cliquet en acier recyclables en filière métaux. Coffret polypropylène (code 5) : à trier séparément. Taux de recyclabilité estimé : 88%.',
            'repairability' => 'Cliquet réparable avec kit de joints et d’encliquetage. Douilles remplaçables à l’unité. Indice de réparabilité : 9/10.',
        ],
        'OUTI-028' => [
            'originMaterials' => 'Acier chrome-vanadium : Allemagne. Mécanisme de déclenchement : France (Auvergne). Poignée bi-matière : Italie.',
            'recyclability' => 'Corps acier recyclable en filière métaux. Poignée bi-matière : à séparer avant recyclage. Taux de recyclabilité estimé : 92%.',
            'repairability' => 'Mécanisme de déclenchement et cliquet remplaçables. Étalonnage recommandé tous les 12 mois ou 5 000 cycles. Indice de réparabilité : 8/10.',
        ],
    ];

    /**
     * @param RepositoryInterface<ProductInterface> $productRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.repository.product')]
        private readonly RepositoryInterface $productRepository,
    ) {
    }

    public function load(array $options): void
    {
        foreach (self::PRODUCT_CODES as $productCode => $equipmentModelCode) {
            /** @var ProductInterface|null $product */
            $product = $this->productRepository->findOneBy(['code' => $productCode]);
            if (null === $product) {
                throw new \RuntimeException(\sprintf('Digital passport fixture "%s": the product is not in the catalogue.', $productCode));
            }

            $variants = $product->getVariants();
            if ($variants->isEmpty()) {
                throw new \RuntimeException(\sprintf('Digital passport fixture "%s": the product has no variant.', $productCode));
            }

            /** @var ProductVariantInterface $variant */
            $variant = $variants->first();

            $existing = $this->entityManager->getRepository(DigitalProductPassport::class)->findOneBy(['productVariant' => $variant]);
            if (null !== $existing) {
                continue;
            }

            $sheet = PassportSheet::fromArray($productCode, self::SHEETS[$productCode]);
            $passport = new DigitalProductPassport();
            $passport->setProductVariant($variant);
            $passport->setProductIdentifier($variant->getCode() ?? $productCode);
            $passport->setOriginMaterials($sheet->originMaterials);
            $passport->setRecyclability($sheet->recyclability);
            $passport->setRepairability($sheet->repairability);
            $passport->setEquipmentModelCode($equipmentModelCode);
            $passport->setVersion(1);

            $this->entityManager->persist($passport);
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_digital_passports';
    }
}
