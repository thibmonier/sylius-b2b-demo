<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-187 (B): the load of the `demo` fixtures (so the nightly reset) leaves the files of prices and stock of "ERP Démo (fictif)" imported:
 * a valid price file and a stock file applied, one price file invalid on purpose rejected whole (its valid line included), all three moved
 * to the `processed` subdirectory, the journal of the batches regenerated. The prices of the demo do not move.
 */
final class ErpDemoImportFixtureTest extends KernelTestCase
{
    use DatabaseAccess;

    public function testThreeBatchesAreJournalledTwoAppliedAndTheInvalidOneRejected(): void
    {
        self::bootKernel();

        $rows = $this->db()->fetchAllAssociative('SELECT name, kind, outcome, lines_read, lines_applied, lines_rejected, report FROM tmonier_erp_flatfile_import_batch ORDER BY name');

        self::assertCount(3, $rows);
        $byOutcome = array_count_values(array_map(static fn (array $row): string => \is_string($row['outcome']) ? $row['outcome'] : '', $rows));
        self::assertSame(['applied' => 2, 'rejected' => 1], $byOutcome);
        $rejected = array_values(array_filter($rows, static fn (array $row): bool => 'rejected' === $row['outcome']))[0];
        self::assertSame('prices', $rejected['kind']);
        self::assertStringContainsString('-invalid-', \is_string($rejected['name']) ? $rejected['name'] : '');
        self::assertSame('0', \is_scalar($rejected['lines_applied']) ? (string) $rejected['lines_applied'] : '', 'all or nothing: the valid line of the invalid file is not applied either');
        $report = \is_string($rejected['report']) ? $rejected['report'] : '';
        self::assertStringContainsString('"reason":"price"', $report);
        self::assertStringContainsString('"reason":"currency"', $report);
    }

    public function testTheFilesAreMovedToTheProcessedDirectoryAndNothingIsLeftToImport(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');
        self::assertIsString($directory);

        self::assertSame([], glob($directory . '/prices-*.csv') ?: []);
        self::assertSame([], glob($directory . '/stock-*.csv') ?: []);
        self::assertCount(3, glob($directory . '/processed/*.csv') ?: []);
        self::assertCount(1, glob($directory . '/orders-*.csv') ?: [], 'the orders of the US-188 are still there');
    }

    public function testThePricesOfTheDemoDidNotMoveAndTheInvalidAmountsWereNeverWritten(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM sylius_channel_pricing WHERE price <= 0'), 'a negative or null price was never written');
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM sylius_channel_pricing cp JOIN sylius_channel c ON c.code = cp.channel_code JOIN sylius_currency cur ON cur.id = c.base_currency_id WHERE cur.code = 'USD'"), 'no channel changed currency');
    }

    public function testTheStockFileAppliedTheQuantitiesItCarried(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');
        self::assertIsString($directory);
        $files = glob($directory . '/processed/stock-*.csv') ?: [];
        self::assertCount(1, $files);

        $lines = array_slice(explode("\n", rtrim((string) file_get_contents($files[0]), "\n")), 1);

        self::assertNotSame([], $lines);
        foreach ($lines as $line) {
            [$code, $onHand] = explode(';', $line);
            self::assertSame((int) $onHand, $this->intValue('SELECT on_hand FROM sylius_product_variant WHERE code = ?', [$code]));
        }
    }
}
