<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\PhpUpgradeWatchSection;
use PHPUnit\Framework\TestCase;

/**
 * ADR-0021: the weekly watch must stay closed while either gate is shut, and say so explicitly.
 * A false green here would be worse than no section at all, since it invites an upgrade that cannot build.
 */
final class PhpUpgradeWatchSectionTest extends TestCase
{
    public function testBothGatesShutReportsBlocked(): void
    {
        $output = (new PhpUpgradeWatchSection())->render(
            new FakePhpUpgradeWatchSource(0, ['league/oauth2-server' => '~8.5.0']),
        );

        self::assertStringContainsString('aucun tag `php8.6` publié', $output);
        self::assertStringContainsString('1 paquet(s) excluent PHP 8.6', $output);
        self::assertStringContainsString('league/oauth2-server', $output);
        self::assertStringContainsString('toujours bloquée', $output);
    }

    public function testImagePublishedButDependencyStillBlocking(): void
    {
        $output = (new PhpUpgradeWatchSection())->render(
            new FakePhpUpgradeWatchSource(12, ['league/oauth2-server' => '~8.5.0']),
        );

        self::assertStringContainsString('- [x] **Image FrankenPHP** : 12 tag(s)', $output);
        self::assertStringContainsString('toujours bloquée', $output);
        self::assertStringNotContainsString('verrous sont levés', $output);
    }

    public function testDependenciesClearedButNoImageStaysBlocked(): void
    {
        $output = (new PhpUpgradeWatchSection())->render(new FakePhpUpgradeWatchSource(0));

        self::assertStringContainsString("aucun paquet verrouillé n'exclut PHP 8.6", $output);
        self::assertStringContainsString('toujours bloquée', $output);
    }

    public function testBothGatesOpenCallsForTheDecisionToBeReopened(): void
    {
        $output = (new PhpUpgradeWatchSection())->render(new FakePhpUpgradeWatchSource(60));

        self::assertStringContainsString('Les deux verrous sont levés', $output);
        self::assertStringContainsString("Rouvrir l'ADR-0021", $output);
    }
}
