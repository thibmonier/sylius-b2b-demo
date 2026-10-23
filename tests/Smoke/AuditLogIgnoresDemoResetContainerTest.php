<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** US-087: the parameter loaded by the audit log bundle ignores the reset command and keeps the plugin defaults. */
final class AuditLogIgnoresDemoResetContainerTest extends KernelTestCase
{
    public function testTheBundleParameterIgnoresTheResetAndTheFixturesLoads(): void
    {
        self::bootKernel();
        $ignored = self::getContainer()->getParameter('tmonier_audit_log.ignored_commands');

        self::assertIsArray($ignored);
        foreach (['app:demo:reset', 'sylius:fixtures:load', 'doctrine:fixtures:load'] as $command) {
            self::assertContains($command, $ignored);
        }
    }

    /** US-088: only the commands are ignored, so what a visitor changes in the restricted admin is still journaled. */
    public function testAVisitorWriteInTheAdminStaysAudited(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->getParameter('tmonier_audit_log.enabled'));
        self::assertSame(['admin', 'admin_api', 'cli'], $container->getParameter('tmonier_audit_log.sources'));
        self::assertSame(['sylius:fixtures:load', 'doctrine:fixtures:load', 'app:demo:reset'], $container->getParameter('tmonier_audit_log.ignored_commands'));
    }
}
