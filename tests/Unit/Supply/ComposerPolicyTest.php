<?php

declare(strict_types=1);

namespace App\Tests\Unit\Supply;

use App\Tests\Support\Supply\ComposerRules;
use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;

/**
 * US-208: Composer policy of the demo, frozen by tests so that any change goes through a review of the pull request.
 */
final class ComposerPolicyTest extends TestCase
{
    /** Reviewed on 2026-10-27. Adding a plugin, or turning one on, fails here until this list changes in the same PR. */
    private const ALLOWED_PLUGINS = [
        'dealerdirect/phpcodesniffer-composer-installer' => false,
        'php-http/discovery' => false,
        'phpstan/extension-installer' => false,
        'rector/extension-installer' => true,
        'symfony/flex' => true,
        'symfony/runtime' => true,
        'symfony/thanks' => false,
    ];

    public function testTheListOfComposerPluginsIsFrozen(): void
    {
        $allowed = Typed::map(Typed::at($this->composer(), 'config', 'allow-plugins'), 'allow-plugins');
        ksort($allowed);

        self::assertSame(self::ALLOWED_PLUGINS, $allowed);
    }

    public function testEveryLockedComposerPluginHasAnExplicitDecision(): void
    {
        $decided = Typed::map(Typed::at($this->composer(), 'config', 'allow-plugins'), 'allow-plugins');
        $plugins = [];
        foreach (['packages', 'packages-dev'] as $section) {
            foreach (Typed::listOfMaps(Typed::at($this->lock(), $section), $section) as $package) {
                if ('composer-plugin' === ($package['type'] ?? null)) {
                    $plugins[] = Typed::string($package['name'] ?? null, 'package name');
                }
            }
        }

        self::assertNotEmpty($plugins);
        self::assertSame([], array_diff($plugins, array_keys($decided)));
    }

    public function testThePolicyBlocksMaliciousPackagesAndFailsTheAuditWithoutCooldown(): void
    {
        $policy = Typed::map(Typed::at($this->composer(), 'config', 'policy'), 'config.policy');

        self::assertTrue(Typed::at($policy, 'malware', 'block'));
        self::assertSame('fail', Typed::at($policy, 'malware', 'audit'));
        self::assertTrue(Typed::at($policy, 'advisories', 'block'));
        self::assertSame('fail', Typed::at($policy, 'advisories', 'audit'));
        // policy.cooldown has no effect in Composer 2.10.3 (the PR must say so again before using it).
        self::assertArrayNotHasKey('cooldown', $policy);
    }

    public function testNoCommandTurnsTheBlockingOfTheAuditOff(): void
    {
        $root = \dirname(__DIR__, 3);
        $files = [...glob($root . '/.github/workflows/*.y*ml') ?: [], ...glob($root . '/bin/*') ?: [], $root . '/composer.json', $root . '/docker/Dockerfile'];
        $found = [];
        foreach ($files as $file) {
            foreach (file($file, \FILE_IGNORE_NEW_LINES) ?: [] as $number => $line) {
                if (str_contains($line, '--no-blocking')) {
                    $found[] = sprintf('%s:%d no-blocking: "--no-blocking" is forbidden', substr($file, \strlen($root) + 1), $number + 1);
                }
            }
        }

        self::assertSame([], $found);
    }

    public function testComposerIsOnTheSameVersionInTheImageAndInTheCi(): void
    {
        preg_match('/composer:(\d+\.\d+\.\d+)@sha256:/', $this->read('docker/Dockerfile'), $image);
        preg_match_all('/tools:\s*composer:(\S+)/', $this->read('.github/workflows/ci.yaml'), $ci);

        self::assertNotEmpty($image);
        self::assertNotEmpty($ci[1]);
        self::assertSame(array_fill(0, \count($ci[1]), $image[1] ?? ''), $ci[1]);
    }

    public function testNoDevelopmentBranchReachesProductionUnlessFrozenWithItsReason(): void
    {
        self::assertSame([], ComposerRules::branchViolations($this->composer(), $this->lock()));
    }

    public function testAFrozenCommitWithItsReasonIsAccepted(): void
    {
        $sha = str_repeat('a', 40);
        $composer = [
            'require' => ['acme/branch' => '1.4.x-dev#' . $sha],
            'extra' => ['supply-chain' => ['pinned-branches' => ['acme/branch' => 'The fix is not released yet.']]],
        ];
        $lock = ['packages' => [['name' => 'acme/branch', 'version' => '1.4.x-dev']], 'packages-dev' => []];

        self::assertSame([], ComposerRules::branchViolations($composer, $lock));
    }

    public function testAFrozenCommitWithoutReasonAndAFloatingBranchAreRefused(): void
    {
        $composer = ['require' => ['acme/frozen' => 'dev-main#' . str_repeat('a', 40), 'acme/floating' => '1.4.x-dev', 'acme/alias' => 'dev-fork as 1.3.0']];
        $violations = ComposerRules::branchViolations($composer, ['packages' => [], 'packages-dev' => []]);

        self::assertCount(3, $violations);
        self::assertStringContainsString('acme/frozen', $violations[0]);
        self::assertStringContainsString('acme/floating', $violations[1]);
        self::assertStringContainsString('acme/alias', $violations[2]);
    }

    /** @return array<array-key, mixed> */
    private function composer(): array
    {
        return $this->decode('composer.json');
    }

    /** @return array<array-key, mixed> */
    private function lock(): array
    {
        return $this->decode('composer.lock');
    }

    /** @return array<array-key, mixed> */
    private function decode(string $path): array
    {
        $data = json_decode($this->read($path), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    private function read(string $path): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 3) . '/' . $path);
    }
}
