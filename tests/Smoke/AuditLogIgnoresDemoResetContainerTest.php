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
}
