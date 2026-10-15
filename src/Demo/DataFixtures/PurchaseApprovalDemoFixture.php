<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\MemberLimitInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * Configures purchase-approval limits for Acme Industries:
 * - Buyer members (Claire): order limit = 500 € (50000 cents) — orders above require approval
 * - Approver members (Paul): approval limit = unlimited (null)
 *
 * This calibration is designed to be easily exceeded during the demo approval scenario.
 *
 * Depends on: demo_companies (-15)
 * Priority: -40
 */
final class PurchaseApprovalDemoFixture extends AbstractFixture
{
    /** Order limit in EUR cents. Orders above this require approval (Claire's limit). */
    private const BUYER_ORDER_LIMIT_CENTS = 50_000; // 500 €

    /**
     * @param FactoryInterface<MemberLimitInterface> $limitFactory
     * @param RepositoryInterface<ChannelInterface>  $channelRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_company_accounts.repository.company')]
        private readonly CompanyRepositoryInterface $companyRepository,
        #[Autowire(service: 'tmonier_purchase_approval.factory.member_limit')]
        private readonly FactoryInterface $limitFactory,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private readonly MemberLimitRepositoryInterface $limitRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        /** @var ChannelInterface|null $b2bChannel */
        $b2bChannel = $this->channelRepository->findOneBy(['code' => 'B2B'])
            ?? $this->channelRepository->findOneBy(['enabled' => true]);

        $currencyCode = $b2bChannel?->getBaseCurrency()?->getCode() ?? 'EUR';

        // Apply limits to all Acme members
        $acme = $this->companyRepository->findOneBy(['code' => 'ACME_IND']);
        if (!$acme instanceof CompanyInterface) {
            return;
        }

        foreach ($acme->getMembers() as $member) {
            $this->applyLimit($member, $currencyCode);
        }

        $this->entityManager->flush();
    }

    private function applyLimit(CompanyMemberInterface $member, string $currencyCode): void
    {
        [$orderLimit, $approvalLimit] = match ($member->getRole()) {
            CompanyMemberInterface::ROLE_BUYER => [self::BUYER_ORDER_LIMIT_CENTS, null],
            CompanyMemberInterface::ROLE_APPROVER => [null, null], // null approval limit = unlimited
            default => null,
        } ?? [null, null];

        // Only configure buyers and approvers
        if (CompanyMemberInterface::ROLE_BUYER !== $member->getRole() &&
            CompanyMemberInterface::ROLE_APPROVER !== $member->getRole()
        ) {
            return;
        }

        $limit = $this->limitRepository->findOneByMember($member);
        if (null === $limit) {
            /** @var MemberLimitInterface $limit */
            $limit = $this->limitFactory->createNew();
            $limit->setMember($member);
        }

        $limit->setOrderLimit($orderLimit);
        $limit->setApprovalLimit($approvalLimit);
        $limit->setCurrencyCode($currencyCode);
        $limit->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($limit);
    }

    public function getName(): string
    {
        return 'demo_purchase_approval';
    }
}
