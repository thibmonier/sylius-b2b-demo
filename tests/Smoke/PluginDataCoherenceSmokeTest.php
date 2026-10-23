<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\MemberLimitInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestInterface;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\TradeCreditAccountInterface;

/**
 * Smoke test: verifies that all US-010 plugin data was loaded coherently.
 *
 * Must be run AFTER loading the demo fixtures:
 *   bin/console sylius:fixtures:load --suite=demo --no-interaction
 */
final class PluginDataCoherenceSmokeTest extends KernelTestCase
{
    public function testAcmeIndustriesHasTradeCreditAccount(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = $container->get('tmonier_company_accounts.repository.company');
        /** @var CompanyInterface|null $acme */
        $acme = $companyRepo->findOneBy(['code' => 'ACME_IND']);
        self::assertNotNull($acme, 'Acme Industries must exist.');

        /** @var ObjectRepository<TradeCreditAccountInterface> $accountRepo */
        $accountRepo = $container->get('tmonier_trade_credit.repository.account');
        $accounts = $accountRepo->findBy([
            'ownerType' => 'company',
            'ownerIdentifier' => (string) $acme->getId(),
        ]);

        self::assertGreaterThanOrEqual(
            1,
            \count($accounts),
            'Acme Industries must have at least one trade-credit account (US-010).',
        );

        $account = $accounts[0];
        self::assertInstanceOf(TradeCreditAccountInterface::class, $account);
        self::assertGreaterThanOrEqual(1, $account->getCreditLimit(), 'Trade-credit limit must be ≥ 1 cent.');
    }

    public function testAtLeastTenProductsHaveTierPricingRules(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $rules = $em->getRepository(TierPriceRuleInterface::class)->findAll();

        // Each product gets 2 tiers; 12 products × 2 = 24 rules minimum
        self::assertGreaterThanOrEqual(
            10,
            \count($rules),
            'At least 10 tier-price rules must exist across all products (US-010).',
        );
    }

    public function testAtLeastOneDemoQuoteExists(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $quotes = $em->getRepository(QuoteRequestInterface::class)->findAll();

        self::assertGreaterThanOrEqual(
            1,
            \count($quotes),
            'At least 1 demo quote must exist (US-010).',
        );
    }

    public function testAtLeastFiveProductsHaveGpsrData(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $safetyRecords = $em->getRepository(ProductSafetyInterface::class)->findAll();

        self::assertGreaterThanOrEqual(
            5,
            \count($safetyRecords),
            'At least 5 products must have GPSR safety records (US-010).',
        );
    }

    public function testAcmePunchoutBuyerExists(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $buyer = $em->getRepository(Buyer::class)->findOneBy(['code' => \App\Demo\PunchOutSimulator\DemoBuyerCodes::CXML]);

        self::assertNotNull(
            $buyer,
            sprintf('PunchOut buyer "%s" must exist for Acme Industries (US-010).', \App\Demo\PunchOutSimulator\DemoBuyerCodes::CXML),
        );
        self::assertSame('cxml', $buyer->getProtocol(), 'Buyer protocol must be cxml.');
    }

    public function testClairePurchaseApprovalLimitIsSet(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = $container->get('sylius.repository.customer');
        $claire = $customerRepo->findOneBy(['emailCanonical' => 'claire@acme.example']);
        self::assertNotNull($claire, 'Claire must exist.');

        /** @var ObjectRepository<CompanyMemberInterface> $memberRepo */
        $memberRepo = $container->get('tmonier_company_accounts.repository.company_member');
        $membership = $memberRepo->findOneBy(['customer' => $claire]);
        self::assertNotNull($membership, 'Claire must have a company membership.');

        /** @var MemberLimitRepositoryInterface $limitRepo */
        $limitRepo = $container->get('tmonier_purchase_approval.repository.member_limit');
        $limit = $limitRepo->findOneByMember($membership);

        self::assertNotNull(
            $limit,
            'Claire must have a purchase-approval limit configured (US-010).',
        );
        self::assertInstanceOf(MemberLimitInterface::class, $limit);
        self::assertSame(
            50_000,
            $limit->getOrderLimit(),
            'Claire\'s order limit must be 500 € (50 000 cents).',
        );
    }
}
