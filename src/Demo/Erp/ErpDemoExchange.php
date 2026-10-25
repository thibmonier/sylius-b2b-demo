<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;
use Tmonier\SyliusErpFlatfilePlugin\Export\OrderExporter;
use Tmonier\SyliusErpFlatfilePlugin\Import\ExchangeDirectoryImporter;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Payload\OrderPayloadNormalizer;

/**
 * The state of the exchange directory of the demo (US-193, US-188, US-195): the last placed orders of each channel are `order.placed`
 * events of the outbox (written directly: no webhook delivery is queued), one export writes their file, one pass of "ERP Démo (fictif)"
 * acknowledges it. {@see build()} does it from an emptied directory (the fixtures, so the nightly reset); {@see refreshIfEmpty()} does it
 * at the start of a container: a deployment brings a new file system while the database (outbox, journal of the export plugin) survives,
 * so the journal rows that point to files that are gone are forgotten and the events already in the outbox are exported again,
 * never written twice. Nothing is done when a file of orders is there.
 */
final readonly class ErpDemoExchange
{
    private const PER_CHANNEL = 4;

    /**
     * @param OrderRepositoryInterface<OrderInterface> $orders
     * @param ChannelRepositoryInterface<ChannelInterface> $channels
     * @param class-string<OutboxEventInterface> $outboxEventClass
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.repository.order')]
        private OrderRepositoryInterface $orders,
        #[Autowire(service: 'sylius.repository.channel')]
        private ChannelRepositoryInterface $channels,
        #[Autowire(service: 'tmonier_webhook.payload.order')]
        private OrderPayloadNormalizer $normalizer,
        private OrderExporter $exporter,
        private ErpDemoSimulator $simulator,
        private SimulatorJournal $journal,
        private DemoPriceStockFiles $files,
        private ExchangeDirectoryImporter $imports,
        #[Autowire(service: 'doctrine.dbal.default_connection')]
        private Connection $connection,
        #[Autowire(param: 'tmonier_webhook.model.outbox_event.class')]
        private string $outboxEventClass,
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
    ) {
    }

    /** From an emptied directory: events of the latest orders, one export, one pass of the simulator. */
    public function build(): void
    {
        $this->emptyTheExchangeDirectory();
        $this->recordTheLatestOrders();
        $this->exporter->export();
        $this->simulator->process();
        // US-187 (B): the simulated ERP sends its files of prices and stock, which the plugin imports.
        $this->files->deposit();
        $this->imports->importPending();
    }

    /** @return bool true when the exchange was rebuilt, false when a file of orders was already there */
    public function refreshIfEmpty(): bool
    {
        if ([] !== (glob(rtrim($this->directory, '/') . '/orders-*.csv') ?: [])) {
            return false;
        }

        // No file of orders: the rows of the journals of the export plugin (orders exported, batches imported) point to files that are gone.
        $this->connection->executeStatement('DELETE FROM tmonier_erp_flatfile_exported_event');
        $this->build();

        return true;
    }

    private function recordTheLatestOrders(): void
    {
        $known = [];
        foreach ($this->connection->fetchFirstColumn("SELECT resource_id FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'") as $resourceId) {
            $known[\is_scalar($resourceId) ? (string) $resourceId : ''] = true;
        }
        foreach ($this->channels->findAll() as $channel) {
            foreach ($this->orders->findBy(['channel' => $channel, 'checkoutState' => 'completed'], ['checkoutCompletedAt' => 'DESC'], self::PER_CHANNEL) as $order) {
                if (!isset($known[(string) $order->getId()])) {
                    $this->entityManager->persist($this->placed($order));
                }
            }
        }
        $this->entityManager->flush();
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
        $root = rtrim($this->directory, '/');
        $files = [];
        foreach (['orders-*.csv', 'ack-*.csv', 'prices-*.csv', 'stock-*.csv', ExchangeDirectoryImporter::PROCESSED . '/*.csv'] as $pattern) {
            $files = [...$files, ...(glob($root . '/' . $pattern) ?: [])];
        }
        $filesystem->remove($files);
        $this->journal->clear();
        $this->connection->executeStatement('DELETE FROM tmonier_erp_flatfile_import_batch');
    }
}
