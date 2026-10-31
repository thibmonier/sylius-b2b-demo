<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusRegistrationApprovalPlugin\Entity\Customer\ApprovalAwareInterface;

/**
 * Two registrations waiting for Sophie in Customers > Registrations (US-243): in public mode registrations are closed,
 * so the demo of the Registration Approval plugin starts from these. Fictitious people and companies; nobody signs in
 * with these accounts (random password). The nightly reset puts them back in the pending state.
 *
 * Depends on: nothing but the schema. Priority: -21 (declared in config/packages/demo_fixtures.yaml)
 */
final class PendingRegistrationDemoFixture extends AbstractFixture
{
    /** E-mail, first name, last name. */
    public const REGISTRATIONS = [
        ['nadia.roux@atelier-roux.example', 'Nadia', 'Roux'],
        ['paul.leroy@maintenance-ouest.example', 'Paul', 'Leroy'],
    ];

    /**
     * @param FactoryInterface<CustomerInterface> $customerFactory
     * @param FactoryInterface<ShopUserInterface> $shopUserFactory
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
        foreach (self::REGISTRATIONS as [$email, $firstName, $lastName]) {
            if (null !== $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email])) {
                continue;
            }

            $customer = $this->customerFactory->createNew();
            $customer->setEmail($email);
            $customer->setFirstName($firstName);
            $customer->setLastName($lastName);
            if ($customer instanceof ApprovalAwareInterface) {
                $customer->markApprovalPending();
            }

            $user = $this->shopUserFactory->createNew();
            $user->setPlainPassword(bin2hex(random_bytes(16)));
            $user->setEnabled(true);
            $customer->setUser($user);
            $this->entityManager->persist($customer);
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_pending_registrations';
    }
}
