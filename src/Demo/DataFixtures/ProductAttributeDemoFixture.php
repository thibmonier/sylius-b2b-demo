<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates the product attributes used by the B2B demo catalogue.
 *
 * Attributes:
 *   - epi_norm       (text)  Protection standard (EN 397, EN 388, EN ISO 20345…)
 *   - packaging_unit (text)  Packaging unit (paire, unité, boîte de 100…)
 *   - material       (text)  Main material
 *   - certifications (text)  Certifications (CE, ATEX…)
 */
final class ProductAttributeDemoFixture extends AbstractFixture
{
    /**
     * @param FactoryInterface<ProductAttributeInterface>       $attributeFactory
     * @param RepositoryInterface<ProductAttributeInterface>    $attributeRepository
     */
    public function __construct(
        #[Autowire(service: 'sylius.factory.product_attribute')]
        private readonly FactoryInterface $attributeFactory,
        #[Autowire(service: 'sylius.repository.product_attribute')]
        private readonly RepositoryInterface $attributeRepository,
        private readonly EntityManagerInterface $objectManager,
    ) {
    }

    public function load(array $options): void
    {
        foreach ($this->attributeDefinitions() as $def) {
            $this->createOrUpdateAttribute($def['code'], $def['fr'], $def['en']);
        }
        $this->objectManager->flush();
    }

    public function getName(): string
    {
        return 'demo_product_attributes';
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * @return list<array{code: string, fr: string, en: string}>
     */
    private function attributeDefinitions(): array
    {
        return [
            ['code' => 'epi_norm', 'fr' => 'Norme de protection', 'en' => 'Protection standard'],
            ['code' => 'packaging_unit', 'fr' => 'Unité de conditionnement', 'en' => 'Packaging unit'],
            ['code' => 'material', 'fr' => 'Matière principale', 'en' => 'Main material'],
            ['code' => 'certifications', 'fr' => 'Certifications', 'en' => 'Certifications'],
        ];
    }

    private function createOrUpdateAttribute(string $code, string $nameFr, string $nameEn): void
    {
        /** @var ProductAttributeInterface|null $attribute */
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);

        if ($attribute === null) {
            /** @var ProductAttributeInterface $attribute */
            $attribute = $this->attributeFactory->createNew();
            $attribute->setCode($code);
            $attribute->setType('text');
            // storage_type is NOT NULL in the schema; a text attribute stores as 'text'.
            $attribute->setStorageType('text');
        }

        $attribute->setCurrentLocale('fr_FR');
        $attribute->setFallbackLocale('fr_FR');
        $attribute->setName($nameFr);

        $attribute->setCurrentLocale('en_US');
        $attribute->setFallbackLocale('en_US');
        $attribute->setName($nameEn);

        $this->objectManager->persist($attribute);
    }
}
