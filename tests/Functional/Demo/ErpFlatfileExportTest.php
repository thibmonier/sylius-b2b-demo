<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\AtomicFileDepositor;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\ExchangeDirectoryUnavailable;
use Tmonier\SyliusErpFlatfilePlugin\Export\DbalExportJournal;
use Tmonier\SyliusErpFlatfilePlugin\Export\DoctrinePendingOrderEvents;
use Tmonier\SyliusErpFlatfilePlugin\Export\OrderExporter;

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
        self::assertGreaterThan(0, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'"), 'the demo fixtures place orders that reach the outbox');

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

    private function exporter(string $directory): OrderExporter
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $class = self::getContainer()->getParameter('tmonier_webhook.model.outbox_event.class');
        self::assertIsString($class);

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
