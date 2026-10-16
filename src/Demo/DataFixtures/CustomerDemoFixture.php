<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Account\DemoAccount;
use App\Demo\Account\DemoAccountCatalog;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates the non-B2B demo shop accounts (US-009), read from DemoAccountCatalog (US-015).
 *
 * Note: Sophie (back-office admin, restricted role) is already created by the
 * `admin_user` fixture in demo_fixtures.yaml (sprint 1 / US-007).
 */
final class CustomerDemoFixture extends AbstractFixture
{
    /**
     * @param FactoryInterface<CustomerInterface>  $customerFactory
     * @param FactoryInterface<ShopUserInterface>  $shopUserFactory
     */
    public function __construct(
        private readonly DemoAccountCatalog $catalog,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.factory.customer')]
        private readonly FactoryInterface $customerFactory,
        #[Autowire(service: 'sylius.factory.shop_user')]
        private readonly FactoryInterface $shopUserFactory,
    ) {
    }

    public function load(array $options): void
    {
        foreach ($this->catalog->consumers() as $consumer) {
            $this->createCustomer($consumer);
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_customers';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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
        $user->setEnabled(true);
        $user->setVerifiedAt(new \DateTime());
        $customer->setUser($user);

        return $customer;
    }
}
