<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-209: bin/plugin-supply-chain (check, apply, list) on repositories of fixtures created in a temporary directory.
 * No test touches a real plugin repository.
 */
final class PluginSupplyChainTest extends TestCase
{
    private const string CHECKOUT = 'actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7.0.1';
    private const string BUILD = 'SyliusLabs/BuildTestAppAction@c3b108b5f3c299c55f429a8cf0ed88734d4f4979 # v4.3.1';
    private const string ZIZMOR = 'ghcr.io/zizmorcore/zizmor:1.31.1@sha256:42d409dbb851d4c5d35a058cf042b828bea1e3f2884378c526eb0974780e18d9';

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/plugin-supply-chain-' . bin2hex(random_bytes(4));
        new Filesystem()->mkdir($this->root);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testAConformingRepositoryPassesTheNineControls(): void
    {
        $repository = $this->repository('conforming');

        $process = $this->execute('check', $repository);

        self::assertSame(0, $process->getExitCode(), $process->getOutput());
        foreach (['pinned-actions', 'permissions', 'persist-credentials', 'pull-request-target', 'zizmor', 'composer-audit', 'policy', 'allow-plugins', 'dependabot'] as $control) {
            self::assertMatchesRegularExpression('/^PASS ' . $control . '\b/m', $process->getOutput());
        }
    }

    public function testARepositoryWithoutLockAuditsAfterTheInstallAndSkipsTheLockReview(): void
    {
        $process = $this->execute('check', $this->repository('no-lock'));

        self::assertSame(0, $process->getExitCode(), $process->getOutput());
        self::assertMatchesRegularExpression('/^N\/A lock-review\b/m', $process->getOutput());
    }

    public function testARepositoryThatTracksItsLockMustAuditTheLock(): void
    {
        $repository = $this->repository('with-lock', trackLock: true);

        $process = $this->execute('check', $repository);

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL composer-audit\b.*--locked/m', $process->getOutput());
        self::assertMatchesRegularExpression('/^FAIL lock-review\b/m', $process->getOutput());
    }

    public function testAnActionPinnedByShaWithItsVersionInACommentIsAccepted(): void
    {
        $process = $this->execute('check', $this->repository('dependabot-bump'));

        self::assertMatchesRegularExpression('/^PASS pinned-actions\b/m', $process->getOutput());
    }

    public function testAnActionReferencedByATagFailsWithFileAndLine(): void
    {
        $repository = $this->repository('tag', workflow: str_replace(self::CHECKOUT, 'actions/checkout@v4', $this->conformingWorkflow()));

        $process = $this->execute('check', $repository);

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL pinned-actions\b.*build\.yaml:\d+/m', $process->getOutput());
    }

    public function testACheckoutWithoutPersistCredentialsFalseFailsWithFileAndLine(): void
    {
        $workflow = (string) preg_replace('/^\s*with:\n\s*persist-credentials: false\n/m', '', $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('credentials', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL persist-credentials\b.*build\.yaml:\d+/m', $process->getOutput());
    }

    public function testAPullRequestTargetTriggerFailsWithFileAndLine(): void
    {
        $workflow = str_replace("on:\n    push:", "on:\n    pull_request_target:\n    push:", $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('target', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL pull-request-target\b.*build\.yaml:\d+/m', $process->getOutput());
    }

    public function testAWorkflowWithoutEmptyPermissionsFails(): void
    {
        $workflow = str_replace("permissions: {}\n", "permissions:\n    contents: read\n", $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('permissions', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL permissions\b/m', $process->getOutput());
    }

    public function testAJobWithoutItsOwnPermissionsFails(): void
    {
        $workflow = str_replace("        permissions:\n            contents: read\n", '', $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('job-permissions', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL permissions\b.*job/m', $process->getOutput());
    }

    public function testAMissingZizmorJobFails(): void
    {
        $workflow = (string) preg_replace('/\n    zizmor:.*$/s', "\n", $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('zizmor', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL zizmor\b/m', $process->getOutput());
    }

    public function testANonBlockingAuditFails(): void
    {
        $workflow = str_replace('composer audit --abandoned=report', 'composer audit --abandoned=report || true', $this->conformingWorkflow());
        $process = $this->execute('check', $this->repository('audit', workflow: $workflow));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL composer-audit\b.*blocking/m', $process->getOutput());
    }

    public function testAMissingPolicyFails(): void
    {
        $composer = $this->composer();
        unset($composer['config']['policy']);

        $process = $this->execute('check', $this->repository('policy', composer: $composer));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL policy\b/m', $process->getOutput());
    }

    public function testAMissingAllowPluginsListFails(): void
    {
        $composer = $this->composer();
        unset($composer['config']['allow-plugins']);

        $process = $this->execute('check', $this->repository('allow-plugins', composer: $composer));

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL allow-plugins\b/m', $process->getOutput());
    }

    public function testAMissingDependabotFileFails(): void
    {
        $repository = $this->repository('dependabot', dependabot: null);

        $process = $this->execute('check', $repository);

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL dependabot\b/m', $process->getOutput());
    }

    public function testADependabotFileWithoutTheGithubActionsEcosystemFails(): void
    {
        $repository = $this->repository('dependabot-composer-only', dependabot: "version: 2\nupdates:\n- package-ecosystem: composer\n  directory: \"/\"\n  schedule:\n    interval: daily\n");

        $process = $this->execute('check', $repository);

        self::assertSame(1, $process->getExitCode());
        self::assertMatchesRegularExpression('/^FAIL dependabot\b.*github-actions/m', $process->getOutput());
    }

    public function testItRefusesADirectoryOutsideTheAllowedRoot(): void
    {
        $outside = sys_get_temp_dir() . '/plugin-supply-chain-outside-' . bin2hex(random_bytes(4));
        new Filesystem()->mkdir($outside);

        try {
            $process = $this->execute('check', $outside);
        } finally {
            new Filesystem()->remove($outside);
        }

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('outside the allowed directory', $process->getErrorOutput());
    }

    public function testItRefusesADirectoryThatIsNotAGitRepository(): void
    {
        $directory = $this->root . '/not-git';
        new Filesystem()->mkdir($directory);

        $process = $this->execute('check', $directory);

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('not a Git repository', $process->getErrorOutput());
    }

    public function testApplyIsADryRunByDefault(): void
    {
        $repository = $this->legacyRepository('dry-run');
        $before = (string) file_get_contents($repository . '/.github/workflows/build.yaml');

        $process = $this->execute('apply', $repository);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame($before, file_get_contents($repository . '/.github/workflows/build.yaml'));
        self::assertStringContainsString('would change .github/workflows/build.yaml', $process->getOutput());
    }

    public function testApplyMakesALegacyRepositoryConform(): void
    {
        $repository = $this->legacyRepository('legacy');

        $apply = $this->execute('apply', $repository, '--write');
        $check = $this->execute('check', $repository);

        self::assertSame(0, $apply->getExitCode(), $apply->getErrorOutput());
        self::assertSame(0, $check->getExitCode(), $check->getOutput());
    }

    public function testApplyPinsTheActionsWithTheirVersionInAComment(): void
    {
        $repository = $this->legacyRepository('pins');

        $this->execute('apply', $repository, '--write');

        $workflow = (string) file_get_contents($repository . '/.github/workflows/build.yaml');
        self::assertStringContainsString('uses: ' . self::CHECKOUT, $workflow);
        self::assertStringContainsString('uses: ' . self::BUILD, $workflow);
    }

    public function testApplyIsIdempotent(): void
    {
        $repository = $this->legacyRepository('idempotent');
        $this->execute('apply', $repository, '--write');
        $once = $this->snapshot($repository);

        $second = $this->execute('apply', $repository, '--write');

        self::assertSame(0, $second->getExitCode(), $second->getErrorOutput());
        self::assertSame($once, $this->snapshot($repository));
    }

    public function testApplyOnlyTouchesGithubAndTheConfigSectionOfComposerJson(): void
    {
        $repository = $this->legacyRepository('scope');
        $composerBefore = json_decode((string) file_get_contents($repository . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);

        $this->execute('apply', $repository, '--write');

        $composerAfter = json_decode((string) file_get_contents($repository . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
        unset($composerBefore['config'], $composerAfter['config']);
        self::assertSame($composerBefore, $composerAfter);
        $changed = trim((string) $this->git($repository, 'status', '--porcelain'));
        foreach (explode("\n", $changed) as $line) {
            self::assertMatchesRegularExpression('/^\s*\S+\s+(\.github\/|composer\.json$)/', $line);
        }
    }

    public function testApplyAddsAnExplicitAllowPluginsListToARepositoryWithoutConfig(): void
    {
        $composer = $this->composer();
        unset($composer['config']);
        $repository = $this->legacyRepository('pack', composer: $composer);

        $this->execute('apply', $repository, '--write');

        $after = json_decode((string) file_get_contents($repository . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([], $after['config']['allow-plugins']);
        self::assertArrayHasKey('policy', $after['config']);
    }

    public function testApplyAddsTheGithubActionsEcosystemToAnExistingDependabotFile(): void
    {
        $repository = $this->legacyRepository('dependabot-append');

        $this->execute('apply', $repository, '--write');

        $dependabot = (string) file_get_contents($repository . '/.github/dependabot.yml');
        self::assertStringContainsString('package-ecosystem: composer', $dependabot);
        self::assertStringContainsString('package-ecosystem: github-actions', $dependabot);
    }

    public function testApplyWriteRefusesABranchBehindTheDefaultBranch(): void
    {
        $repository = $this->legacyRepository('stale');
        $this->advanceOrigin($repository);

        $process = $this->execute('apply', $repository, '--write');

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('behind origin/main', $process->getErrorOutput());
    }

    public function testApplyWriteRefusesTheDefaultBranchItself(): void
    {
        $repository = $this->legacyRepository('on-default', hardeningBranch: false);

        $process = $this->execute('apply', $repository, '--write');

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('default branch', $process->getErrorOutput());
    }

    public function testApplyWriteRefusesAWorkflowReferencingAnUnknownAction(): void
    {
        $workflow = str_replace('actions/checkout@v4', 'someone/unknown-action@v1', $this->legacyWorkflow());
        $repository = $this->legacyRepository('unknown', workflow: $workflow);

        $process = $this->execute('apply', $repository, '--write');

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('someone/unknown-action@v1', $process->getErrorOutput());
    }

    #[DataProvider('listedRepositories')]
    public function testTheListedRepositoriesAreTheSyliusPluginsAndPacks(string $name): void
    {
        self::assertMatchesRegularExpression('/^sylius-[a-z0-9-]+$/', $name);
        self::assertStringNotContainsString('fork', $name);
    }

    /** @return iterable<string, array{string}> */
    public static function listedRepositories(): iterable
    {
        $process = new Process([PHP_BINARY, self::bin(), 'list']);
        $process->mustRun();
        foreach (array_filter(explode("\n", $process->getOutput())) as $name) {
            yield $name => [$name];
        }
    }

    public function testTheListHasNoDuplicateAndStartsWithThePilot(): void
    {
        $process = new Process([PHP_BINARY, self::bin(), 'list']);
        $process->mustRun();
        $names = array_values(array_filter(explode("\n", $process->getOutput())));

        self::assertSame($names, array_values(array_unique($names)));
        self::assertSame('sylius-quick-order-plugin', $names[0]);
    }

    private static function bin(): string
    {
        return dirname(__DIR__, 3) . '/bin/plugin-supply-chain';
    }

    private function execute(string $mode, string $directory, string ...$options): Process
    {
        $process = new Process([PHP_BINARY, self::bin(), $mode, $directory, '--allowed-root=' . $this->root, ...$options]);
        $process->run();

        return $process;
    }

    /** @return array<string, mixed> */
    private function composer(): array
    {
        return [
            'name' => 'tmonier/sylius-fixture-plugin',
            'type' => 'sylius-plugin',
            'require' => ['php' => '^8.3'],
            'config' => [
                'sort-packages' => true,
                'allow-plugins' => ['symfony/flex' => true, 'phpstan/extension-installer' => true],
                'policy' => ['advisories' => ['block' => true, 'audit' => 'fail']],
            ],
        ];
    }

    private function conformingWorkflow(): string
    {
        return $this->fill(<<<YAML
            name: Build

            on:
                push:
                    branches-ignore:
                        - 'dependabot/**'
                pull_request:

            permissions: {}

            jobs:
                tests:
                    runs-on: ubuntu-latest
                    permissions:
                        contents: read
                    steps:
                        -
                            uses: %CHECKOUT%
                            with:
                                persist-credentials: false

                        -
                            name: Build Sylius Test Application
                            uses: %BUILD%

                        -
                            name: Audit of the PHP dependencies
                            run: composer audit --abandoned=report

                zizmor:
                    name: Workflow audit (zizmor)
                    runs-on: ubuntu-latest
                    permissions:
                        contents: read
                    env:
                        ZIZMOR_IMAGE: %ZIZMOR%
                    steps:
                        - uses: %CHECKOUT%
                          with:
                              persist-credentials: false

                        - name: zizmor (offline)
                          run: docker run --rm -v "\$PWD:/work" -w /work "\$ZIZMOR_IMAGE" --offline .github

            YAML);
    }

    private function legacyWorkflow(): string
    {
        return <<<'YAML'
            name: Build

            on:
                push:
                    branches-ignore:
                        - 'dependabot/**'
                pull_request:
                workflow_dispatch: ~

            concurrency:
                group: ${{ github.workflow }}-${{ github.ref }}
                cancel-in-progress: true

            jobs:
                tests:
                    runs-on: ubuntu-latest

                    name: "Sylius ${{ matrix.sylius }}"

                    strategy:
                        matrix:
                            sylius: ["~2.3.0"]

                    steps:
                        -
                            uses: actions/checkout@v4

                        -
                            name: Build Sylius Test Application
                            uses: SyliusLabs/BuildTestAppAction@v4
                            with:
                                sylius_version: "${{ matrix.sylius }}"

                        -
                            name: Run static analysis
                            run: vendor/bin/phpstan analyse --no-progress

            YAML;
    }

    private function fill(string $workflow): string
    {
        return strtr($workflow, ['%CHECKOUT%' => self::CHECKOUT, '%BUILD%' => self::BUILD, '%ZIZMOR%' => self::ZIZMOR]);
    }

    /** @param array<string, mixed>|null $composer */
    private function repository(string $name, ?string $workflow = null, ?array $composer = null, ?string $dependabot = "version: 2\nupdates:\n- package-ecosystem: composer\n  directory: \"/\"\n  schedule:\n    interval: weekly\n- package-ecosystem: github-actions\n  directory: \"/\"\n  schedule:\n    interval: weekly\n", bool $trackLock = false): string
    {
        $files = [
            '.github/workflows/build.yaml' => $workflow ?? $this->conformingWorkflow(),
            'composer.json' => json_encode($composer ?? $this->composer(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
        ];
        if (null !== $dependabot) {
            $files['.github/dependabot.yml'] = $dependabot;
        }
        if ($trackLock) {
            $files['composer.lock'] = "{}\n";
        }

        return $this->createRepository($name, $files);
    }

    /** @param array<string, mixed>|null $composer */
    private function legacyRepository(string $name, ?string $workflow = null, ?array $composer = null, bool $hardeningBranch = true): string
    {
        return $this->createRepository($name, [
            '.github/workflows/build.yaml' => $workflow ?? $this->legacyWorkflow(),
            'composer.json' => json_encode($composer ?? $this->legacyComposer(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
            '.github/dependabot.yml' => "version: 2\nupdates:\n- package-ecosystem: composer\n  directory: \"/\"\n  schedule:\n    interval: daily\n",
        ], $hardeningBranch);
    }

    /** @return array<string, mixed> */
    private function legacyComposer(): array
    {
        $composer = $this->composer();
        unset($composer['config']['policy']);

        return $composer;
    }

    /** @param array<string, string> $files */
    private function createRepository(string $name, array $files, bool $hardeningBranch = true): string
    {
        $filesystem = new Filesystem();
        $origin = $this->root . '/origin-' . $name . '.git';
        $directory = $this->root . '/' . $name;
        $this->git($this->root, 'init', '--bare', '--initial-branch=main', $origin);
        $this->git($this->root, 'clone', $origin, $directory);
        foreach ($files as $path => $content) {
            $filesystem->dumpFile($directory . '/' . $path, $content);
        }
        $this->git($directory, 'checkout', '-b', 'main');
        $this->git($directory, 'add', '.');
        $this->git($directory, '-c', 'user.name=Test', '-c', 'user.email=test@example.org', 'commit', '-m', 'fixture');
        $this->git($directory, 'push', '-u', 'origin', 'main');
        $this->git($directory, 'remote', 'set-head', 'origin', 'main');
        if ($hardeningBranch) {
            $this->git($directory, 'checkout', '-b', 'chore/supply-chain-hardening');
        }

        return $directory;
    }

    private function advanceOrigin(string $repository): void
    {
        $clone = $repository . '-other';
        $this->git($this->root, 'clone', trim($this->git($repository, 'remote', 'get-url', 'origin')), $clone);
        new Filesystem()->dumpFile($clone . '/CHANGELOG.md', "next\n");
        $this->git($clone, 'add', '.');
        $this->git($clone, '-c', 'user.name=Test', '-c', 'user.email=test@example.org', 'commit', '-m', 'next');
        $this->git($clone, 'push', 'origin', 'HEAD:main');
    }

    private function git(string $directory, string ...$arguments): string
    {
        $process = new Process(['git', ...$arguments], $directory);
        $process->mustRun();

        return $process->getOutput();
    }

    /** @return array<string, string> */
    private function snapshot(string $repository): array
    {
        $snapshot = [];
        foreach (['.github/workflows/build.yaml', '.github/dependabot.yml', 'composer.json'] as $path) {
            $snapshot[$path] = (string) file_get_contents($repository . '/' . $path);
        }

        return $snapshot;
    }
}
