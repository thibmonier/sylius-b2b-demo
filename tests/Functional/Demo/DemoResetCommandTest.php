<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Order\DemoOrderPlan;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * US-033: guard, atomicity, alert and purge of `app:demo:reset`, run against the test database loaded with the
 * `demo` suite (the reset reloads that same suite, so every test leaves it as it found it).
 */
final class DemoResetCommandTest extends KernelTestCase
{
    use DatabaseAccess;

    private const MARKER = 'MARKER (before reset)';

    /** Products of fixtures/csv and customers of config/demo/accounts.yaml (11 B2B members + Léa). */
    private const INITIAL_PRODUCTS = 210;

    private const INITIAL_CUSTOMERS = 13;

    /** Carts that are not history orders (US-096 / US-097): the cart of Claire and the two drafts of the back office. */
    private const LIVE_CARTS = 3;

    private ?string $originalChannelName = null;

    protected function tearDown(): void
    {
        $this->restoreB2bChannelName();
        // A failed reset leaves a marker that turns /healthz to 503 (US-033): never leak it to the next tests.
        @unlink(self::resetFailedMarker());
        $this->setDemoMode('1');
        parent::tearDown();
    }

    public function testItIsRefusedWithoutDemoModeAndLeavesTheDatabaseIntact(): void
    {
        $this->setDemoMode('0');
        $before = $this->counts();
        $this->markB2bChannel();

        $tester = $this->reset();

        self::assertNotSame(0, $tester->getStatusCode());
        self::assertStringContainsString('DEMO_RESET_REFUSED', $tester->getErrorOutput());
        self::assertStringContainsString('DEMO_MODE=1', $tester->getErrorOutput());
        self::assertSame($before, $this->counts());
        self::assertSame(self::MARKER, $this->channelName(), 'A refused reset must not touch the data.');
    }

    public function testItRunsWithDemoModeAndRestoresTheInitialData(): void
    {
        $this->markB2bChannel();

        $tester = $this->reset();

        self::assertSame(0, $tester->getStatusCode(), $tester->getDisplay() . $tester->getErrorOutput());
        self::assertMatchesRegularExpression('/DEMO_RESET_OK duration=\d+(\.\d+)?s/', $tester->getDisplay());
        self::assertSame('', $tester->getErrorOutput());
        // The initial demo data, whatever other tests added before (smoke tests leave carts and PunchOut orders).
        self::assertSame(['products' => self::INITIAL_PRODUCTS, 'customers' => self::INITIAL_CUSTOMERS, 'orders' => \count(DemoOrderPlan::all()) + self::LIVE_CARTS], $this->counts());
        self::assertNotSame(self::MARKER, $this->channelName(), 'The reload must replace the previous data.');
    }

    public function testAFailureInTheMiddleKeepsThePreviousStateAndIsReportedOnStderr(): void
    {
        $before = $this->counts();
        $this->markB2bChannel();
        $this->db()->executeStatement('DELETE FROM sylius_product_review');

        $tester = $this->reset(['--simulate-failure' => true]);

        self::assertNotSame(0, $tester->getStatusCode());
        self::assertMatchesRegularExpression('/DEMO_RESET_FAILED cause=".*Simulated failure.*" duration=\d+(\.\d+)?s/', $tester->getErrorOutput());
        self::assertStringNotContainsString('DEMO_RESET_OK', $tester->getDisplay());
        self::assertSame($before, $this->counts(), 'Products, customers and orders of before the reset must still be there.');
        self::assertSame(self::MARKER, $this->channelName(), 'The reload ran then failed: it must have been rolled back entirely.');
        self::assertFileExists(self::resetFailedMarker(), 'The failure stays visible to /healthz until a reset succeeds.');
    }

    public function testTheResetRemovesTheSimulatorOrdersOfAcmeAndRestoresTheCredit(): void
    {
        $before = $this->counts();
        $acmeCompany = (string) $this->intValue('SELECT id FROM tmonier_company WHERE code = ?', ['ACME_IND']);
        $limit = $this->intValue('SELECT credit_limit FROM tmonier_trade_credit_account WHERE owner_identifier = ?', [$acmeCompany]);
        self::assertGreaterThan(0, $limit);
        $this->simulateAnonymousOrders(3);
        $this->db()->executeStatement('UPDATE tmonier_trade_credit_account SET committed_amount = ? WHERE owner_identifier = ?', [$limit - 1000, $acmeCompany]);
        self::assertSame($before['orders'] + 3, $this->counts()['orders']);

        $tester = $this->reset();

        self::assertSame(0, $tester->getStatusCode(), $tester->getDisplay() . $tester->getErrorOutput());
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM sylius_order WHERE number LIKE 'SIM-%'"));
        // The fixture count, not $before: orders leaked by other tests are removed by the reset too.
        self::assertSame(\count(DemoOrderPlan::all()) + self::LIVE_CARTS, $this->counts()['orders']);
        // Back to the open items of the demo (US-097), not to zero: the committed amount is what the reservations hold.
        $acme = (string) $this->intValue('SELECT id FROM tmonier_company WHERE code = ?', ['ACME_IND']);
        $committed = $this->intValue('SELECT committed_amount FROM tmonier_trade_credit_account WHERE owner_identifier = ?', [$acme]);
        self::assertSame(
            $this->intValue('SELECT COALESCE(SUM(t.amount), 0) FROM tmonier_trade_credit_order_terms t JOIN tmonier_trade_credit_account a ON a.id = t.account_id WHERE t.state = ? AND a.owner_identifier = ?', ['reserved', $acme]),
            $committed,
        );
        self::assertGreaterThan(0, $committed);
        self::assertLessThan($limit, $committed);
    }

    /** @param array<string, mixed> $options */
    private function reset(array $options = []): CommandTester
    {
        $application = new Application(self::bootKernel());
        $tester = new CommandTester($application->find('app:demo:reset'));
        $tester->execute(
            ['--no-media' => true, '--no-cache' => true, '--no-interaction' => true] + $options,
            ['capture_stderr_separately' => true],
        );

        return $tester;
    }

    private static function resetFailedMarker(): string
    {
        $marker = self::getContainer()->getParameter('demo.reset_marker_file');
        \assert(\is_string($marker));

        return $marker;
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $counts = [];
        foreach (['products' => 'sylius_product', 'customers' => 'sylius_customer', 'orders' => 'sylius_order'] as $key => $table) {
            $counts[$key] = $this->intValue(\sprintf('SELECT COUNT(*) FROM %s', $table));
        }

        return $counts;
    }

    private function markB2bChannel(): void
    {
        $this->originalChannelName ??= $this->channelName();
        $this->db()->executeStatement('UPDATE sylius_channel SET name = ? WHERE code = ?', [self::MARKER, 'B2B']);
    }

    // A refused or rolled back reset leaves the marker in the shared test database: later tests would read it.
    private function restoreB2bChannelName(): void
    {
        if (null === $this->originalChannelName) {
            return;
        }
        if (!self::$booted) {
            self::bootKernel();
        }
        $this->db()->executeStatement('UPDATE sylius_channel SET name = ? WHERE code = ?', [$this->originalChannelName, 'B2B']);
        $this->originalChannelName = null;
    }

    private function channelName(): string
    {
        return $this->stringValue('SELECT name FROM sylius_channel WHERE code = ?', ['B2B']);
    }

    /** Copies a fixture order without customer, as many times as asked, with the numbers the simulator would not use. */
    private function simulateAnonymousOrders(int $count): void
    {
        $table = $this->db()->createSchemaManager()->introspectTable('sylius_order');
        $unique = ['id', 'customer_id'];
        foreach ($table->getIndexes() as $index) {
            if ($index->isUnique()) {
                array_push($unique, ...$index->getColumns());
            }
        }
        $columns = array_diff(array_map(static fn ($column) => $column->getName(), $table->getColumns()), $unique, ['number', 'token_value']);
        $list = implode(', ', $columns);
        for ($i = 1; $i <= $count; ++$i) {
            $this->db()->executeStatement(
                \sprintf('INSERT INTO sylius_order (%1$s, number, token_value, customer_id) SELECT %1$s, ?, ?, NULL FROM sylius_order ORDER BY id LIMIT 1', $list),
                ['SIM-' . $i, 'sim-token-' . $i],
            );
        }
    }

    private function setDemoMode(string $value): void
    {
        $_SERVER['DEMO_MODE'] = $_ENV['DEMO_MODE'] = $value;
        putenv('DEMO_MODE=' . $value);
        self::ensureKernelShutdown();
    }
}
