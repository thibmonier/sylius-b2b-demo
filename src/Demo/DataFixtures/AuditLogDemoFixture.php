<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusAuditLogPlugin\Entity\AuditLogEntryInterface;
use Tmonier\SyliusAuditLogPlugin\Recorder\AuditLogWriter;

/**
 * US-097 (T-097-06): a few business events in the audit log. The reset runs in a command the audit log ignores (its
 * thousand "creation" lines would bury the visitors' changes, US-087), so the log starts empty; these entries are the
 * changes the back office team made during the last two weeks. Each one describes a change that is really in the
 * data (the price, the phone number and the note are the loaded ones), written like the recorder writes it.
 *
 * Depends on: products (-30), customers (-15), the order history (-60), admin users (0). Priority: -74.
 */
final class AuditLogDemoFixture extends AbstractFixture
{
    private const ADMIN_EMAIL = 'sophie@fournitures-pro.example';

    private const NOTE_ORDER = 'ACME-PO-2026-0634';

    private const NOTE = 'Commande au-dessus du plafond de Claire : validation demandée à Paul Martin.';

    /** Variant code => price before the reduction, as a share of the current price (percent). */
    private const REDUCED_PRICES = ['EPI-GANT-001' => 106, 'OUTI-001' => 104];

    /** Customer e-mail => phone number entered by the team. */
    private const PHONES = ['claire@acme.example' => '+33 4 72 00 01 10', 'isabelle@acme.example' => '+33 4 72 00 01 11'];

    /** @param FactoryInterface<AuditLogEntryInterface> $entryFactory */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_audit_log.factory.audit_log_entry')]
        private readonly FactoryInterface $entryFactory,
        #[Autowire(service: 'tmonier_audit_log.writer')]
        private readonly AuditLogWriter $writer,
    ) {
    }

    public function load(array $options): void
    {
        $admin = $this->entityManager->getRepository(AdminUserInterface::class)->findOneBy(['email' => self::ADMIN_EMAIL]);
        if (!$admin instanceof AdminUserInterface) {
            return;
        }

        $entries = [...$this->priceEntries($admin), ...$this->phoneEntries($admin), ...$this->noteEntries($admin)];
        $this->entityManager->flush();
        $this->writer->write($this->entityManager, $entries);
    }

    public function getName(): string
    {
        return 'demo_audit_log';
    }

    /** @return list<AuditLogEntryInterface> */
    private function priceEntries(AdminUserInterface $admin): array
    {
        $entries = [];
        $days = 9;
        foreach (self::REDUCED_PRICES as $code => $previousShare) {
            $variant = $this->entityManager->getRepository(ProductVariantInterface::class)->findOneBy(['code' => $code]);
            $pricing = $variant instanceof ProductVariantInterface ? $this->entityManager->getRepository(ChannelPricingInterface::class)->findOneBy(['productVariant' => $variant, 'channelCode' => 'B2B']) : null;
            if (!$variant instanceof ProductVariantInterface || null === $pricing || null === $pricing->getPrice()) {
                continue;
            }

            $entry = $this->entry($admin, AuditLogEntryInterface::ACTION_UPDATE, 'channel_pricing', $pricing->getId(), sprintf('%s (%s)', $variant->getProduct()?->getName(), $code), $days, 'sylius_admin_product_variant_update');
            $entry->setOwnerType('product_variant');
            $entry->setOwnerId((string) $variant->getId());
            $entry->setChanges(['price' => ['old' => (int) round($pricing->getPrice() * $previousShare / 100), 'new' => $pricing->getPrice()]]);
            $entries[] = $entry;
            $days -= 3;
        }

        return $entries;
    }

    /** @return list<AuditLogEntryInterface> */
    private function phoneEntries(AdminUserInterface $admin): array
    {
        $entries = [];
        $days = 8;
        foreach (self::PHONES as $email => $phone) {
            $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
            if (!$customer instanceof CustomerInterface) {
                continue;
            }

            $customer->setPhoneNumber($phone);
            $entry = $this->entry($admin, AuditLogEntryInterface::ACTION_UPDATE, 'customer', $customer->getId(), (string) $customer->getEmail(), $days, 'sylius_admin_customer_update');
            $entry->setChanges(['phoneNumber' => ['old' => null, 'new' => $phone]]);
            $entries[] = $entry;
            --$days;
        }

        return $entries;
    }

    /** @return list<AuditLogEntryInterface> */
    private function noteEntries(AdminUserInterface $admin): array
    {
        $order = $this->entityManager->getRepository(OrderInterface::class)->findOneBy(['poReference' => self::NOTE_ORDER]);
        if (!$order instanceof OrderInterface) {
            return [];
        }

        $order->setNotes(self::NOTE);
        $entry = $this->entry($admin, AuditLogEntryInterface::ACTION_UPDATE, 'order', $order->getId(), '#' . $order->getNumber(), 3, 'sylius_admin_order_update');
        $entry->setChanges(['notes' => ['old' => null, 'new' => self::NOTE]]);

        return [$entry];
    }

    private function entry(AdminUserInterface $admin, string $action, string $resource, mixed $id, string $label, int $daysAgo, string $route): AuditLogEntryInterface
    {
        $entry = $this->entryFactory->createNew();
        $entry->setCreatedAt((new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->modify(sprintf('-%d days', $daysAgo))->setTime(8 + $daysAgo % 8, 15 + $daysAgo));
        $entry->setAction($action);
        $entry->setResourceType($resource);
        $entry->setResourceClass($this->classOf($resource));
        $entry->setResourceId(is_scalar($id) ? (string) $id : null);
        $entry->setResourceLabel($label);
        $entry->setOwnerType($resource);
        $entry->setOwnerId(is_scalar($id) ? (string) $id : null);
        $entry->setActorType(AuditLogEntryInterface::ACTOR_ADMIN);
        $entry->setActorId($admin->getId());
        $entry->setActorName(trim($admin->getFirstName() . ' ' . $admin->getLastName()));
        $entry->setSource(AuditLogEntryInterface::SOURCE_ADMIN);
        $entry->setIpAddress('203.0.113.' . (20 + $daysAgo));
        $entry->setRequestId(substr(md5($resource . $label . $daysAgo), 0, 16));
        $entry->setRoute($route);

        return $entry;
    }

    private function classOf(string $resource): string
    {
        $interface = match ($resource) {
            'channel_pricing' => ChannelPricingInterface::class,
            'customer' => CustomerInterface::class,
            default => OrderInterface::class,
        };

        return $this->entityManager->getClassMetadata($interface)->getName();
    }
}
