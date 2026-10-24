<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Erp\ErpDemoSimulator;
use App\Demo\Erp\SimulatorJournal;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\ExchangeDirectoryUnavailable;
use Tmonier\SyliusErpFlatfilePlugin\Export\OrderExporter;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Payload\OrderPayloadNormalizer;

/**
 * US-193: the last placed orders of each channel become `order.placed` events of the outbox (written directly: no webhook delivery is
 * queued by the fixtures), then the export of the flat-file plugin runs once, as a cron would: the page "Flat-file exchange" shows a real
 * file; US-188: then the simulated ERP acknowledges it. The exchange directory is the configured one (the cache of the worker in the tests),
 * emptied first (files of orders, acknowledgements, journal of the simulator); a reset reloads the same state.
 */
final class ErpExportDemoFixture extends AbstractFixture
{
    private const PER_CHANNEL = 4;

    /**
     * @param class-string<OutboxEventInterface> $outboxEventClass
     * @param OrderRepositoryInterface<OrderInterface> $orders
     * @param ChannelRepositoryInterface<ChannelInterface> $channels
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.repository.order')]
        private readonly OrderRepositoryInterface $orders,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly ChannelRepositoryInterface $channels,
        #[Autowire(service: 'tmonier_webhook.payload.order')]
        private readonly OrderPayloadNormalizer $normalizer,
        private readonly OrderExporter $exporter,
        private readonly ErpDemoSimulator $simulator,
        private readonly SimulatorJournal $journal,
        private readonly LoggerInterface $logger,
        #[Autowire(param: 'tmonier_webhook.model.outbox_event.class')]
        private readonly string $outboxEventClass,
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private readonly string $directory,
    ) {
    }

    public function load(array $options): void
    {
        $this->emptyTheExchangeDirectory();
        foreach ($this->channels->findAll() as $channel) {
            foreach ($this->orders->findBy(['channel' => $channel, 'checkoutState' => 'completed'], ['checkoutCompletedAt' => 'DESC'], self::PER_CHANNEL) as $order) {
                $this->entityManager->persist($this->placed($order));
            }
        }
        $this->entityManager->flush();

        try {
            $this->exporter->export();
            // US-188: "ERP Démo (fictif)" acknowledges the file, as it would on its next pass.
            $this->simulator->process();
        } catch (ExchangeDirectoryUnavailable $e) {
            $this->logger->warning('The demo export could not write its file: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'demo_erp_export';
    }

    private function placed(OrderInterface $order): OutboxEventInterface
    {
        $class = $this->outboxEventClass;

        return new $class(
            'order.placed',
            OrderPayloadNormalizer::VERSION,
            \DateTimeImmutable::createFromInterface($order->getCheckoutCompletedAt() ?? new \DateTimeImmutable()),
            $this->normalizer->normalize($order),
            $order->getChannel()?->getCode(),
            'order',
            (string) $order->getId(),
            $order->getCustomer()?->getId(),
            Uuid::v7()->toRfc4122(),
        );
    }

    private function emptyTheExchangeDirectory(): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->directory);
        foreach ([...(glob(rtrim($this->directory, '/') . '/orders-*.csv') ?: []), ...(glob(rtrim($this->directory, '/') . '/ack-*.csv') ?: [])] as $file) {
            $filesystem->remove($file);
        }
        $this->journal->clear();
    }
}
