<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * US-048 / T-048-05: the demo order history does not look its references up one by one. Runs the reset (the same
 * suite the nightly job reloads, leaving the database as it found it) with the fixture profiler on, and reads the
 * statements of `demo_orders`. The budget is the measured count after the work (822 SELECT before): a guard against
 * the per-order lookups coming back. The rest is Sylius' order processors (zones, promotions) run at every checkout
 * transition, see docs/remise-a-zero-demo.md.
 */
final class OrderHistoryFixtureQueryCountTest extends KernelTestCase
{
    use DatabaseAccess;

    // 770 + 10 (US-090): the tier-pricing rules of the Acme customer group are read once per product (cached rule
    // provider), about four more selects than before the company price existed; 774 measured.
    // 780 + 210 (US-097): three more orders, paid on account (open items of the credit gauge), about 65 selects each
    // like the others; 980 measured for 15 orders.
    private const MAX_SELECT = 1050;

    private string $profile = '';

    protected function setUp(): void
    {
        $this->profile = tempnam(sys_get_temp_dir(), 'reset-profile-') ?: '';
        $_SERVER['PROFILE_RESET_OUT'] = $_ENV['PROFILE_RESET_OUT'] = $this->profile;
        putenv('PROFILE_RESET_OUT=' . $this->profile);
        self::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['PROFILE_RESET_OUT'], $_ENV['PROFILE_RESET_OUT']);
        putenv('PROFILE_RESET_OUT');
        @unlink($this->profile);
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function testTheOrderHistoryIsLoadedWithFewSelectsAndTheSameOrders(): void
    {
        $application = new Application(self::bootKernel());
        $before = $this->orders();
        self::assertCount(\count(DemoOrderPlan::all()), $before);

        $tester = new CommandTester($application->find('app:demo:reset'));
        $tester->execute(['--no-media' => true, '--no-cache' => true, '--no-interaction' => true], ['capture_stderr_separately' => true]);
        self::assertSame(0, $tester->getStatusCode(), $tester->getDisplay() . $tester->getErrorOutput());

        $selects = $this->selectsOf('demo_orders');
        self::assertLessThanOrEqual(self::MAX_SELECT, $selects, 'SELECT statements of demo_orders');
        self::assertSame($before, $this->orders(), 'The reset rebuilds the same orders, with the same states and totals.');
    }

    /**
     * The history orders only: other tests leave carts and PunchOut orders in the database, the reset removes them.
     *
     * @return list<array<string, mixed>>
     */
    private function orders(): array
    {
        return $this->db()->fetchAllAssociative(
            'SELECT po_reference, cost_center, state, payment_state, shipping_state, checkout_state, items_total, adjustments_total, total, checkout_completed_at
             FROM sylius_order WHERE po_reference IN (?) ORDER BY po_reference',
            [array_map(static fn (DemoOrderSpec $spec): string => $spec->poReference, DemoOrderPlan::all())],
            [ArrayParameterType::STRING],
        );
    }

    private function selectsOf(string $fixture): int
    {
        foreach (file($this->profile, \FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $data = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
            if (\is_array($data) && ($data['fixture'] ?? null) === $fixture) {
                self::assertIsInt($data['select'] ?? null);

                return $data['select'];
            }
        }

        self::fail(sprintf('No profile line for "%s" in %s.', $fixture, $this->profile));
    }
}
