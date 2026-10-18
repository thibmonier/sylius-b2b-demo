<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-087: the reset reloads the fixtures in-process; its entries must not flood the audit log. */
final class AuditLogIgnoresDemoResetTest extends TestCase
{
    public function testTheResetCommandIsIgnoredAndTheDefaultsAreKept(): void
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 3) . '/config/packages/tmonier_plugins_overrides.yaml');

        self::assertIsArray($config);
        self::assertIsArray($config['tmonier_sylius_audit_log']['ignored_commands'] ?? null);
        foreach (['sylius:fixtures:load', 'doctrine:fixtures:load', 'app:demo:reset'] as $command) {
            self::assertContains($command, $config['tmonier_sylius_audit_log']['ignored_commands']);
        }
    }

    public function testTheIgnoredCommandIsTheRealNameOfTheResetCommand(): void
    {
        self::assertStringContainsString("name: 'app:demo:reset'", (string) file_get_contents(\dirname(__DIR__, 3) . '/src/Demo/Command/DemoResetCommand.php'));
    }
}
