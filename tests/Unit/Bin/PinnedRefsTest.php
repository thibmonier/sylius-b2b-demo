<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * US-081: bin/check-pinned-refs on throwaway trees, the real repository, and the format of the dated exceptions.
 */
final class PinnedRefsTest extends TestCase
{
    private const SHA = '11d5960a326750d5838078e36cf38b85af677262';

    private const DIGEST = 'sha256:24989179f58856db763f418010c1169fecc944e3ec1ea7e921572d83eb070f70';

    private string $tree;

    protected function setUp(): void
    {
        $this->tree = sys_get_temp_dir() . '/pinned-refs-' . bin2hex(random_bytes(4));
        new Filesystem()->mkdir([$this->tree . '/.github/workflows', $this->tree . '/docker']);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->tree);
    }

    public function testTheRepositoryIsFullyPinned(): void
    {
        $process = new Process([\dirname(__DIR__, 3) . '/bin/check-pinned-refs']);
        $process->run();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    }

    public function testAnActionReferencedByATagIsRefused(): void
    {
        $this->workflow("steps:\n  - uses: actions/checkout@v4\n");

        self::assertStringContainsString('actions/checkout@v4', $this->failure());
    }

    public function testAPinnedActionWithoutVersionCommentIsRefused(): void
    {
        $this->workflow("steps:\n  - uses: actions/checkout@" . self::SHA . "\n");

        self::assertStringContainsString('without a version comment', $this->failure());
    }

    public function testAPinnedActionWithItsVersionIsAccepted(): void
    {
        $this->workflow("steps:\n  - uses: actions/checkout@" . self::SHA . " # v4.4.0\n  - uses: ./local-action\n");

        self::assertSame(0, $this->check()->getExitCode());
    }

    public function testAServiceImageAndADockerRunImageMustCarryADigest(): void
    {
        $this->workflow("services:\n  db:\n    image: mysql:8.4\nrun: docker run mysql:8.4 --x\n");

        $failure = $this->failure();
        self::assertSame(2, substr_count($failure, 'mysql:8.4'));
    }

    public function testADockerfileImageByTagIsRefusedButStageAliasesAreFine(): void
    {
        file_put_contents($this->tree . '/docker/Dockerfile', "ARG PHP_IMAGE=php:8.4\nFROM \${PHP_IMAGE} AS base\nFROM base AS vendor\nCOPY --from=composer:2 /usr/bin/composer /c\nCOPY --from=vendor /a /b\nFROM node:22 AS assets\n");

        $failure = $this->failure();
        foreach (['php:8.4', 'composer:2', 'node:22'] as $image) {
            self::assertStringContainsString($image, $failure);
        }
        self::assertStringNotContainsString('image not pinned by digest: vendor', $failure);
        self::assertStringNotContainsString('image not pinned by digest: base', $failure);
    }

    public function testADockerfileFullyPinnedIsAccepted(): void
    {
        file_put_contents($this->tree . '/docker/Dockerfile', 'FROM node:22@' . self::DIGEST . " AS assets\nFROM scratch\n");

        self::assertSame(0, $this->check()->getExitCode());
    }

    public function testTheScanExceptionsCarryAReasonAndAReviewDate(): void
    {
        $file = \dirname(__DIR__, 3) . '/.trivyignore.yaml';
        $config = Yaml::parseFile($file);
        self::assertIsArray($config);
        $entries = $config['vulnerabilities'] ?? null;

        self::assertIsArray($entries);
        /** @var array<string, mixed> $entry */
        foreach ($entries as $entry) {
            self::assertMatchesRegularExpression('/^(CVE|GHSA)-/', self::text($entry['id'] ?? ''));
            self::assertNotSame('', trim(self::text($entry['statement'] ?? '')), 'An exception needs its reason.');
            $date = $entry['expired_at'] ?? null;
            self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', self::text($date), 'An exception needs its review date.');
        }
    }

    public function testTheAuditExceptionsCarryAReasonAndAReviewDate(): void
    {
        /** @var array{yarn: list<array<string, mixed>>} $data */
        $data = json_decode((string) file_get_contents(\dirname(__DIR__, 3) . '/security/audit-exceptions.json'), true, flags: \JSON_THROW_ON_ERROR);

        foreach ($data['yarn'] as $entry) {
            foreach (['advisory', 'package', 'reason'] as $key) {
                self::assertNotSame('', trim(self::text($entry[$key] ?? '')), $key);
            }
            self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', self::text($entry['review_by'] ?? ''));
        }
    }

    private static function text(mixed $value): string
    {
        return match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
            \is_scalar($value) => (string) $value,
            default => '',
        };
    }

    private function workflow(string $content): void
    {
        file_put_contents($this->tree . '/.github/workflows/ci.yaml', $content);
    }

    private function failure(): string
    {
        $process = $this->check();
        self::assertSame(1, $process->getExitCode(), $process->getOutput());

        return $process->getErrorOutput();
    }

    private function check(): Process
    {
        $process = new Process([\dirname(__DIR__, 3) . '/bin/check-pinned-refs', $this->tree]);
        $process->run();

        return $process;
    }
}
