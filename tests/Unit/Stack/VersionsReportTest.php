<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\VersionsReport;
use PHPUnit\Framework\TestCase;

/**
 * US-239: unit tests for VersionsReport.
 * Tests the behavior with fixture data (not the shell commands).
 */
final class VersionsReportTest extends TestCase
{
    private function fixturesDir(): string
    {
        return \dirname(__DIR__, 2) . '/Fixtures/Stack';
    }

    public function testComposerPatchUpdateIsClassifiedCorrectly(): void
    {
        $composerJson = file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] vendor/patch-update : 7.1.0 → 7.1.3',
            $output,
            'Patch update (7.1.0 → 7.1.3) must be classified under "Mises à jour correctives (patch)"',
        );

        self::assertStringContainsString(
            '### Mises à jour correctives (patch)',
            $output,
        );
    }

    public function testComposerMinorUpdateIsClassifiedCorrectly(): void
    {
        $composerJson = file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] vendor/minor-update : 7.1.0 → 7.2.0',
            $output,
            'Minor update (7.1.0 → 7.2.0) must be classified under "Mises à jour mineures (minor)"',
        );

        self::assertStringContainsString(
            '### Mises à jour mineures (minor)',
            $output,
        );
    }

    public function testComposerMajorUpdateGeneratesStoryDraft(): void
    {
        $composerJson = file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] vendor/major-update : 7.5.0 → 8.0.0',
            $output,
            'Major update (7.5.0 → 8.0.0) must be classified under "Mises à jour majeures (major)"',
        );

        self::assertStringContainsString(
            '### Story : Montée vendor/major-update v8.0.0',
            $output,
            'Major update must generate a story draft',
        );

        self::assertStringContainsString(
            'Version actuelle : 7.5.0',
            $output,
        );

        self::assertStringContainsString(
            'Version cible : 8.0.0',
            $output,
        );

        self::assertStringContainsString(
            'Contrainte bloquante',
            $output,
        );
    }

    public function testNpmPatchUpdateIsClassifiedCorrectly(): void
    {
        $npmJson = file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] package-patch : 3.2.1 → 3.2.3',
            $output,
            'npm patch update (3.2.1 → 3.2.3) must be classified under patch',
        );
    }

    public function testNpmMinorUpdateIsClassifiedCorrectly(): void
    {
        $npmJson = file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] package-minor : 4.1.0 → 4.2.0',
            $output,
            'npm minor update (4.1.0 → 4.2.0) must be classified under minor',
        );
    }

    public function testNpmMajorUpdateIsClassifiedCorrectly(): void
    {
        $npmJson = file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- [ ] package-major : 5.0.0 → 6.0.0',
            $output,
            'npm major update (5.0.0 → 6.0.0) must be classified under major',
        );
    }

    public function testDependabotPRsAreListed(): void
    {
        $ghJson = file_get_contents($this->fixturesDir() . '/gh-pr-list-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', '{}', $ghJson);
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '- #123 : chore(deps): Bump vendor/package from 1.0 to 1.1',
            $output,
            'Dependabot PR #123 must be listed',
        );

        self::assertStringNotContainsString(
            '#124',
            $output,
            'PR #124 is not from Dependabot, it must not be listed',
        );
    }

    public function testUpToDatePackagesAreNotListed(): void
    {
        $composerJson = file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringNotContainsString(
            'vendor/up-to-date',
            $output,
            'Up-to-date packages must not be listed in outdated sections',
        );
    }

    public function testEmptyDataGeneratesValidReport(): void
    {
        $source = new FakeOutdatedSource();
        $report = new VersionsReport($source);

        $output = $report->generate();

        self::assertStringContainsString(
            '# Veille des versions — semaine',
            $output,
        );

        self::assertStringContainsString(
            'Aucune mise à jour corrective',
            $output,
        );

        self::assertStringContainsString(
            'Aucune PR Dependabot ouverte',
            $output,
        );
    }
}
