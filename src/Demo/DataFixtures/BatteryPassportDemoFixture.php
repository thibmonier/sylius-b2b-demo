<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\DataFixtures\Passport\PassportSheet;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryCategory;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Sprint 55 (T-TECH-55-03, US-284): the passport of an industrial battery (Regulation (EU) 2023/1542, art. 77, public level): a lithium iron
 * phosphate (LFP) traction battery of a forklift truck, 28.7 kWh (above the 2 kWh threshold of industrial batteries).
 *
 * The product is created here, not in the CSV files: the CSV catalogue is the 210 products of the shop and each of its rows needs its image.
 * It is on sale in the B2B channel only and in no category (reached by its address, /products/batt-001-fr), so the catalogue lists are unchanged.
 * Everything is fictional (the GTIN carries the GS1 example prefix); the profile goes through the validator of the plugin, so a missing
 * mandatory field fails the load by name.
 *
 * Depends on: the geographical and channel fixtures (0), the tax categories (0). Priority: -51 (after demo_digital_passports, -50).
 */
final class BatteryPassportDemoFixture extends AbstractFixture
{
    public const PRODUCT_CODE = 'BATT-001';

    private const PRICE_EXCLUDING_TAX_CENTS = 689000;

    private const SHEET = [
        'gtin' => '09506000000060',
        'originMaterials' => 'Cellules LFP prismatiques : assemblées en France à partir de cellules importées. Boîtier acier : France. Carte de gestion (BMS) : Allemagne.',
        'recyclability' => 'Cellules lithium-fer-phosphate : filière de recyclage des batteries industrielles. Boîtier acier recyclable en filière métaux. Reprise en fin de vie proposée par le fabricant (fictif).',
        'repairability' => 'Module de cellules et BMS remplaçables séparément par un technicien habilité. Diagnostic de l\'état de santé par la prise de service. Pièces détachées disponibles 10 ans.',
    ];

    /**
     * @param FactoryInterface<ProductInterface>        $productFactory
     * @param FactoryInterface<ProductVariantInterface> $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param RepositoryInterface<ChannelInterface>     $channelRepository
     * @param RepositoryInterface<TaxCategoryInterface> $taxCategoryRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        #[Autowire(service: 'sylius.factory.product')]
        private readonly FactoryInterface $productFactory,
        #[Autowire(service: 'sylius.factory.product_variant')]
        private readonly FactoryInterface $productVariantFactory,
        #[Autowire(service: 'sylius.factory.channel_pricing')]
        private readonly FactoryInterface $channelPricingFactory,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.tax_category')]
        private readonly RepositoryInterface $taxCategoryRepository,
    ) {
    }

    public function load(array $options): void
    {
        $variant = $this->createProduct()->getVariants()->first();
        \assert($variant instanceof ProductVariantInterface);

        $sheet = PassportSheet::fromArray(self::PRODUCT_CODE, self::SHEET);
        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setProductIdentifier($sheet->gtin);
        $passport->setOriginMaterials($sheet->originMaterials);
        $passport->setRecyclability($sheet->recyclability);
        $passport->setRepairability($sheet->repairability);
        $passport->setBatteryProfile($this->profile());
        $passport->setVersion(1);

        $violations = $this->validator->validate($passport);
        if (\count($violations) > 0) {
            throw new \RuntimeException(\sprintf('Battery passport fixture "%s": %s', self::PRODUCT_CODE, (string) $violations));
        }

        $this->entityManager->persist($passport);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_battery_passport';
    }

    private function createProduct(): ProductInterface
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->createNew();
        $product->setCode(self::PRODUCT_CODE);
        $product->setEnabled(true);
        $names = ['fr_FR' => 'Batterie de traction lithium-fer-phosphate 51,2 V 560 Ah pour chariot élévateur', 'en_US' => 'Lithium iron phosphate traction battery 51.2 V 560 Ah for forklift truck'];
        $descriptions = [
            'fr_FR' => 'Batterie lithium-fer-phosphate (LFP) 51,2 V, 560 Ah (28,7 kWh) pour chariot élévateur électrique. Charge d\'opportunité, sans entretien ni ajout d\'eau, BMS communiquant. Fiche fictive de démonstration.',
            'en_US' => 'Lithium iron phosphate (LFP) battery, 51.2 V, 560 Ah (28.7 kWh) for an electric forklift truck. Opportunity charging, maintenance-free, communicating BMS. Fictional demo sheet.',
        ];
        foreach ($names as $locale => $name) {
            $product->setCurrentLocale($locale);
            $product->setFallbackLocale($locale);
            $product->setName($name);
            $product->setSlug('batt-001-' . ('fr_FR' === $locale ? 'fr' : 'en'));
            $product->setDescription($descriptions[$locale]);
            $product->setShortDescription($descriptions[$locale]);
        }

        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']);
        if (!$channel instanceof ChannelInterface) {
            throw new \RuntimeException('Battery passport fixture: the B2B channel is not loaded.');
        }
        $product->addChannel($channel);

        $variant = $this->variant($channel, $names);
        $product->addVariant($variant);
        $this->entityManager->persist($product);

        return $product;
    }

    /** @param array<string, string> $names */
    private function variant(ChannelInterface $channel, array $names): ProductVariantInterface
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->productVariantFactory->createNew();
        $variant->setCode(self::PRODUCT_CODE);
        foreach ($names as $locale => $name) {
            $variant->setCurrentLocale($locale);
            $variant->setFallbackLocale($locale);
            $variant->setName($name);
        }
        $variant->setWeight(330.0);
        $variant->setTaxCategory($this->taxCategoryRepository->findOneBy(['code' => 'standard']));

        /** @var ChannelPricingInterface $pricing */
        $pricing = $this->channelPricingFactory->createNew();
        $pricing->setChannelCode((string) $channel->getCode());
        $pricing->setPrice(self::PRICE_EXCLUDING_TAX_CENTS);
        $variant->addChannelPricing($pricing);

        return $variant;
    }

    private function profile(): BatteryProfile
    {
        $battery = new BatteryProfile();
        $battery->category = BatteryCategory::Industrial;
        $battery->chemistry = 'Lithium-fer-phosphate (LFP)';
        $battery->nominalCapacityAh = 560.0;
        $battery->nominalVoltageV = 51.2;
        $battery->weightKg = 330.0;
        $battery->manufacturedOn = new \DateTimeImmutable('2026-09-01');
        $battery->minVoltageV = 44.8;
        $battery->maxVoltageV = 58.4;
        $battery->manufacturingPlace = 'Lyon, France (site fictif)';
        $battery->carbonFootprintKgCo2ePerKwh = 82.0;
        $battery->recycledCobaltPercent = 0.0;
        $battery->recycledLithiumPercent = 6.0;
        $battery->recycledNickelPercent = 0.0;
        $battery->recycledLeadPercent = 0.0;
        $battery->renewableSharePercent = 38.0;
        $battery->cycleLife = 3500;
        $battery->hazardousSubstances = 'Électrolyte organique à sel de lithium (LiPF6). Ni plomb, ni cadmium, ni mercure.';
        $battery->criticalRawMaterials = 'Lithium, graphite naturel, phosphore : teneur supérieure à 0,1 % en masse.';
        $battery->extinguishingAgent = 'Eau en grande quantité ; poudre ABC pour un début d\'incendie.';
        $battery->warrantyMonths = 60;

        return $battery;
    }
}
