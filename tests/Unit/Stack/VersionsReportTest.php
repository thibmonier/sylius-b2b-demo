<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use PHPUnit\Framework\TestCase;

/**
 * US-239: weekly versions report generates a structured Markdown issue body.
 * The script compares installed versions (composer.lock, package.json) against
 * the latest published versions and reports outdated dependencies.
 */
final class VersionsReportTest extends TestCase
{
    private const REQUIRED_SECTIONS = [
        '## 📦 Dépendances Composer',
        '## 📦 Dépendances npm',
        '## 🐳 Images Docker',
        '## 🔄 PR Dependabot ouvertes',
    ];

    public function testReportContainsAllRequiredSections(): void
    {
        $report = $this->generateReport();

        foreach (self::REQUIRED_SECTIONS as $section) {
            self::assertStringContainsString(
                $section,
                $report,
                sprintf('The report must contain the section "%s"', $section),
            );
        }
    }

    public function testReportStartsWithWeekNumber(): void
    {
        $report = $this->generateReport();

        self::assertMatchesRegularExpression(
            '/^# Veille des versions — semaine \d{4}-\d{2}/',
            $report,
            'The report must start with the title containing the week number (format YYYY-WW)',
        );
    }

    public function testReportContainsGenerationDate(): void
    {
        $report = $this->generateReport();

        self::assertStringContainsString(
            'Date de génération :',
            $report,
            'The report must contain the generation date',
        );

        self::assertMatchesRegularExpression(
            '/Date de génération : \d{4}-\d{2}-\d{2}/',
            $report,
            'The generation date must be in ISO format (YYYY-MM-DD)',
        );
    }

    public function testComposerSectionGroupsByUpdateType(): void
    {
        $report = $this->generateReport();

        self::assertStringContainsString(
            '### Mises à jour correctives (patch)',
            $report,
            'Composer section must have a subsection for patch updates',
        );

        self::assertStringContainsString(
            '### Mises à jour mineures (minor)',
            $report,
            'Composer section must have a subsection for minor updates',
        );

        self::assertStringContainsString(
            '### Mises à jour majeures (major)',
            $report,
            'Composer section must have a subsection for major updates',
        );
    }

    public function testNpmSectionGroupsByUpdateType(): void
    {
        $report = $this->generateReport();

        self::assertStringContainsString(
            '### Mises à jour correctives (patch)',
            $report,
            'npm section must have a subsection for patch updates',
        );

        self::assertStringContainsString(
            '### Mises à jour mineures (minor)',
            $report,
            'npm section must have a subsection for minor updates',
        );

        self::assertStringContainsString(
            '### Mises à jour majeures (major)',
            $report,
            'npm section must have a subsection for major updates',
        );
    }

    public function testDockerSectionListsCurrentAndLatestVersions(): void
    {
        $report = $this->generateReport();

        self::assertStringContainsString(
            '### Images à jour',
            $report,
            'Docker section must have a subsection for up-to-date images',
        );

        self::assertStringContainsString(
            '### Images en retard',
            $report,
            'Docker section must have a subsection for outdated images',
        );

        self::assertStringContainsString(
            '### Images volontairement ignorées',
            $report,
            'Docker section must have a subsection for intentionally ignored images',
        );
    }

    public function testDependabotPRsAreListed(): void
    {
        $report = $this->generateReport();

        // The section must exist even if there are no open PRs
        self::assertStringContainsString(
            '## 🔄 PR Dependabot ouvertes',
            $report,
            'The report must contain the Dependabot PRs section',
        );
    }

    public function testMajorUpgradeDraftsAreIncluded(): void
    {
        $report = $this->generateReport();

        // The section must exist (it may be empty if no major upgrades are blocked)
        self::assertStringContainsString(
            '## 📝 Brouillons de stories pour montées majeures',
            $report,
            'The report must contain the section for major upgrade story drafts',
        );
    }

    private function generateReport(): string
    {
        // The script does not exist yet: this will fail until T-239-03 is implemented
        $scriptPath = \dirname(__DIR__, 3) . '/bin/stack-versions-report';

        if (!file_exists($scriptPath) || !is_executable($scriptPath)) {
            self::fail(sprintf('The script "%s" does not exist or is not executable', $scriptPath));
        }

        $output = shell_exec($scriptPath . ' 2>&1');

        if (null === $output || false === $output || '' === $output) {
            self::fail('The script did not produce any output');
        }

        return $output;
    }
}
