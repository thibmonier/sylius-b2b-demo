<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

/**
 * Adds GPSR product-safety records for 5 representative EPI products.
 * All manufacturer / responsible-person data is fictional (*.example domains).
 *
 * Depends on: demo_products_csv (-30)
 * Priority: -50
 */
final class GpsrDemoFixture extends AbstractFixture
{
    private const PRODUCT_CODES = [
        'EPI-CASK-001',
        'EPI-CASK-002',
        'EPI-GANT-001',
        'EPI-CHAU-001',
        'EPI-VEST-001',
    ];

    /**
     * @param FactoryInterface<ManufacturerInterface>      $manufacturerFactory
     * @param FactoryInterface<ResponsiblePersonInterface> $responsiblePersonFactory
     * @param FactoryInterface<ProductSafetyInterface>     $productSafetyFactory
     * @param RepositoryInterface<ProductInterface>        $productRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_gpsr.factory.manufacturer')]
        private readonly FactoryInterface $manufacturerFactory,
        #[Autowire(service: 'tmonier_gpsr.factory.responsible_person')]
        private readonly FactoryInterface $responsiblePersonFactory,
        #[Autowire(service: 'tmonier_gpsr.factory.product_safety')]
        private readonly FactoryInterface $productSafetyFactory,
        #[Autowire(service: 'sylius.repository.product')]
        private readonly RepositoryInterface $productRepository,
    ) {
    }

    public function load(array $options): void
    {
        $manufacturer = $this->findOrCreateManufacturer();
        $responsiblePerson = $this->findOrCreateResponsiblePerson();

        foreach (self::PRODUCT_CODES as $index => $productCode) {
            /** @var ProductInterface|null $product */
            $product = $this->productRepository->findOneBy(['code' => $productCode]);
            if (null === $product) {
                continue;
            }

            $existing = $this->entityManager->getRepository(ProductSafetyInterface::class)->findOneBy(['product' => $product]);
            if (null !== $existing) {
                continue;
            }

            /** @var ProductSafetyInterface $safety */
            $safety = $this->productSafetyFactory->createNew();
            $safety->setProduct($product);
            $safety->setManufacturer($manufacturer);
            $safety->setResponsiblePerson($responsiblePerson);
            $safety->setTypeDesignation(sprintf('TYPE-EPI-%03d', $index + 1));
            $safety->setOtherIdentifier(sprintf('CE-2024-%04d', 1000 + $index));

            // Translations
            foreach (['fr_FR' => $this->frWarnings($productCode), 'en_US' => $this->enWarnings($productCode)] as $locale => $texts) {
                $safety->setCurrentLocale($locale);
                $safety->setFallbackLocale($locale);
                $safety->setWarnings($texts['warnings']);
                $safety->setSafetyInformation($texts['safety']);
            }

            $this->entityManager->persist($safety);
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_gpsr';
    }

    private function findOrCreateManufacturer(): ManufacturerInterface
    {
        $existing = $this->entityManager->getRepository(ManufacturerInterface::class)->findOneBy(['code' => 'DEMO-MFR']);
        if ($existing instanceof ManufacturerInterface) {
            return $existing;
        }

        /** @var ManufacturerInterface $manufacturer */
        $manufacturer = $this->manufacturerFactory->createNew();
        $manufacturer->setCode('DEMO-MFR');
        $manufacturer->setName('SafeEquip Manufacturing (fictive)');
        $manufacturer->setTradeMark('SafeEquip™');
        $manufacturer->setStreet('15 rue de la Sécurité');
        $manufacturer->setPostcode('75001');
        $manufacturer->setCity('Paris');
        $manufacturer->setCountryCode('FR');
        $manufacturer->setEmail('conformite@safeequip.example');
        $manufacturer->setWebsite('https://www.safeequip.example');

        $this->entityManager->persist($manufacturer);
        $this->entityManager->flush();

        return $manufacturer;
    }

    private function findOrCreateResponsiblePerson(): ResponsiblePersonInterface
    {
        $existing = $this->entityManager->getRepository(ResponsiblePersonInterface::class)->findOneBy(['code' => 'DEMO-RESP']);
        if ($existing instanceof ResponsiblePersonInterface) {
            return $existing;
        }

        /** @var ResponsiblePersonInterface $responsiblePerson */
        $responsiblePerson = $this->responsiblePersonFactory->createNew();
        $responsiblePerson->setCode('DEMO-RESP');
        $responsiblePerson->setName('EuroSafe Compliance SAS (fictive)');
        $responsiblePerson->setRole(ResponsiblePersonInterface::ROLE_AUTHORISED_REPRESENTATIVE);
        $responsiblePerson->setStreet('42 avenue de la Conformité');
        $responsiblePerson->setPostcode('69001');
        $responsiblePerson->setCity('Lyon');
        $responsiblePerson->setCountryCode('FR');
        $responsiblePerson->setEmail('rep@eurosafe-compliance.example');
        $responsiblePerson->setPhone('+33 4 00 00 00 00');

        $this->entityManager->persist($responsiblePerson);
        $this->entityManager->flush();

        return $responsiblePerson;
    }

    /**
     * @return array{warnings: string, safety: string}
     */
    private function frWarnings(string $code): array
    {
        return [
            'warnings' => 'Porter cet équipement conformément aux instructions du fabricant. Ne pas modifier.',
            'safety' => sprintf('Fiche de sécurité démo — %s. Notice disponible sur https://safeequip.example/docs/%s.pdf', $code, strtolower($code)),
        ];
    }

    /**
     * @return array{warnings: string, safety: string}
     */
    private function enWarnings(string $code): array
    {
        return [
            'warnings' => 'Wear this equipment in accordance with the manufacturer instructions. Do not modify.',
            'safety' => sprintf('Demo safety sheet — %s. Manual available at https://safeequip.example/docs/%s.pdf', $code, strtolower($code)),
        ];
    }
}
