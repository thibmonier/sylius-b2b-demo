<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Factory\CompanyMemberFactoryInterface;

/**
 * Creates five fictitious B2B companies with their members (US-009).
 *
 * Companies:
 *   - Acme Industries      (claire, paul, isabelle)
 *   - TechParts SAS        (marc, nathalie)
 *   - BuildCo SA           (thomas[disabled], antoine)
 *   - GlobalMaint SA       (henri, celine)
 *   - IndusPro SARL        (yves, martine)
 *
 * Demo password : Demo1234! (all shop users).
 * thomas@buildco.example has ShopUser.enabled = false (error scenario: disabled account).
 */
final class CompanyDemoFixture extends AbstractFixture
{
    public const DEMO_PASSWORD = 'Demo1234!';

    /**
     * @param FactoryInterface<CompanyInterface>      $companyFactory
     * @param FactoryInterface<CustomerInterface>     $customerFactory
     * @param FactoryInterface<ShopUserInterface>     $shopUserFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_company_accounts.factory.company')]
        private readonly FactoryInterface $companyFactory,
        #[Autowire(service: 'tmonier_company_accounts.factory.company_member')]
        private readonly CompanyMemberFactoryInterface $memberFactory,
        #[Autowire(service: 'sylius.factory.customer')]
        private readonly FactoryInterface $customerFactory,
        #[Autowire(service: 'sylius.factory.shop_user')]
        private readonly FactoryInterface $shopUserFactory,
    ) {
    }

    public function load(array $options): void
    {
        // --- Company 1: Acme Industries ---
        $acme = $this->createCompany('ACME_IND', 'Acme Industries', 'FR40123456824', '123 456 824');

        $claire = $this->createCustomer('claire@acme.example', 'Claire', 'Dupont');
        $paul = $this->createCustomer('paul@acme.example', 'Paul', 'Martin');
        $isabelle = $this->createCustomer('isabelle@acme.example', 'Isabelle', 'Renard');

        $acme->addMember($this->memberFactory->createForCustomer($acme, $claire, CompanyMemberInterface::ROLE_BUYER));
        $acme->addMember($this->memberFactory->createForCustomer($acme, $paul, CompanyMemberInterface::ROLE_APPROVER));
        $acme->addMember($this->memberFactory->createForCustomer($acme, $isabelle, CompanyMemberInterface::ROLE_ADMIN));

        $this->entityManager->flush();

        // --- Company 2: TechParts SAS ---
        $techparts = $this->createCompany('TECHPARTS', 'TechParts SAS', 'FR81987654321', '987 654 321');

        $marc = $this->createCustomer('marc@techparts.example', 'Marc', 'Lefebvre');
        $nathalie = $this->createCustomer('nathalie@techparts.example', 'Nathalie', 'Girard');

        $techparts->addMember($this->memberFactory->createForCustomer($techparts, $marc, CompanyMemberInterface::ROLE_BUYER));
        $techparts->addMember($this->memberFactory->createForCustomer($techparts, $nathalie, CompanyMemberInterface::ROLE_ADMIN));

        $this->entityManager->flush();

        // --- Company 3: BuildCo SA ---
        $buildco = $this->createCompany('BUILDCO', 'BuildCo SA', 'FR22345678901', '345 678 901');

        // thomas is disabled (error scenario: disabled account)
        $thomas = $this->createCustomer('thomas@buildco.example', 'Thomas', 'Bernard', enabled: false);
        $antoine = $this->createCustomer('antoine@buildco.example', 'Antoine', 'Moreau');

        $buildco->addMember($this->memberFactory->createForCustomer($buildco, $thomas, CompanyMemberInterface::ROLE_BUYER));
        $buildco->addMember($this->memberFactory->createForCustomer($buildco, $antoine, CompanyMemberInterface::ROLE_APPROVER));

        $this->entityManager->flush();

        // --- Company 4: GlobalMaint SA ---
        $globalmaint = $this->createCompany('GLOBALMAINT', 'GlobalMaint SA', 'FR65432109876', '432 109 876');

        $henri = $this->createCustomer('henri@globalmaint.example', 'Henri', 'Dubois');
        $celine = $this->createCustomer('celine@globalmaint.example', 'Céline', 'Lambert');

        $globalmaint->addMember($this->memberFactory->createForCustomer($globalmaint, $henri, CompanyMemberInterface::ROLE_BUYER));
        $globalmaint->addMember($this->memberFactory->createForCustomer($globalmaint, $celine, CompanyMemberInterface::ROLE_ADMIN));

        $this->entityManager->flush();

        // --- Company 5: IndusPro SARL ---
        $induspro = $this->createCompany('INDUSPRO', 'IndusPro SARL', 'FR93210987654', '210 987 654');

        $yves = $this->createCustomer('yves@induspro.example', 'Yves', 'Fontaine');
        $martine = $this->createCustomer('martine@induspro.example', 'Martine', 'Rousseau');

        $induspro->addMember($this->memberFactory->createForCustomer($induspro, $yves, CompanyMemberInterface::ROLE_BUYER));
        $induspro->addMember($this->memberFactory->createForCustomer($induspro, $martine, CompanyMemberInterface::ROLE_APPROVER));

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_companies';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createCompany(string $code, string $name, ?string $vatNumber, ?string $registrationNumber): CompanyInterface
    {
        /** @var CompanyInterface|null $existing */
        $existing = $this->entityManager->getRepository(CompanyInterface::class)->findOneBy(['code' => $code]);
        if ($existing !== null) {
            return $existing;
        }

        $company = $this->companyFactory->createNew();
        $company->setCode($code);
        $company->setName($name);
        $company->setVatNumber($vatNumber);
        $company->setRegistrationNumber($registrationNumber);
        $company->setEnabled(true);
        $this->entityManager->persist($company);

        return $company;
    }

    private function createCustomer(string $email, string $firstName, string $lastName, bool $enabled = true): CustomerInterface
    {
        /** @var CustomerInterface|null $existing */
        $existing = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => strtolower($email)]);
        if ($existing !== null) {
            return $existing;
        }

        $customer = $this->customerFactory->createNew();
        $customer->setEmail($email);
        $customer->setFirstName($firstName);
        $customer->setLastName($lastName);
        $this->entityManager->persist($customer);

        $user = $this->shopUserFactory->createNew();
        $user->setPlainPassword(self::DEMO_PASSWORD);
        $user->setEnabled($enabled);
        $user->setVerifiedAt(new \DateTime());
        $customer->setUser($user);

        return $customer;
    }
}
