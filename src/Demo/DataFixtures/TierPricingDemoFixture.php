<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;
use Tmonier\SyliusTierPricingPlugin\Factory\TierPriceRuleFactoryInterface;

/**
 * Adds two quantity-break tiers (10+ units → -5 %, 50+ units → -10 %) on 12 representative products.
 * Scoped at product level on the B2B channel so the thresholds are easy to hit during demo scenarios.
 *
 * Claire's purchase-approval limit (500 €) is intentionally below the cost of ordering 10+
 * items of a mid-price product to trigger the approval workflow (US-010).
 *
 * Depends on: demo_products_csv (-30)
 * Priority: -40
 */
final class TierPricingDemoFixture extends AbstractFixture
{
    /** Product codes from the CSV catalogue that will receive tier pricing */
    private const PRODUCT_CODES = [
        'EPI-CASQUE-001',
        'EPI-GANTS-001',
        'EPI-CHAUSSURES-001',
        'EPI-GILET-001',
        'EPI-LUNETTES-001',
        'VIS-M6-001',
        'VIS-M8-001',
        'ECROU-M6-001',
        'RONDELLE-M6-001',
        'TOURNEVIS-001',
        'CLE-MOLETTE-001',
        'PERCEUSE-001',
    ];

    /** @var array<int, array{minQty: int, pct: int}> */
    private const TIERS = [
        ['minQty' => 10, 'pct' => 5],
        ['minQty' => 50, 'pct' => 10],
    ];

    /**
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_tier_pricing.factory.tier_price_rule')]
        private readonly TierPriceRuleFactoryInterface $ruleFactory,
        #[Autowire(service: 'sylius.repository.product')]
        private readonly RepositoryInterface $productRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        /** @var ChannelInterface|null $b2bChannel */
        $b2bChannel = $this->channelRepository->findOneBy(['code' => 'B2B'])
            ?? $this->channelRepository->findOneBy(['enabled' => true]);

        if (null === $b2bChannel) {
            return;
        }

        $created = 0;
        foreach (self::PRODUCT_CODES as $productCode) {
            /** @var ProductInterface|null $product */
            $product = $this->productRepository->findOneBy(['code' => $productCode]);
            if (null === $product) {
                continue;
            }

            foreach (self::TIERS as $tier) {
                // Idempotent: skip if a rule already exists for this product/channel/minQty combo
                $existing = $this->entityManager->getRepository(TierPriceRuleInterface::class)->findOneBy([
                    'product' => $product,
                    'channel' => $b2bChannel,
                    'minQuantity' => $tier['minQty'],
                ]);
                if (null !== $existing) {
                    continue;
                }

                $rule = $this->ruleFactory->createNew();
                $rule->setScope(TierPriceRuleInterface::SCOPE_PRODUCT);
                $rule->setProduct($product);
                $rule->setChannel($b2bChannel);
                $rule->setMinQuantity($tier['minQty']);
                $rule->setType(TierPriceRuleInterface::TYPE_PERCENTAGE_OFF);
                $rule->setValue($tier['pct']);
                $rule->setQuantityBasis(TierPriceRuleInterface::BASIS_LINE);
                $rule->setCumulation(TierPriceRuleInterface::CUMULATION_BEST_OF);
                $rule->enable();

                $this->entityManager->persist($rule);
                ++$created;
            }

            if ($created > 0 && $created % 20 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_tier_pricing';
    }
}
