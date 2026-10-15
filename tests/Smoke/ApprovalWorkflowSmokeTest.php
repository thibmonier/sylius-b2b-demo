<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\Persistence\ObjectRepository;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\MemberLimitInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * Smoke test: verifies the approval workflow configuration (US-010 scénario approbation).
 *
 * Tests the data setup that makes the approval flow possible:
 * - Claire (buyer) has an order limit of 500 €
 * - Paul (approver) has no order limit
 * - Acme has both roles configured
 *
 * Full end-to-end order-placement tests require a running database and are handled
 * by functional/integration test suites.
 */
final class ApprovalWorkflowSmokeTest extends KernelTestCase
{
    public function testClairesOrderLimitEnablesApprovalWorkflow(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = $container->get('sylius.repository.customer');
        $claire = $customerRepo->findOneBy(['emailCanonical' => 'claire@acme.example']);
        self::assertNotNull($claire, 'Claire (claire@acme.example) must exist.');

        /** @var ObjectRepository<CompanyMemberInterface> $memberRepo */
        $memberRepo = $container->get('tmonier_company_accounts.repository.company_member');
        $membership = $memberRepo->findOneBy(['customer' => $claire]);
        self::assertNotNull($membership, 'Claire must be a company member.');
        self::assertSame(CompanyMemberInterface::ROLE_BUYER, $membership->getRole(), 'Claire must be a buyer.');

        /** @var MemberLimitRepositoryInterface $limitRepo */
        $limitRepo = $container->get('tmonier_purchase_approval.repository.member_limit');
        $limit = $limitRepo->findOneByMember($membership);

        self::assertNotNull($limit, 'Claire must have a purchase-approval MemberLimit configured.');
        self::assertInstanceOf(MemberLimitInterface::class, $limit);

        // Orders ≤ 500 € → auto-validated (orderLimit = 50000 cents)
        self::assertSame(50_000, $limit->getOrderLimit(), 'Claire\'s order limit must be 500 € = 50 000 cents.');
    }

    public function testPaulIsConfiguredAsApproverForAcme(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = $container->get('sylius.repository.customer');
        $paul = $customerRepo->findOneBy(['emailCanonical' => 'paul@acme.example']);
        self::assertNotNull($paul, 'Paul (paul@acme.example) must exist.');

        /** @var ObjectRepository<CompanyMemberInterface> $memberRepo */
        $memberRepo = $container->get('tmonier_company_accounts.repository.company_member');
        $membership = $memberRepo->findOneBy(['customer' => $paul]);
        self::assertNotNull($membership, 'Paul must be a company member.');
        self::assertSame(CompanyMemberInterface::ROLE_APPROVER, $membership->getRole(), 'Paul must be ROLE_APPROVER.');

        /** @var MemberLimitRepositoryInterface $limitRepo */
        $limitRepo = $container->get('tmonier_purchase_approval.repository.member_limit');
        $limit = $limitRepo->findOneByMember($membership);

        self::assertNotNull($limit, 'Paul must have a MemberLimit configured (even with null approval limit).');
    }

    public function testAcmeHasBothBuyerAndApproverRoles(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = $container->get('tmonier_company_accounts.repository.company');
        /** @var CompanyInterface|null $acme */
        $acme = $companyRepo->findOneBy(['code' => 'ACME_IND']);
        self::assertNotNull($acme, 'Acme Industries must exist.');

        $hasBuyer = false;
        $hasApprover = false;
        foreach ($acme->getMembers() as $member) {
            if (CompanyMemberInterface::ROLE_BUYER === $member->getRole()) {
                $hasBuyer = true;
            }
            if (CompanyMemberInterface::ROLE_APPROVER === $member->getRole()) {
                $hasApprover = true;
            }
        }

        self::assertTrue($hasBuyer, 'Acme must have at least one buyer member for the approval scenario.');
        self::assertTrue($hasApprover, 'Acme must have at least one approver member for the approval scenario.');
    }

    public function testOrderLimitThresholdMatchesApprovalScenario(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        // Verify the threshold is exactly 500 € = 50 000 cents
        // (calibrated to be reachable with the tier-pricing products)
        /** @var ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = $container->get('sylius.repository.customer');
        $claire = $customerRepo->findOneBy(['emailCanonical' => 'claire@acme.example']);
        self::assertNotNull($claire);

        /** @var ObjectRepository<CompanyMemberInterface> $memberRepo */
        $memberRepo = $container->get('tmonier_company_accounts.repository.company_member');
        $membership = $memberRepo->findOneBy(['customer' => $claire]);
        self::assertNotNull($membership);

        /** @var MemberLimitRepositoryInterface $limitRepo */
        $limitRepo = $container->get('tmonier_purchase_approval.repository.member_limit');
        $limit = $limitRepo->findOneByMember($membership);
        self::assertNotNull($limit);

        $orderLimitEuros = ($limit->getOrderLimit() ?? 0) / 100;
        self::assertSame(
            500.0,
            $orderLimitEuros,
            'The order limit must be exactly 500 € so the demo scenario triggers approval with a realistic cart.',
        );
    }
}
