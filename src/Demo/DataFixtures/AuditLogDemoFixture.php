<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Entity\Customer\Customer;
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
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMember;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\MemberLimit;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\OrderApproval;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\OrderApprovalInterface;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\ApprovalDecision;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\ApprovalStatus;

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
    private const REGISTERED_CUSTOMER = 'isabelle@acme.example';

    private const REGISTRATION_REASON = 'Extrait Kbis vérifié, compte rattaché à Acme Industrie.';

    private const LIMITED_BUYER = 'claire@acme.example';

    private const PREVIOUS_ORDER_LIMIT = 30_000;

    private const APPROVED_ORDER = 'ACME-PO-2026-0458';

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

        $entries = [...$this->priceEntries($admin), ...$this->registrationEntries($admin), ...$this->ceilingEntries($admin), ...$this->approvalEntries(), ...$this->noteEntries($admin)];
        $this->entityManager->flush();
        $this->writer->write($this->entityManager, $entries);
    }

    public function getName(): string
    {
        return 'demo_audit_log';
    }

    /** @return list<AuditLogEntryInterface> */
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
    /** @return list<AuditLogEntryInterface> */
    private function registrationEntries(AdminUserInterface $admin): array
    {
        $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => self::REGISTERED_CUSTOMER]);
        if (!$customer instanceof Customer) {
            return [];
        }

        $entry = $this->entry($admin, AuditLogEntryInterface::ACTION_UPDATE, 'customer', $customer->getId(), (string) $customer->getEmail(), 6, 'sylius_admin_customer_update');
        $customer->decideApproval(ApprovalStatus::Approved, new ApprovalDecision($entry->getCreatedAt() ?? new \DateTimeImmutable(), $entry->getActorName() ?? '', self::REGISTRATION_REASON));
        $entry->setChanges(['approvalStatus' => ['old' => ApprovalStatus::Pending->value, 'new' => ApprovalStatus::Approved->value]]);

        return [$entry];
    }

    /** @return list<AuditLogEntryInterface> */
    private function ceilingEntries(AdminUserInterface $admin): array
    {
        $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => self::LIMITED_BUYER]);
        $member = $customer instanceof CustomerInterface ? $this->entityManager->getRepository(CompanyMember::class)->findOneBy(['customer' => $customer]) : null;
        $limit = null === $member ? null : $this->entityManager->getRepository(MemberLimit::class)->findOneBy(['member' => $member]);
        if (null === $limit || null === $limit->getOrderLimit()) {
            return [];
        }

        $entry = $this->entry($admin, AuditLogEntryInterface::ACTION_UPDATE, 'member_limit', $limit->getId(), (string) $customer->getEmail(), 5, 'sylius_admin_member_limit_update');
        $entry->setChanges(['orderLimit' => ['old' => self::PREVIOUS_ORDER_LIMIT, 'new' => $limit->getOrderLimit()]]);

        return [$entry];
    }

    /**
     * The approval was really decided by the approver when the order history was loaded: the event replays that decision.
     *
     * @return list<AuditLogEntryInterface>
     */
    private function approvalEntries(): array
    {
        $order = $this->entityManager->getRepository(OrderInterface::class)->findOneBy(['poReference' => self::APPROVED_ORDER]);
        $approval = null === $order ? null : $this->entityManager->getRepository(OrderApproval::class)->findOneBy(['order' => $order]);
        $approver = $approval?->getDecidedBy();
        if (null === $approval || null === $approver || null === $approval->getDecidedAt()) {
            return [];
        }

        $entry = $this->entry(null, AuditLogEntryInterface::ACTION_UPDATE, 'order_approval', $approval->getId(), '#' . $order->getNumber(), 0, 'tmonier_purchase_approval_shop_approval_approve');
        $entry->setCreatedAt($approval->getDecidedAt());
        $entry->setActorType(AuditLogEntryInterface::ACTOR_CUSTOMER);
        $entry->setActorId($approver->getId());
        $entry->setActorName(trim($approver->getFirstName() . ' ' . $approver->getLastName()));
        $entry->setSource(AuditLogEntryInterface::SOURCE_SHOP);
        $entry->setChanges(['state' => ['old' => OrderApprovalInterface::STATE_PENDING, 'new' => OrderApprovalInterface::STATE_APPROVED]]);

        return [$entry];
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

    private function entry(?AdminUserInterface $admin, string $action, string $resource, mixed $id, string $label, int $daysAgo, string $route): AuditLogEntryInterface
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
        $entry->setActorId($admin?->getId());
        $entry->setActorName(null === $admin ? null : trim($admin->getFirstName() . ' ' . $admin->getLastName()));
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
            'member_limit' => MemberLimit::class,
            'order_approval' => OrderApproval::class,
            default => OrderInterface::class,
        };

        return $this->entityManager->getClassMetadata($interface)->getName();
    }
}
