<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Account\DemoAccount;
use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Account\DemoCompany;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Factory\CompanyMemberFactoryInterface;

/**
 * Creates the demo B2B companies and their members (US-009).
 *
 * Companies, members, roles and the demo password come from DemoAccountCatalog
 * (config/demo/accounts.yaml, US-015). A disabled member gets a disabled ShopUser.
 */
final class CompanyDemoFixture extends AbstractFixture
{
    /**
     * @param FactoryInterface<CompanyInterface>      $companyFactory
     * @param FactoryInterface<CustomerInterface>     $customerFactory
     * @param FactoryInterface<ShopUserInterface>     $shopUserFactory
     */
    public function __construct(
        private readonly DemoAccountCatalog $catalog,
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
        foreach ($this->catalog->companies() as $demoCompany) {
            $company = $this->createCompany($demoCompany);

            foreach ($demoCompany->members as $member) {
                $customer = $this->createCustomer($member);
                $companyMember = $this->memberFactory->createForCustomer($company, $customer, $member->role);
                // A member who left is disabled as a member too: the purchasing dashboard marks his past orders (US-155).
                $companyMember->setEnabled($member->enabled);
                $company->addMember($companyMember);
            }

            $this->entityManager->flush();
        }
    }

    public function getName(): string
    {
        return 'demo_companies';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createCompany(DemoCompany $demo): CompanyInterface
    {
        /** @var CompanyInterface|null $existing */
        $existing = $this->entityManager->getRepository(CompanyInterface::class)->findOneBy(['code' => $demo->code]);
        if ($existing !== null) {
            return $existing;
        }

        $company = $this->companyFactory->createNew();
        $company->setCode($demo->code);
        $company->setName($demo->name);
        $company->setVatNumber($demo->vatNumber);
        $company->setRegistrationNumber($demo->registrationNumber);
        $company->setEnabled(true);
        $this->entityManager->persist($company);

        return $company;
    }

    private function createCustomer(DemoAccount $account): CustomerInterface
    {
        /** @var CustomerInterface|null $existing */
        $existing = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => strtolower($account->email)]);
        if ($existing !== null) {
            return $existing;
        }

        $customer = $this->customerFactory->createNew();
        $customer->setEmail($account->email);
        $customer->setFirstName($account->firstName);
        $customer->setLastName($account->lastName);
        $this->entityManager->persist($customer);

        $user = $this->shopUserFactory->createNew();
        $user->setPlainPassword($account->password);
        $user->setEnabled($account->enabled);
        $user->setVerifiedAt(new \DateTime());
        $customer->setUser($user);

        return $customer;
    }
}
