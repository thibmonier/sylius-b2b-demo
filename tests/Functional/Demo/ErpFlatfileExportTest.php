<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\AtomicFileDepositor;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\ExchangeDirectoryUnavailable;
use Tmonier\SyliusErpFlatfilePlugin\Export\DbalExportJournal;
use Tmonier\SyliusErpFlatfilePlugin\Export\DoctrinePendingOrderEvents;
use Tmonier\SyliusErpFlatfilePlugin\Export\OrderExporter;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Payload\OrderPayloadNormalizer;

/**
 * US-190: the export of the plugin on the placed orders of the demo (outbox events of the `demo` fixtures). Every test runs
 * in a transaction that is rolled back: the journal of the demo database is left as the fixtures made it.
 */
final class ErpFlatfileExportTest extends KernelTestCase
{
    use DatabaseAccess;

    private string $directory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->db()->beginTransaction();
        $this->directory = sys_get_temp_dir() . '/demo-erp-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $this->db()->rollBack();
        foreach (array_merge(glob($this->directory . '/.incoming/*') ?: [], glob($this->directory . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        is_dir($this->directory . '/.incoming') && rmdir($this->directory . '/.incoming');
        rmdir($this->directory);
        parent::tearDown();
    }

    public function testThePlacedOrdersOfTheDemoAreExportedAsAFileOfTheContract(): void
    {
        $this->recordPlacedEventsOfDemoOrders(2);

        $result = $this->exporter($this->directory)->export();

        self::assertNotNull($result->fileName);
        $lines = explode("\n", rtrim((string) file_get_contents($this->directory . '/' . $result->fileName), "\n"));
        self::assertSame(implode(';', OrderLinesCsv::HEADER), $lines[0]);
        self::assertSame($result->lines, \count($lines) - 1);
        foreach (\array_slice($lines, 1) as $line) {
            $fields = explode(';', $line);
            self::assertCount(10, $fields);
            self::assertStringEndsWith('.example', $fields[4], 'the demo holds no real address');
        }
    }

    public function testAnUnavailableDirectoryLeavesNoFileAndTheEventsToExport(): void
    {
        $this->recordPlacedEventsOfDemoOrders(2);
        $pending = $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' AND anonymized_at IS NULL");

        try {
            $this->exporter($this->directory . '/absent')->export();
            self::fail('A missing exchange directory must be refused.');
        } catch (ExchangeDirectoryUnavailable) {
        }

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'));
        self::assertSame($pending, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' AND anonymized_at IS NULL"));
        self::assertSame([], glob($this->directory . '/*'));
    }

    public function testTheExporterOfThePluginIsAvailableToTheConsoleCommand(): void
    {
        self::assertInstanceOf(OrderExporter::class, self::getContainer()->get(OrderExporter::class));
    }

    /** The fixtures write no outbox event: the event of a placed order is built from two orders of the demo, as the plugin captures it. */
    private function recordPlacedEventsOfDemoOrders(int $count): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $normalizer = self::getContainer()->get('tmonier_webhook.payload.order');
        self::assertInstanceOf(OrderPayloadNormalizer::class, $normalizer);
        $class = self::getContainer()->getParameter('tmonier_webhook.model.outbox_event.class');
        self::assertIsString($class);

        $repository = self::getContainer()->get('sylius.repository.order');
        self::assertInstanceOf(OrderRepositoryInterface::class, $repository);
        $orders = $repository->findBy(['state' => 'new'], ['id' => 'ASC'], $count);
        self::assertCount($count, $orders, 'the demo fixtures place orders');
        foreach ($orders as $order) {
            self::assertInstanceOf(OrderInterface::class, $order);
            $entityManager->persist(new $class('order.placed', 1, new \DateTimeImmutable('2026-10-24T10:00:00Z'), $normalizer->normalize($order), $order->getChannel()?->getCode(), 'order', (string) $order->getId(), null, Uuid::v7()->toRfc4122()));
        }
        $entityManager->flush();
    }

    private function exporter(string $directory): OrderExporter
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $class = self::getContainer()->getParameter('tmonier_webhook.model.outbox_event.class');
        self::assertIsString($class);
        self::assertTrue(is_a($class, OutboxEventInterface::class, true));

        return new OrderExporter(
            new DoctrinePendingOrderEvents($entityManager, $class, ['order.placed']),
            new DbalExportJournal($this->db()),
            new AtomicFileDepositor($directory),
            new MockClock('2026-10-24T10:15:00Z'),
            new NullLogger(),
            500,
        );
    }
}
