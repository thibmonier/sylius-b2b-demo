<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Bridge\OrderHold\CompanyAccountOwnerResolver;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\PaymentTermsPolicyInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\TradeCreditAccountInterface;
use Tmonier\SyliusTradeCreditPlugin\Repository\PaymentTermsPolicyRepositoryInterface;
use Tmonier\SyliusTradeCreditPlugin\Repository\TradeCreditAccountRepositoryInterface;

/**
 * Creates B2B trade-credit accounts (company-scoped) for the 3 main demo companies.
 *
 * Depends on: demo_companies (-15)
 * Priority: -40 (runs after products -30, companies -15)
 */
final class TradeCreditDemoFixture extends AbstractFixture
{
    /** Payment method of the demo_fixtures suite that is paid on account (US-026 I3). */
    public const ON_ACCOUNT_PAYMENT_METHOD = 'on_account';

    private const NET_DAYS = 30;

    /** @var array<string, int> companyCode => credit limit in EUR cents */
    private const LIMITS = [
        'ACME_IND' => 1_000_000,   // 10 000 €
        'TECHPARTS' => 500_000,    // 5 000 €
        'BUILDCO' => 300_000,      // 3 000 €
    ];

    /**
     * @param FactoryInterface<TradeCreditAccountInterface>         $accountFactory
     * @param FactoryInterface<PaymentTermsPolicyInterface>         $policyFactory
     * @param \Sylius\Resource\Doctrine\Persistence\RepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
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
        #[Autowire(service: 'tmonier_trade_credit.factory.payment_terms_policy')]
        private readonly FactoryInterface $policyFactory,
        #[Autowire(service: 'tmonier_trade_credit.repository.payment_terms_policy')]
        private readonly PaymentTermsPolicyRepositoryInterface $policyRepository,
        #[Autowire(service: 'sylius.repository.payment_method')]
        private readonly \Sylius\Resource\Doctrine\Persistence\RepositoryInterface $paymentMethodRepository,
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

        $this->ensureOnAccountPolicy();
        $this->entityManager->flush();
    }

    /** Makes the "on account" payment method a trade-credit one: orders paid with it reserve the company credit. */
    private function ensureOnAccountPolicy(): void
    {
        $method = $this->paymentMethodRepository->findOneBy(['code' => self::ON_ACCOUNT_PAYMENT_METHOD]);
        if (!$method instanceof PaymentMethodInterface || null !== $this->policyRepository->findOneByPaymentMethod($method)) {
            return;
        }

        $policy = $this->policyFactory->createNew();
        $policy->setPaymentMethod($method);
        $policy->setNetDays(self::NET_DAYS);
        $this->entityManager->persist($policy);
    }

    public function getName(): string
    {
        return 'demo_trade_credit';
    }
}
