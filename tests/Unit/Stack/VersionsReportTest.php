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
        $composerJson = (string) file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
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
        $composerJson = (string) file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        $patchSection = $this->extractSection($output, 'Mises à jour correctives (patch)');
        $minorSection = $this->extractSection($output, 'Mises à jour mineures (minor)');
        $majorSection = $this->extractSection($output, 'Mises à jour majeures (major)');

        $minorLine = '- [ ] vendor/minor-update : 7.1.0 → 7.2.0';

        self::assertStringContainsString(
            $minorLine,
            $minorSection,
            'Minor update must be in the minor section',
        );

        self::assertStringNotContainsString(
            $minorLine,
            $patchSection,
            'Minor update must NOT be in the patch section',
        );

        self::assertStringNotContainsString(
            $minorLine,
            $majorSection,
            'Minor update must NOT be in the major section',
        );
    }

    public function testComposerMajorUpdateGeneratesStoryDraft(): void
    {
        $composerJson = (string) file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson($composerJson, '{}', '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        $patchSection = $this->extractSection($output, 'Mises à jour correctives (patch)');
        $minorSection = $this->extractSection($output, 'Mises à jour mineures (minor)');
        $majorSection = $this->extractSection($output, 'Mises à jour majeures (major)');

        $majorLine = '- [ ] vendor/major-update : 7.5.0 → 8.0.0';

        self::assertStringContainsString(
            $majorLine,
            $majorSection,
            'Major update must be in the major section',
        );

        self::assertStringNotContainsString(
            $majorLine,
            $patchSection,
            'Major update must NOT be in the patch section',
        );

        self::assertStringNotContainsString(
            $majorLine,
            $minorSection,
            'Major update must NOT be in the minor section',
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
        $npmJson = (string) file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        $npmSection = $this->extractSection($output, '📦 Dépendances npm');
        $patchSection = $this->extractSection($npmSection, 'Mises à jour correctives (patch)');
        $minorSection = $this->extractSection($npmSection, 'Mises à jour mineures (minor)');
        $majorSection = $this->extractSection($npmSection, 'Mises à jour majeures (major)');

        $patchLine = '- [ ] package-patch : 3.2.1 → 3.2.3';

        self::assertStringContainsString(
            $patchLine,
            $patchSection,
            'npm patch must be in patch section',
        );

        self::assertStringNotContainsString(
            $patchLine,
            $minorSection,
            'npm patch must NOT be in minor section',
        );

        self::assertStringNotContainsString(
            $patchLine,
            $majorSection,
            'npm patch must NOT be in major section',
        );
    }

    public function testNpmMinorUpdateIsClassifiedCorrectly(): void
    {
        $npmJson = (string) file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        $npmSection = $this->extractSection($output, '📦 Dépendances npm');
        $patchSection = $this->extractSection($npmSection, 'Mises à jour correctives (patch)');
        $minorSection = $this->extractSection($npmSection, 'Mises à jour mineures (minor)');
        $majorSection = $this->extractSection($npmSection, 'Mises à jour majeures (major)');

        $minorLine = '- [ ] package-minor : 4.1.0 → 4.2.0';

        self::assertStringContainsString(
            $minorLine,
            $minorSection,
            'npm minor must be in minor section',
        );

        self::assertStringNotContainsString(
            $minorLine,
            $patchSection,
            'npm minor must NOT be in patch section',
        );

        self::assertStringNotContainsString(
            $minorLine,
            $majorSection,
            'npm minor must NOT be in major section',
        );
    }

    public function testNpmMajorUpdateIsClassifiedCorrectly(): void
    {
        $npmJson = (string) file_get_contents($this->fixturesDir() . '/npm-outdated-sample.json');
        $source = FakeOutdatedSource::fromJson('{"installed":[]}', $npmJson, '[]');
        $report = new VersionsReport($source);

        $output = $report->generate();

        $npmSection = $this->extractSection($output, '📦 Dépendances npm');
        $patchSection = $this->extractSection($npmSection, 'Mises à jour correctives (patch)');
        $minorSection = $this->extractSection($npmSection, 'Mises à jour mineures (minor)');
        $majorSection = $this->extractSection($npmSection, 'Mises à jour majeures (major)');

        $majorLine = '- [ ] package-major : 5.0.0 → 6.0.0';

        self::assertStringContainsString(
            $majorLine,
            $majorSection,
            'npm major must be in major section',
        );

        self::assertStringNotContainsString(
            $majorLine,
            $patchSection,
            'npm major must NOT be in patch section',
        );

        self::assertStringNotContainsString(
            $majorLine,
            $minorSection,
            'npm major must NOT be in minor section',
        );
    }

    public function testDependabotPRsAreListed(): void
    {
        $ghJson = (string) file_get_contents($this->fixturesDir() . '/gh-pr-list-sample.json');
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
        $composerJson = (string) file_get_contents($this->fixturesDir() . '/composer-outdated-sample.json');
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

    /**
     * Extracts text between a section title and the next section.
     *
     * @param string $output Full report
     * @param string $title  Section title (without ## or ###)
     *
     * @return string Section content
     */
    private function extractSection(string $output, string $title): string
    {
        // Find the section and capture its level (## or ###)
        $findPattern = '/(##+) ' . preg_quote($title, '/') . '/';
        if (!preg_match($findPattern, $output, $findMatches, \PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $level = \strlen($findMatches[1][0]);
        $startPos = $findMatches[0][1] + \strlen($findMatches[0][0]);

        // Extract until next heading of same or higher level (fewer #)
        $endPattern = '/\n#{1,' . $level . '} /';
        if (preg_match($endPattern, $output, $endMatches, \PREG_OFFSET_CAPTURE, $startPos)) {
            $endPos = $endMatches[0][1];

            return substr($output, $startPos, $endPos - $startPos);
        }

        return substr($output, $startPos);
    }
}
