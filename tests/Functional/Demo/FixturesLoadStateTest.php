<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * US-048 / T-048-10: the entrypoint loads the fixtures when `app:demo:fixtures-loaded` says they are not (exit code 3),
 * not when the channel table is empty: a first load killed half-way leaves channels but no end-of-load marker.
 * Run against a database loaded with the `demo` suite; every test rolls its changes back.
 */
final class FixturesLoadStateTest extends KernelTestCase
{
    use DatabaseAccess;

    protected function tearDown(): void
    {
        if (self::$booted && $this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    public function testACompleteLoadLeavesOneMarkerAndIsReportedAsLoaded(): void
    {
        self::bootKernel();

        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_demo_load_marker'));
        self::assertSame(0, $this->execute()->getStatusCode());
    }

    public function testAPartialLoadHasDataButNoMarkerAndMustBeLoadedAgain(): void
    {
        self::bootKernel();
        $this->db()->beginTransaction();
        $this->db()->executeStatement('DELETE FROM app_demo_load_marker');
        self::assertGreaterThan(0, $this->intValue('SELECT COUNT(*) FROM sylius_channel'), 'The partial state keeps the channels the entrypoint used to look at.');

        self::assertSame(3, $this->execute()->getStatusCode());
    }

    public function testTheCommandSaysNothingButItsExitCode(): void
    {
        self::bootKernel();

        self::assertSame('', trim($this->execute()->getDisplay()));
    }

    private function execute(): CommandTester
    {
        $tester = new CommandTester((new Application(self::$kernel ?? self::bootKernel()))->find('app:demo:fixtures-loaded'));
        $tester->execute([], ['capture_stderr_separately' => true]);

        return $tester;
    }
}
