<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-215: the demo uses only the replay adapter — no LLM API key must be configured.
 * US-213: the cassettes shipped with the plugin must be loadable.
 */
final class CopilotReplayConfigTest extends KernelTestCase
{
    public function testNoLlmApiKeyIsConfigured(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        foreach (['COPILOT_API_KEY', 'OPENAI_API_KEY', 'ANTHROPIC_API_KEY', 'LLM_API_KEY'] as $var) {
            self::assertFalse(
                $container->hasParameter('env(' . $var . ')'),
                \sprintf('The demo must not configure %s — only the replay adapter is used.', $var),
            );
        }
    }

    public function testTheCopilotPluginIsEnabled(): void
    {
        self::bootKernel();
        $bundles = self::getContainer()->getParameter('kernel.bundles');
        self::assertIsArray($bundles);

        self::assertArrayHasKey(
            'TmonierSyliusB2bSalesCopilotPlugin',
            $bundles,
            'The copilot plugin must be registered.',
        );
    }

    public function testTheCassettesFileExistsAndContainsScenarios(): void
    {
        self::bootKernel();
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        self::assertIsString($projectDir);

        $cassettePath = $projectDir . '/vendor/tmonier/sylius-b2b-sales-copilot-plugin/config/cassettes.jsonl';

        self::assertFileExists($cassettePath, 'The cassettes file must be shipped with the plugin.');

        $lines = file($cassettePath, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
        self::assertNotFalse($lines);
        self::assertGreaterThanOrEqual(6, \count($lines), 'At least 6 cassettes (3 FR + 3 EN) must exist.');

        $first = json_decode($lines[0], true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($first);
        self::assertArrayHasKey('intent', $first);
        self::assertArrayHasKey('response', $first);
        self::assertArrayHasKey('tools', $first);
    }
}
