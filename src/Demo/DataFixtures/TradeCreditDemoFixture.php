<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Bridge\OrderHold\CompanyAccountOwnerResolver;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\TradeCreditAccountInterface;
use Tmonier\SyliusTradeCreditPlugin\Repository\TradeCreditAccountRepositoryInterface;

/**
 * Creates B2B trade-credit accounts (company-scoped) for the 3 main demo companies.
 *
 * Depends on: demo_companies (-15)
 * Priority: -40 (runs after products -30, companies -15)
 */
final class TradeCreditDemoFixture extends AbstractFixture
{
    /** @var array<string, int> companyCode => credit limit in EUR cents */
    private const LIMITS = [
        'ACME_IND' => 1_000_000,   // 10 000 €
        'TECHPARTS' => 500_000,    // 5 000 €
        'BUILDCO' => 300_000,      // 3 000 €
    ];

    /**
     * @param FactoryInterface<TradeCreditAccountInterface>         $accountFactory
     * @param \Sylius\Resource\Doctrine\Persistence\RepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_company_accounts.repository.company')]
        private readonly CompanyRepositoryInterface $companyRepository,
        #[Autowire(service: 'tmonier_trade_credit.factory.account')]
        private readonly FactoryInterface $accountFactory,
        #[Autowire(service: 'tmonier_trade_credit.repository.account')]
        private readonly TradeCreditAccountRepositoryInterface $accountRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly \Sylius\Resource\Doctrine\Persistence\RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        /** @var ChannelInterface|null $b2bChannel */
        $b2bChannel = $this->channelRepository->findOneBy(['code' => 'B2B'])
            ?? $this->channelRepository->findOneBy(['enabled' => true]);

        $currencyCode = $b2bChannel?->getBaseCurrency()?->getCode() ?? 'EUR';

        foreach (self::LIMITS as $companyCode => $limitCents) {
            $company = $this->companyRepository->findOneBy(['code' => $companyCode]);
            if (!$company instanceof CompanyInterface || null === $company->getId()) {
                continue;
            }

            $ownerType = CompanyAccountOwnerResolver::TYPE_COMPANY;
            $ownerIdentifier = (string) $company->getId();

            // Idempotent: skip if account already exists
            $existing = $this->accountRepository->findOneBy([
                'ownerType' => $ownerType,
                'ownerIdentifier' => $ownerIdentifier,
            ]);
            if (null !== $existing) {
                continue;
            }

            /** @var TradeCreditAccountInterface $account */
            $account = $this->accountFactory->createNew();
            $account->setOwner($ownerType, $ownerIdentifier);
            $account->setCurrencyCode($currencyCode);
            $account->setCreditLimit($limitCents);
            $account->setNetDays(30);
            $account->setEnabled(true);
            $account->setNote(sprintf('Crédit commercial démo pour %s (US-010).', $company->getName()));

            $this->entityManager->persist($account);
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_trade_credit';
    }
}
