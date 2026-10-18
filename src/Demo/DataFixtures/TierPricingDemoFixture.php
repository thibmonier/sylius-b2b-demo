<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Account\DemoAccountCatalog;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Customer\Model\CustomerGroupInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;
use Tmonier\SyliusTierPricingPlugin\Factory\TierPriceRuleFactoryInterface;

/**
 * Adds three public quantity-break tiers (10+ units → -5 %, 50+ → -10 %, 200+ → -15 %) on 12 representative
 * products, plus the negotiated price of the Acme company (US-090, decision D2): the plugin has no rule "per
 * company", so Acme gets a customer group and the rules of that group (2+ units → -12 %, 25+ → -16 %, 100+ → -20 %).
 * Scoped at product level on the B2B channel so the thresholds are easy to hit during demo scenarios.
 * Percentages are in basis points, the unit of the plugin (500 = 5 %).
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
        'EPI-CASK-001',
        'EPI-GANT-001',
        'EPI-CHAU-001',
        'EPI-VEST-001',
        'VISS-001',
        'VISS-002',
        'OUTI-001',
        'OUTI-002',
        'FIXA-001',
        'EMBA-001',
        'SIGN-001',
        'PAPE-001',
    ];

    public const ACME_COMPANY_CODE = 'ACME_IND';

    public const ACME_GROUP_CODE = 'acme';

    /** @var array<int, array{minQty: int, bps: int}> public tiers, for everybody */
    private const TIERS = [
        ['minQty' => 10, 'bps' => 500],
        ['minQty' => 50, 'bps' => 1000],
        ['minQty' => 200, 'bps' => 1500],
    ];

    /** @var array<int, array{minQty: int, bps: int}> negotiated by Acme: the plugin needs at least 2 units */
    private const ACME_TIERS = [
        ['minQty' => 2, 'bps' => 1200],
        ['minQty' => 25, 'bps' => 1600],
        ['minQty' => 100, 'bps' => 2000],
    ];

    /**
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     * @param FactoryInterface<CustomerGroupInterface> $groupFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_tier_pricing.factory.tier_price_rule')]
        private readonly TierPriceRuleFactoryInterface $ruleFactory,
        #[Autowire(service: 'sylius.repository.product')]
        private readonly RepositoryInterface $productRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        private readonly DemoAccountCatalog $catalog,
        #[Autowire(service: 'sylius.factory.customer_group')]
        private readonly FactoryInterface $groupFactory,
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

        $acme = $this->acmeGroup();
        foreach (self::PRODUCT_CODES as $productCode) {
            /** @var ProductInterface|null $product */
            $product = $this->productRepository->findOneBy(['code' => $productCode]);
            if (null === $product) {
                continue;
            }

            foreach (self::TIERS as $tier) {
                $this->createRule($product, $b2bChannel, $tier, null);
            }
            foreach (self::ACME_TIERS as $tier) {
                $this->createRule($product, $b2bChannel, $tier, $acme);
            }
        }

        $this->entityManager->flush();
    }

    /** @param array{minQty: int, bps: int} $tier */
    private function createRule(ProductInterface $product, ChannelInterface $channel, array $tier, ?CustomerGroupInterface $group): void
    {
        // Idempotent: skip if a rule already exists for this product/channel/group/minQty combo
        $existing = $this->entityManager->getRepository(TierPriceRuleInterface::class)->findOneBy([
            'product' => $product,
            'channel' => $channel,
            'customerGroup' => $group,
            'minQuantity' => $tier['minQty'],
        ]);
        if (null !== $existing) {
            return;
        }

        $rule = $this->ruleFactory->createNew();
        $rule->setScope(TierPriceRuleInterface::SCOPE_PRODUCT);
        $rule->setProduct($product);
        $rule->setChannel($channel);
        $rule->setCustomerGroup($group);
        $rule->setMinQuantity($tier['minQty']);
        $rule->setType(TierPriceRuleInterface::TYPE_PERCENTAGE_OFF);
        $rule->setValue($tier['bps']);
        $rule->setQuantityBasis(TierPriceRuleInterface::BASIS_LINE);
        $rule->setCumulation(TierPriceRuleInterface::CUMULATION_BEST_OF);
        $rule->enable();

        $this->entityManager->persist($rule);
    }

    /** The group of the Acme company, and its members in it. */
    private function acmeGroup(): CustomerGroupInterface
    {
        $group = $this->entityManager->getRepository(CustomerGroupInterface::class)->findOneBy(['code' => self::ACME_GROUP_CODE]);
        if (!$group instanceof CustomerGroupInterface) {
            $group = $this->groupFactory->createNew();
            $group->setCode(self::ACME_GROUP_CODE);
            $group->setName('Acme Industries');
            $this->entityManager->persist($group);
        }

        foreach ($this->catalog->companies() as $company) {
            if (self::ACME_COMPANY_CODE !== $company->code) {
                continue;
            }
            foreach ($company->members as $member) {
                /** @var CustomerInterface|null $customer */
                $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => strtolower($member->email)]);
                $customer?->setGroup($group);
            }
        }
        $this->entityManager->flush();

        return $group;
    }

    public function getName(): string
    {
        return 'demo_tier_pricing';
    }
}
