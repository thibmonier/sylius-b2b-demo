<?php

declare(strict_types=1);

namespace App\Tests\Unit\Supply;

use App\Tests\Support\Supply\WorkflowRules;
use PHPUnit\Framework\TestCase;

/**
 * US-208: GitHub workflows are hardened (no default token permission, no persisted credentials, no pull_request_target).
 * A violation is reported as "file:line rule", so the pull request shows what to fix.
 */
final class WorkflowHardeningTest extends TestCase
{
    public function testEveryWorkflowOfTheRepositoryRespectsTheRules(): void
    {
        $root = \dirname(__DIR__, 3);
        $files = glob($root . '/.github/workflows/*.y*ml') ?: [];
        $violations = [];
        foreach ($files as $file) {
            $violations = [...$violations, ...WorkflowRules::violations(basename($file), (string) file_get_contents($file))];
        }

        self::assertNotEmpty($files);
        self::assertSame([], $violations);
    }

    public function testAHardenedWorkflowIsAccepted(): void
    {
        self::assertSame([], WorkflowRules::violations('ok.yaml', $this->workflow()));
    }

    public function testACheckoutWithoutPersistCredentialsIsRefusedWithFileAndLine(): void
    {
        $yaml = str_replace("                  persist-credentials: false\n", '', $this->workflow());

        $violations = WorkflowRules::violations('ci.yaml', $yaml);

        self::assertCount(1, $violations);
        self::assertStringStartsWith('ci.yaml:11 persist-credentials:', $violations[0]);
    }

    public function testAWorkflowWithoutEmptyPermissionsIsRefused(): void
    {
        $violations = WorkflowRules::violations('ci.yaml', str_replace("permissions: {}\n", "permissions:\n    contents: read\n", $this->workflow()));

        self::assertCount(1, $violations);
        self::assertStringContainsString('workflow-permissions', $violations[0]);
    }

    public function testAJobWithoutContentsReadIsRefused(): void
    {
        $violations = WorkflowRules::violations('ci.yaml', str_replace("            contents: read\n", "            contents: write\n", $this->workflow()));

        self::assertCount(1, $violations);
        self::assertStringContainsString('job-permissions', $violations[0]);
    }

    public function testThePullRequestTargetTriggerIsRefused(): void
    {
        $violations = WorkflowRules::violations('ci.yaml', str_replace("pull_request: ~\n", "pull_request_target: ~\n", $this->workflow()));

        self::assertCount(1, $violations);
        self::assertStringContainsString('no-pull-request-target', $violations[0]);
    }

    private function workflow(): string
    {
        return <<<'YAML'
            name: CI
            on:
                pull_request: ~
            permissions: {}
            jobs:
                tests:
                    runs-on: ubuntu-latest
                    permissions:
                        contents: read
                    steps:
                        - uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7.0.1
                          with:
                              persist-credentials: false
                        - run: echo ok

            YAML;
    }
}
