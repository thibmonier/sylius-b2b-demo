<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Adds digital product passports for 3 representative EPI products (helmets).
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
        'OUTI-028' => 'torque-wrench-tw200',
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
                continue;
            }

            $variants = $product->getVariants();
            if ($variants->isEmpty()) {
                continue;
            }

            /** @var ProductVariantInterface $variant */
            $variant = $variants->first();

            $existing = $this->entityManager->getRepository(DigitalProductPassport::class)->findOneBy(['productVariant' => $variant]);
            if (null !== $existing) {
                continue;
            }

            $passport = new DigitalProductPassport();
            $passport->setProductVariant($variant);
            $passport->setProductIdentifier($variant->getCode() ?? $productCode);
            $passport->setOriginMaterials($this->getOriginMaterials($productCode));
            $passport->setRecyclability($this->getRecyclability($productCode));
            $passport->setRepairability($this->getRepairability($productCode));
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

    private function getOriginMaterials(string $code): string
    {
        return match ($code) {
            'EPI-CASK-001' => 'Polyéthylène haute densité (HDPE) : France (Rhône-Alpes). Suspension textile : Portugal.',
            'EPI-CASK-002' => 'Polyéthylène haute densité (HDPE) : France (Rhône-Alpes). Suspension textile : Espagne. Bandes réfléchissantes : Belgique.',
            'EPI-CASK-003' => 'Polyéthylène (PE) : France (Rhône-Alpes). Système de ventilation : Allemagne. Frein de nuque : Italie.',
            'OUTI-028' => 'Acier chrome-vanadium : Allemagne. Mécanisme de déclenchement : France (Auvergne). Poignée bi-matière : Italie.',
            default => 'Origine des matériaux non spécifiée.',
        };
    }

    private function getRecyclability(string $code): string
    {
        return match ($code) {
            'EPI-CASK-001' => 'Coque HDPE recyclable (code 2). Suspension textile : filière textile spécialisée recommandée. Taux de recyclabilité estimé : 85%.',
            'EPI-CASK-002' => 'Coque HDPE recyclable (code 2). Bandes réfléchissantes : retrait conseillé avant recyclage. Taux de recyclabilité estimé : 80%.',
            'EPI-CASK-003' => 'Coque PE recyclable (code 2). Système de ventilation : pièces détachables pour tri. Taux de recyclabilité estimé : 90%.',
            'OUTI-028' => 'Corps acier recyclable en filière métaux. Poignée bi-matière : à séparer avant recyclage. Taux de recyclabilité estimé : 92%.',
            default => 'Informations de recyclabilité non disponibles.',
        };
    }

    private function getRepairability(string $code): string
    {
        return match ($code) {
            'EPI-CASK-001' => 'Suspension remplaçable (réf. SUS-CASK-001). Vis standard. Indice de réparabilité : 7/10. Manuel de réparation disponible sur demande.',
            'EPI-CASK-002' => 'Suspension et bandes réfléchissantes remplaçables. Indice de réparabilité : 8/10. Pièces détachées disponibles 10 ans.',
            'EPI-CASK-003' => 'Tous composants remplaçables individuellement. Indice de réparabilité : 9/10. Kit de réparation disponible (réf. REP-CASK-003).',
            'OUTI-028' => 'Mécanisme de déclenchement et cliquet remplaçables. Étalonnage recommandé tous les 12 mois ou 5 000 cycles. Indice de réparabilité : 8/10.',
            default => 'Informations de réparabilité non disponibles.',
        };
    }
}
