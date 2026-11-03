<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\DependabotPullRequest;
use App\Demo\Stack\GhPluginRepositoriesSource;
use PHPUnit\Framework\TestCase;

final class GhPluginRepositoriesSourceTest extends TestCase
{
    public function testAFailedCheckRunMakesTheDefaultBranchRed(): void
    {
        $status = $this->source(checkRuns: ['check_runs' => [['conclusion' => 'success'], ['conclusion' => 'failure']]])->statuses()[0];

        self::assertFalse($status->mainGreen);
    }

    public function testSkippedAndCancelledRunsKeepTheDefaultBranchGreen(): void
    {
        $status = $this->source(checkRuns: ['check_runs' => [['conclusion' => 'success'], ['conclusion' => 'skipped'], ['conclusion' => 'cancelled']]])->statuses()[0];

        self::assertTrue($status->mainGreen);
    }

    public function testAnUnreadableDefaultBranchIsUnknown(): void
    {
        $status = $this->source(checkRuns: null)->statuses()[0];

        self::assertNull($status->mainGreen);
        self::assertSame([], $status->pullRequests);
    }

    public function testThePullRequestChecksAreSummarised(): void
    {
        $prs = $this->source(prs: [
            ['number' => 1, 'title' => 'green', 'statusCheckRollup' => [['status' => 'COMPLETED', 'conclusion' => 'SUCCESS'], ['state' => 'SUCCESS']]],
            ['number' => 2, 'title' => 'red', 'statusCheckRollup' => [['status' => 'COMPLETED', 'conclusion' => 'SUCCESS'], ['status' => 'COMPLETED', 'conclusion' => 'FAILURE']]],
            ['number' => 3, 'title' => 'running', 'statusCheckRollup' => [['status' => 'IN_PROGRESS', 'conclusion' => '']]],
            ['number' => 4, 'title' => 'no check yet', 'statusCheckRollup' => []],
        ])->statuses()[0]->pullRequests;

        self::assertSame(
            [DependabotPullRequest::CHECKS_SUCCESS, DependabotPullRequest::CHECKS_FAILURE, DependabotPullRequest::CHECKS_PENDING, DependabotPullRequest::CHECKS_PENDING],
            array_map(static fn (DependabotPullRequest $pr): string => $pr->checks, $prs),
        );
    }

    public function testItAsksGitHubForTheDependabotPullRequestsOfEachRepository(): void
    {
        $commands = [];
        $source = new GhPluginRepositoriesSource('thibmonier', ['sylius-a-plugin', 'sylius-b-plugin'], static function (array $command) use (&$commands): string {
            $commands[] = implode(' ', $command);

            return '[]';
        });

        self::assertCount(2, $source->statuses());
        self::assertContains('gh pr list -R thibmonier/sylius-b-plugin --author app/dependabot --state open --json number,title,statusCheckRollup', $commands);
        self::assertContains('gh api repos/thibmonier/sylius-a-plugin/commits/main/check-runs?per_page=100', $commands);
    }

    /**
     * @param array<mixed>|null $checkRuns
     * @param list<array<mixed>> $prs
     */
    private function source(?array $checkRuns = ['check_runs' => []], array $prs = []): GhPluginRepositoriesSource
    {
        return new GhPluginRepositoriesSource('thibmonier', ['sylius-a-plugin'], static fn (array $command): ?string => match ($command[1]) {
            'api' => null === $checkRuns ? null : json_encode($checkRuns, \JSON_THROW_ON_ERROR),
            default => null === $checkRuns ? null : json_encode($prs, \JSON_THROW_ON_ERROR),
        });
    }
}
