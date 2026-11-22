<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\IgnoredUpdates;
use PHPUnit\Framework\TestCase;

/**
 * Exclusions volontaires de la veille hebdomadaire des versions (demande de Thibaut du 2026-11-22).
 *
 * Une exclusion sans date est une décision définitive ; une exclusion datée cesse de
 * s'appliquer une fois l'échéance passée, pour qu'aucun paquet ne disparaisse de la veille
 * en silence et pour toujours.
 */
final class IgnoredUpdatesTest extends TestCase
{
    private const string SAMPLE = __DIR__ . '/../../Fixtures/Stack/version-watch-ignore-sample.json';

    public function testAnEntryWithoutDateIsAlwaysIgnored(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2099-01-01'));

        self::assertTrue($ignored->ignores('composer', 'vendor/major-update'));
    }

    public function testAnEntryIsStillIgnoredOnItsReviewDate(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2027-06-30'));

        self::assertTrue($ignored->ignores('npm', 'package-major'));
    }

    public function testAnEntryStopsBeingIgnoredAfterItsReviewDate(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2027-07-01'));

        self::assertFalse(
            $ignored->ignores('npm', 'package-major'),
            'Une exclusion datée doit cesser de s\'appliquer après son échéance : sinon un paquet sort de la veille pour toujours',
        );
    }

    public function testAnExpiredEntryIsNotListedEither(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2026-11-22'));

        self::assertStringNotContainsString('package-expire', implode("\n", $ignored->lines('npm')));
    }

    public function testLinesCarryTheReasonTheStoryAndTheReviewDate(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2026-11-22'));

        $line = implode("\n", $ignored->lines('npm'));

        self::assertStringContainsString('package-major', $line);
        self::assertStringContainsString('Attend la migration de Sylius', $line);
        self::assertStringContainsString('US-267', $line);
        self::assertStringContainsString('2027-06-30', $line);
    }

    public function testAPackageNotDeclaredIsNotIgnored(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2026-11-22'));

        self::assertFalse($ignored->ignores('composer', 'vendor/patch-update'));
    }

    public function testAnUnknownEcosystemHasNoExclusion(): void
    {
        $ignored = IgnoredUpdates::fromFile(self::SAMPLE, new \DateTimeImmutable('2026-11-22'));

        self::assertFalse($ignored->ignores('cargo', 'serde'));
        self::assertSame([], $ignored->lines('cargo'));
    }

    public function testNoExclusionByDefault(): void
    {
        $ignored = new IgnoredUpdates();

        self::assertFalse($ignored->ignores('npm', 'package-major'));
        self::assertSame([], $ignored->lines('npm'));
    }

    public function testAnUnreadableFileIsAnError(): void
    {
        $this->expectException(\RuntimeException::class);

        IgnoredUpdates::fromFile(__DIR__ . '/does-not-exist.json', new \DateTimeImmutable());
    }

    public function testTheRealConfigurationFileParses(): void
    {
        $ignored = IgnoredUpdates::fromFile(
            \dirname(__DIR__, 3) . '/config/demo/version-watch-ignore.json',
            new \DateTimeImmutable('2026-11-22'),
        );

        self::assertTrue(
            $ignored->ignores('npm', 'apexcharts'),
            'apexcharts est exclue par décision du 2026-11-10 (licence non MIT depuis la 5)',
        );
    }
}
