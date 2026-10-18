<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusAdminOrderCreationPlugin\Draft\DraftOrderFactoryInterface;
use Tmonier\SyliusAdminOrderCreationPlugin\Draft\DraftOrderModifierInterface;
use Tmonier\SyliusAdminOrderCreationPlugin\Entity\AdminOrderCreationInterface;

/**
 * US-097: two orders in preparation (drafts) started by Sophie in the back office for customers who phoned in, so that
 * "Commandes en préparation" is not empty. Nothing in them is invented: customers and variants are the demo ones.
 *
 * Depends on: products (-30), customers (-15), admin users (0). Priority: -72 (before the webhook endpoint).
 */
final class DraftOrderDemoFixture extends AbstractFixture
{
    private const ADMIN_EMAIL = 'sophie@fournitures-pro.example';

    /** Customer e-mail => quantity by variant code. */
    private const DRAFTS = [
        'henri@globalmaint.example' => ['EPI-CASK-001' => 15, 'EPI-GANT-001' => 30],
        'marc@techparts.example' => ['VISS-001' => 20, 'OUTI-001' => 4],
    ];

    /**
     * @param RepositoryInterface<ChannelInterface>        $channelRepository
     * @param RepositoryInterface<CustomerInterface>       $customerRepository
     * @param RepositoryInterface<ProductVariantInterface> $variantRepository
     * @param RepositoryInterface<AdminUserInterface>      $adminUserRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DraftOrderFactoryInterface $draftFactory,
        private readonly DraftOrderModifierInterface $draftModifier,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.product_variant')]
        private readonly RepositoryInterface $variantRepository,
        #[Autowire(service: 'sylius.repository.admin_user')]
        private readonly RepositoryInterface $adminUserRepository,
    ) {
    }

    public function load(array $options): void
    {
        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']);
        $admin = $this->adminUserRepository->findOneBy(['email' => self::ADMIN_EMAIL]);
        if (!$channel instanceof ChannelInterface || !$admin instanceof AdminUserInterface) {
            return;
        }

        foreach (self::DRAFTS as $email => $items) {
            $customer = $this->customerRepository->findOneBy(['emailCanonical' => $email]);
            if ($customer instanceof CustomerInterface && !$this->hasDraft($customer)) {
                $this->createDraft($channel, $customer, $admin, $items);
            }
        }
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_draft_orders';
    }

    /** @param array<string, int> $items quantity by variant code */
    private function createDraft(ChannelInterface $channel, CustomerInterface $customer, AdminUserInterface $admin, array $items): void
    {
        $draft = $this->draftFactory->create($channel, $customer, null, $admin);
        $order = $draft->getOrder();
        if (null === $order) {
            return;
        }

        foreach ($this->variantRepository->findBy(['code' => array_keys($items)]) as $variant) {
            $this->draftModifier->addVariant($order, $variant, $items[(string) $variant->getCode()]);
        }
    }

    private function hasDraft(CustomerInterface $customer): bool
    {
        return null !== $this->entityManager->createQuery(
            'SELECT d.id FROM ' . AdminOrderCreationInterface::class . ' d JOIN d.order o WHERE o.customer = :customer AND d.completedAt IS NULL',
        )->setParameter('customer', $customer)->setMaxResults(1)->getOneOrNullResult();
    }
}
