<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use App\Demo\Order\DeterministicOrderFactory;
use App\Demo\Order\OrderScenario;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalDecisionMaker;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\Decider;
use Tmonier\SyliusPurchaseApprovalPlugin\Entity\OrderApprovalInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\OrderApprovalRepositoryInterface;

/**
 * US-011: the order history of the demo.
 *
 * Orders are placed through the real services (Sylius order factory and state machines), so the plugins
 * react as in production: company accounts links the order to the company, purchase approval requests an
 * approval above the buyer limit, invoicing creates the invoice and Factur-X turns it into a PDF/A-3.
 * The clock is frozen on each order date so that invoices are dated like their orders.
 *
 * Depends on: products (-30), companies and customers (-15), limits (-40), quotes (-50). Priority: -60.
 */
final class OrderHistoryDemoFixture extends AbstractFixture
{
    /**
     * @param RepositoryInterface<ChannelInterface>  $channelRepository
     * @param RepositoryInterface<CustomerInterface> $customerRepository
     * @param RepositoryInterface<OrderInterface>    $orderRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeterministicOrderFactory $orderFactory,
        private readonly StateMachineInterface $stateMachine,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.order')]
        private readonly RepositoryInterface $orderRepository,
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $memberRepository,
        #[Autowire(service: 'tmonier_purchase_approval.repository.order_approval')]
        private readonly OrderApprovalRepositoryInterface $approvalRepository,
        private readonly ApprovalDecisionMaker $approvalDecisionMaker,
        #[Autowire('%sylius_invoicing.invoice_save_path%')]
        private readonly string $invoiceDirectory,
    ) {
    }

    public function load(array $options): void
    {
        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']) ?? $this->channelRepository->findOneBy(['enabled' => true]);
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $this->removeStaleInvoiceFiles();
        $now = new \DateTimeImmutable();

        try {
            foreach (DemoOrderPlan::all() as $spec) {
                $this->placeOrder($spec, $channel, DemoOrderPlan::completedAt($now, $spec->daysAgo));
            }
        } finally {
            Clock::set(new NativeClock());
        }
    }

    public function getName(): string
    {
        return 'demo_orders';
    }

    /** The purger empties the database, not the invoice PDFs, and the invoicing plugin refuses to overwrite one. */
    private function removeStaleInvoiceFiles(): void
    {
        if (null !== $this->orderRepository->findOneBy([])) {
            return;
        }

        foreach (glob(rtrim($this->invoiceDirectory, '/') . '/*.pdf') ?: [] as $file) {
            unlink($file);
        }
    }

    private function placeOrder(DemoOrderSpec $spec, ChannelInterface $channel, \DateTimeImmutable $date): void
    {
        if (null !== $this->orderRepository->findOneBy(['poReference' => $spec->poReference])) {
            return;
        }

        $customer = $this->customer($spec->customerEmail);
        Clock::set(new MockClock($date));

        $order = $this->orderFactory->create([
            'channel' => $channel,
            'customer' => $customer,
            'country' => 'FR',
            'company' => $this->memberRepository->findOneByCustomer($customer)?->getCompany()?->getName(),
            'complete_date' => \DateTime::createFromImmutable($date),
            'items' => $spec->items,
            'po_reference' => $spec->poReference,
            'cost_center' => $spec->costCenter,
        ]);
        $order->setCreatedAt(\DateTime::createFromImmutable($date));
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->advance($order, $spec, $date);
    }

    private function advance(OrderInterface $order, DemoOrderSpec $spec, \DateTimeImmutable $date): void
    {
        $afterPlacing = static fn (int $days): MockClock => new MockClock($date->modify(sprintf('+%d days', $days)));

        $needsApproval = OrderScenario::PendingApproval === $spec->scenario || OrderScenario::ApprovedShipped === $spec->scenario;
        if (!$needsApproval && null !== $this->approvalRepository->findOneByOrder($order)) {
            throw new \LogicException(sprintf('Demo order %s is over the buyer limit: it would wait for an approval.', $spec->poReference));
        }
        if (OrderScenario::PendingApproval === $spec->scenario) {
            $this->approvalOf($order);

            return;
        }
        if (OrderScenario::ApprovedShipped === $spec->scenario) {
            Clock::set($afterPlacing(1));
            $this->approve($order, $spec);
        }

        match ($spec->scenario) {
            OrderScenario::Cancelled => $this->cancel($order),
            OrderScenario::AwaitingPayment => null,
            default => $this->pay($order),
        };

        if (OrderScenario::Shipped === $spec->scenario || OrderScenario::ApprovedShipped === $spec->scenario) {
            Clock::set($afterPlacing(3));
            $this->apply($order->getShipments(), ShipmentTransitions::GRAPH, ShipmentTransitions::TRANSITION_SHIP);
        }
        if (OrderScenario::Refunded === $spec->scenario) {
            Clock::set($afterPlacing(10));
            $this->apply($order->getPayments(), PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_REFUND);
        }
        $this->entityManager->flush();
    }

    private function pay(OrderInterface $order): void
    {
        $this->apply($order->getPayments(), PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);
    }

    private function cancel(OrderInterface $order): void
    {
        $this->stateMachine->apply($order, OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL);
    }

    private function approve(OrderInterface $order, DemoOrderSpec $spec): void
    {
        $approval = $this->approvalOf($order);
        $approver = $this->memberRepository->findOneByCustomer($this->customer((string) $spec->approverEmail));
        if (null === $approver) {
            throw new \LogicException(sprintf('Demo order %s: approver "%s" is not a company member.', $spec->poReference, $spec->approverEmail));
        }

        $error = $this->approvalDecisionMaker->approve($approval, Decider::member($approver), 'Commande validée, besoin chantier confirmé.');
        if (null !== $error) {
            throw new \LogicException(sprintf('Demo order %s: approval refused (%s).', $spec->poReference, $error));
        }
    }

    private function approvalOf(OrderInterface $order): OrderApprovalInterface
    {
        $approval = $this->approvalRepository->findOneByOrder($order);
        if (null === $approval) {
            throw new \LogicException(sprintf('Demo order %s should be over the buyer limit and wait for an approval.', $order->getNumber()));
        }

        return $approval;
    }

    /** @param iterable<object> $subjects */
    private function apply(iterable $subjects, string $graph, string $transition): void
    {
        foreach ($subjects as $subject) {
            if ($this->stateMachine->can($subject, $graph, $transition)) {
                $this->stateMachine->apply($subject, $graph, $transition);
            }
        }
    }

    private function customer(string $email): CustomerInterface
    {
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => $email]);
        if (!$customer instanceof CustomerInterface) {
            throw new \LogicException(sprintf('Demo order history: customer "%s" does not exist.', $email));
        }

        return $customer;
    }
}
