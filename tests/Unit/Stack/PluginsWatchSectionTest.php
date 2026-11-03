<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\DependabotPullRequest;
use App\Demo\Stack\PluginRepositoryStatus;
use App\Demo\Stack\PluginsWatchSection;
use PHPUnit\Framework\TestCase;

/**
 * Weekly version watch extended to the plugins (2026-11-03): one section lists, for every plugin repository,
 * the red default branches and the open Dependabot pull requests sorted by what Thibaut has to do with them.
 */
final class PluginsWatchSectionTest extends TestCase
{
    public function testARedDefaultBranchIsListedFirst(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-quick-order-plugin', false, []));

        self::assertStringContainsString("### `main` rouge\n- [ ] sylius-quick-order-plugin : https://github.com/thibmonier/sylius-quick-order-plugin/actions?query=branch%3Amain", $output);
    }

    public function testAGreenMinorPullRequestIsReadyToMerge(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-gpsr-plugin', true, [
            $this->pr(12, 'chore(deps-dev): Bump phpstan/phpstan from 2.3.3 to 2.3.4', DependabotPullRequest::CHECKS_SUCCESS),
        ]));

        self::assertStringContainsString("### Prêtes à merger (minor/patch, CI vert)\n- [ ] sylius-gpsr-plugin#12 : chore(deps-dev): Bump phpstan/phpstan from 2.3.3 to 2.3.4", $output);
    }

    public function testAMajorPullRequestNeedsADecisionEvenWhenGreen(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-gpsr-plugin', true, [
            $this->pr(6, 'chore(deps-dev): Update phpunit/phpunit requirement from ^10.5 to ^13.4', DependabotPullRequest::CHECKS_SUCCESS),
        ]));

        self::assertStringContainsString("### Majeures ou groupes : décision ou story\n- [ ] sylius-gpsr-plugin#6 : chore(deps-dev): Update phpunit/phpunit requirement from ^10.5 to ^13.4 (CI vert)", $output);
        self::assertStringContainsString("### Prêtes à merger (minor/patch, CI vert)\nAucune\n", $output);
    }

    public function testAPullRequestWithoutVersionsIsReviewedLikeAMajor(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-facturx-plugin', true, [
            $this->pr(8, 'chore(deps): Bump the github-actions group across 1 directory with 3 updates', DependabotPullRequest::CHECKS_FAILURE),
        ]));

        self::assertStringContainsString('- [ ] sylius-facturx-plugin#8 : chore(deps): Bump the github-actions group across 1 directory with 3 updates (CI rouge)', $this->section($output, 'Majeures ou groupes : décision ou story'));
    }

    public function testARedOrPendingMinorPullRequestIsKeptApart(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-timeline-plugin', true, [
            $this->pr(3, 'Bump twig/twig from 3.23.0 to 3.24.0', DependabotPullRequest::CHECKS_FAILURE),
            $this->pr(4, 'Bump doctrine/orm from 3.6.1 to 3.6.2', DependabotPullRequest::CHECKS_PENDING),
        ]));

        $section = $this->section($output, 'Minor/patch en échec ou en cours');
        self::assertStringContainsString('- [ ] sylius-timeline-plugin#3 : Bump twig/twig from 3.23.0 to 3.24.0 (CI rouge)', $section);
        self::assertStringContainsString('- [ ] sylius-timeline-plugin#4 : Bump doctrine/orm from 3.6.1 to 3.6.2 (CI en cours)', $section);
    }

    public function testBefore1Dot0AMinorBumpCountsAsMajor(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-b2b-sales-copilot-plugin', true, [
            $this->pr(7, 'build(deps-dev): Update symfony/ai-platform requirement from ^0.14 to ^0.15', DependabotPullRequest::CHECKS_SUCCESS),
        ]));

        self::assertStringContainsString('sylius-b2b-sales-copilot-plugin#7', $this->section($output, 'Majeures ou groupes : décision ou story'));
    }

    public function testTheSummaryCountsRepositoriesPullRequestsAndRedBranches(): void
    {
        $output = $this->render(
            new PluginRepositoryStatus('sylius-a-plugin', false, [$this->pr(1, 'Bump a/a from 1.0.0 to 1.0.1', DependabotPullRequest::CHECKS_SUCCESS)]),
            new PluginRepositoryStatus('sylius-b-plugin', true, [$this->pr(2, 'Bump b/b from 1.0.0 to 2.0.0', DependabotPullRequest::CHECKS_SUCCESS)]),
            new PluginRepositoryStatus('sylius-c-plugin', null, []),
        );

        self::assertStringContainsString('3 dépôts suivis · 2 PR Dependabot ouvertes · 1 `main` rouge', $output);
        self::assertStringContainsString("### État de `main` inconnu\n- sylius-c-plugin", $output);
    }

    public function testAQuietWeekSaysSo(): void
    {
        $output = $this->render(new PluginRepositoryStatus('sylius-a-plugin', true, []));

        self::assertStringContainsString("### `main` rouge\nAucun\n", $output);
        self::assertStringContainsString("### Prêtes à merger (minor/patch, CI vert)\nAucune\n", $output);
        self::assertStringNotContainsString('État de `main` inconnu', $output);
    }

    private function render(PluginRepositoryStatus ...$statuses): string
    {
        return (new PluginsWatchSection('thibmonier'))->render(array_values($statuses));
    }

    /** @param DependabotPullRequest::CHECKS_* $checks */
    private function pr(int $number, string $title, string $checks): DependabotPullRequest
    {
        return new DependabotPullRequest($number, $title, $checks);
    }

    private function section(string $output, string $title): string
    {
        $start = strpos($output, '### ' . $title);
        self::assertNotFalse($start, "section « $title » missing");
        $end = strpos($output, '###', $start + 4);

        return false === $end ? substr($output, $start) : substr($output, $start, $end - $start);
    }
}
