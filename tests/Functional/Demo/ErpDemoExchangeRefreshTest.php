<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ErpRefreshCommand;
use App\Demo\Erp\ErpDemoExchange;
use App\Demo\Erp\ErpDemoSimulator;
use App\Demo\Erp\SimulatorJournal;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\AtomicFileDepositor;
use Tmonier\SyliusErpFlatfilePlugin\Export\DbalExportJournal;
use Tmonier\SyliusErpFlatfilePlugin\Export\DoctrinePendingOrderEvents;
use Tmonier\SyliusErpFlatfilePlugin\Export\OrderExporter;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Payload\OrderPayloadNormalizer;

/**
 * US-195: a deployment starts a new container with an empty file system, while the database (outbox, journal of the export plugin)
 * survives. The refresh run at the start rebuilds the exchange when the directory holds no file of orders: the journal rows that
 * point to files that are gone are forgotten, the placed orders already in the outbox are exported again, the simulator
 * answers once. It never duplicates an outbox event, does nothing when a file is there, and works on a directory of its own here
 * (the tests run in a transaction that is rolled back).
 */
final class ErpDemoExchangeRefreshTest extends KernelTestCase
{
    use DatabaseAccess;

    private string $directory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->db()->beginTransaction();
        $this->directory = sys_get_temp_dir() . '/erp-refresh-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $this->db()->rollBack();
        foreach (array_merge(glob($this->directory . '/.incoming/*') ?: [], glob($this->directory . '/.erp-*') ?: [], glob($this->directory . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        is_dir($this->directory . '/.incoming') && rmdir($this->directory . '/.incoming');
        rmdir($this->directory);
        parent::tearDown();
    }

    public function testAnEmptyDirectoryAfterADeploymentIsRebuiltFromTheOutboxWithoutDuplicatingAnEvent(): void
    {
        $events = $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'");
        self::assertGreaterThan(0, $events, 'the fixtures left placed orders in the outbox');
        self::assertGreaterThan(0, $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'), 'and their journal rows, which survive a deployment');

        self::assertTrue($this->exchange($this->directory)->refreshIfEmpty());

        self::assertCount(1, glob($this->directory . '/orders-*.csv') ?: []);
        self::assertCount(1, glob($this->directory . '/ack-orders-*.csv') ?: []);
        self::assertCount(1, (new SimulatorJournal($this->directory))->all());
        self::assertSame($events, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'"), 'no event written twice');
        self::assertSame($events, $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'), 'every event is in the journal again, once');
    }

    public function testWithoutAnyPlacedOrderInTheOutboxTheLatestOrdersOfEachChannelAreRecordedOnce(): void
    {
        $this->db()->executeStatement("DELETE FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'");
        $this->db()->executeStatement('DELETE FROM tmonier_erp_flatfile_exported_event');

        $this->exchange($this->directory)->refreshIfEmpty();

        $events = $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'");
        self::assertGreaterThan(0, $events);
        self::assertLessThanOrEqual(4, $events, 'four orders per channel at most, and the demo has one channel with orders');
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_webhook_delivery'), 'no webhook is queued');
        self::assertCount(1, glob($this->directory . '/orders-*.csv') ?: []);
    }

    public function testNothingIsDoneWhenAFileOfOrdersIsThere(): void
    {
        file_put_contents($this->directory . '/orders-20261024T000000Z-aaaaaaaa.csv', "already here\n");
        $before = $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event');

        self::assertFalse($this->exchange($this->directory)->refreshIfEmpty());

        self::assertSame(['orders-20261024T000000Z-aaaaaaaa.csv'], array_map('basename', glob($this->directory . '/*') ?: []));
        self::assertSame($before, $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'));
        self::assertSame([], (new SimulatorJournal($this->directory))->all());
    }

    public function testASecondStartIsANoop(): void
    {
        $exchange = $this->exchange($this->directory);
        $exchange->refreshIfEmpty();
        $files = array_map('basename', glob($this->directory . '/*') ?: []);

        self::assertFalse($exchange->refreshIfEmpty());

        self::assertSame($files, array_map('basename', glob($this->directory . '/*') ?: []));
        self::assertCount(1, (new SimulatorJournal($this->directory))->all());
    }

    public function testTheDirectoryIsCreatedWhenTheContainerIsNew(): void
    {
        $fresh = $this->directory . '/new/erp-exchange';

        self::assertTrue($this->exchange($fresh)->refreshIfEmpty());

        self::assertCount(1, glob($fresh . '/orders-*.csv') ?: []);
        foreach (array_merge(glob($fresh . '/.incoming/*') ?: [], glob($fresh . '/.erp-*') ?: [], glob($fresh . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        is_dir($fresh . '/.incoming') && rmdir($fresh . '/.incoming');
        rmdir($fresh);
        rmdir($this->directory . '/new');
    }

    public function testTheCommandSaysWhatItDidAndAFailureIsAClearMessageWithAFailureCode(): void
    {
        $tester = new CommandTester(new ErpRefreshCommand($this->exchange($this->directory)));
        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('rebuilt', $tester->getDisplay());
        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Nothing to do', $tester->getDisplay());

        file_put_contents($this->directory . '/blocker', 'a file, not a directory');
        $failing = new CommandTester(new ErpRefreshCommand($this->exchange($this->directory . '/blocker/erp-exchange')));
        self::assertSame(Command::FAILURE, $failing->execute([]));
        self::assertStringContainsString('could not be rebuilt', $failing->getDisplay());
        self::assertStringNotContainsString($this->directory, $failing->getDisplay(), 'no internal path');
    }

    private function exchange(string $directory): ErpDemoExchange
    {
        $container = self::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $orders = $container->get('sylius.repository.order');
        $channels = $container->get('sylius.repository.channel');
        $normalizer = $container->get('tmonier_webhook.payload.order');
        $class = $container->getParameter('tmonier_webhook.model.outbox_event.class');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        self::assertInstanceOf(OrderRepositoryInterface::class, $orders);
        self::assertInstanceOf(ChannelRepositoryInterface::class, $channels);
        self::assertInstanceOf(OrderPayloadNormalizer::class, $normalizer);
        self::assertIsString($class);
        self::assertTrue(is_a($class, OutboxEventInterface::class, true));

        $journal = new SimulatorJournal($directory);
        $clock = new MockClock('2026-10-24T10:15:00Z');

        return new ErpDemoExchange(
            $entityManager,
            $orders,
            $channels,
            $normalizer,
            new OrderExporter(new DoctrinePendingOrderEvents($entityManager, $class, ['order.placed']), new DbalExportJournal($this->db()), new AtomicFileDepositor($directory), $clock, new NullLogger(), 500),
            new ErpDemoSimulator($directory, $journal, $clock),
            $journal,
            $this->db(),
            $class,
            $directory,
        );
    }
}
