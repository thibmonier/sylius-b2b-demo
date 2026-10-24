<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Erp\SimulatorJournal;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-188: the load of the `demo` fixtures (and so the nightly reset, which reloads them) leaves a known state in the exchange
 * directory: one file of orders, its acknowledgement and one line in the journal of the simulator; the files of the previous load are gone.
 */
final class ErpDemoExchangeFixtureTest extends KernelTestCase
{
    public function testTheLoadLeavesOneFileOfOrdersItsAcknowledgementAndOneLineOfJournal(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');
        self::assertIsString($directory);

        $orders = glob($directory . '/orders-*.csv') ?: [];
        self::assertCount(1, $orders, 'the files of the previous load are gone');
        self::assertCount(1, glob($directory . '/ack-*.csv') ?: []);
        self::assertSame('ack-' . basename($orders[0]), basename((glob($directory . '/ack-*.csv') ?: [''])[0]));
        $batches = (new SimulatorJournal($directory))->all();
        self::assertCount(1, $batches);
        self::assertSame(basename($orders[0]), $batches[0]->source);
        self::assertSame(0, $batches[0]->rejected);
    }

    public function testNothingIsWrittenOutsideTheExchangeDirectoryOfTheTest(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');
        self::assertIsString($directory);

        self::assertStringNotContainsString('/var/erp-exchange', $directory);
        self::assertSame([], glob(\dirname(__DIR__, 3) . '/var/erp-exchange/ack-*') ?: [], 'the demo directory is not the one of the tests');
    }
}
