<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * A test that changes a shared demo account (the cart of Claire, of Paul…) runs inside a transaction that is rolled
 * back at the end, so the next test finds the demo data as the fixtures left it, whatever the order of the suite.
 * The client keeps one kernel, hence one connection, for all its requests (see docs/tests-locaux.md).
 */
trait IsolatesSharedAccounts
{
    private ?Connection $isolatedConnection = null;

    protected function tearDown(): void
    {
        if ($this->isolatedConnection?->isTransactionActive()) {
            $this->isolatedConnection->rollBack();
        }
        $this->isolatedConnection = null;
        parent::tearDown();
    }

    private function isolateSharedAccounts(KernelBrowser $client): void
    {
        $client->disableReboot();
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);
        $connection->beginTransaction();
        $this->isolatedConnection = $connection;
    }
}
