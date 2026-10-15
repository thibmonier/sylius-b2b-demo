<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates the non-B2B demo shop accounts (US-009).
 *
 *   - Léa Petit  lea@demo.example  — B2C customer (shop user)
 *
 * Note: Sophie (back-office admin, restricted role) is already created by the
 * `admin_user` fixture in demo_fixtures.yaml (sprint 1 / US-007).
 *
 * Demo password for all accounts: Demo1234!
 */
final class CustomerDemoFixture extends AbstractFixture
{
    /**
     * @param FactoryInterface<CustomerInterface>  $customerFactory
     * @param FactoryInterface<ShopUserInterface>  $shopUserFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.factory.customer')]
        private readonly FactoryInterface $customerFactory,
        #[Autowire(service: 'sylius.factory.shop_user')]
        private readonly FactoryInterface $shopUserFactory,
    ) {
    }

    public function load(array $options): void
    {
        $this->createCustomer('lea@demo.example', 'Léa', 'Petit');
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_customers';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createCustomer(string $email, string $firstName, string $lastName): CustomerInterface
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
        $user->setPlainPassword(CompanyDemoFixture::DEMO_PASSWORD);
        $user->setEnabled(true);
        $user->setVerifiedAt(new \DateTime());
        $customer->setUser($user);

        return $customer;
    }
}
