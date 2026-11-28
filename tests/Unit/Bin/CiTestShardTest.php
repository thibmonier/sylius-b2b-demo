<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-323: bin/ci-test-shard splits the PHPUnit suite by file for the matrix of the CI. Each file runs in exactly one
 * shard, the split does not move between two runs, and the shards carry about the same weight.
 */
final class CiTestShardTest extends TestCase
{
    private const SHARDS = 4;

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/ci-test-shard-' . bin2hex(random_bytes(4));
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->root . '/tests/Unit/Deep');
        $filesystem->mkdir($this->root . '/tests/Functional');
        $filesystem->dumpFile($this->root . '/phpunit.xml.dist', <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit bootstrap="tests/bootstrap.php" failOnWarning="true">
                <testsuites>
                    <testsuite name="Test Suite"><directory>tests</directory></testsuite>
                </testsuites>
                <source><include><directory>src</directory></include></source>
            </phpunit>
            XML);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testEveryTestFileBelongsToExactlyOneShard(): void
    {
        $all = $this->createTestFiles(23);

        $listed = [];
        for ($shard = 1; $shard <= self::SHARDS; ++$shard) {
            array_push($listed, ...$this->shard($shard));
        }

        sort($listed);
        self::assertSame($all, $listed);
    }

    public function testFilesThatAreNotTestsAreLeftOut(): void
    {
        $this->createTestFiles(3);
        file_put_contents($this->root . '/tests/Unit/Helper.php', '<?php');
        file_put_contents($this->root . '/tests/bootstrap.php', '<?php');

        $listed = array_merge(...array_map($this->shard(...), range(1, self::SHARDS)));

        self::assertCount(3, $listed);
        self::assertNotContains('tests/Unit/Helper.php', $listed);
    }

    public function testTheSplitIsTheSameOnEveryRun(): void
    {
        $this->createTestFiles(17);
        $this->weigh(['tests/Unit/ATest.php' => 30.0, 'tests/Unit/BTest.php' => 12.5]);

        self::assertSame($this->shard(2), $this->shard(2));
    }

    public function testTheShardsCarryAboutTheSameWeight(): void
    {
        $files = $this->createTestFiles(13);
        $seconds = [97.0, 80.0, 61.0, 55.0, 40.0, 33.0, 30.0, 12.0, 9.0, 7.0, 4.0, 2.0, 1.0];
        $weights = array_combine($files, $seconds);
        $this->weigh($weights);

        $loads = array_map(
            fn (int $shard): float => (float) array_sum(array_map(static fn (string $file): float => $weights[$file], $this->shard($shard))),
            range(1, self::SHARDS),
        );

        // Every shard is within 5 % of the ideal share: nothing like one shard doing the heavy files of the suite.
        $ideal = array_sum($seconds) / self::SHARDS;
        self::assertLessThanOrEqual($ideal * 1.05, max($loads));
        self::assertGreaterThanOrEqual($ideal * 0.95, min($loads));
    }

    public function testAFileWithoutWeightIsStillPlaced(): void
    {
        $files = $this->createTestFiles(5);
        $this->weigh([$files[0] => 10.0]);

        $listed = array_merge(...array_map($this->shard(...), range(1, self::SHARDS)));

        sort($listed);
        self::assertSame($files, $listed);
    }

    public function testTheShardConfigurationRunsOnlyItsFilesAndKeepsTheRest(): void
    {
        $files = $this->createTestFiles(6);

        $process = $this->script(['--shard=1', '--of=' . self::SHARDS]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $configuration = simplexml_load_file($this->root . '/phpunit.shard.xml');
        self::assertInstanceOf(\SimpleXMLElement::class, $configuration);
        $configured = array_map(strval(...), $configuration->xpath('/phpunit/testsuites/testsuite/file') ?: []);
        self::assertSame($this->shard(1), $configured);
        self::assertSame([], $configuration->xpath('/phpunit/testsuites/testsuite/directory'));
        self::assertSame('tests/bootstrap.php', (string) $configuration['bootstrap']);
        self::assertCount(1, $configuration->xpath('/phpunit/source/include/directory') ?: []);
        self::assertGreaterThan(0, \count($files));
    }

    public function testAShardOutOfRangeIsAUsageError(): void
    {
        self::assertSame(2, $this->script(['--shard=5', '--of=4'])->getExitCode());
        self::assertSame(2, $this->script(['--shard=0', '--of=4'])->getExitCode());
        self::assertSame(2, $this->script(['--of=4'])->getExitCode());
    }

    /** @return list<string> the test files created, sorted, relative to the root */
    private function createTestFiles(int $count): array
    {
        $files = [];
        for ($index = 0; $index < $count; ++$index) {
            $directory = ['Unit', 'Functional', 'Unit/Deep'][$index % 3];
            $files[] = \sprintf('tests/%s/%cTest.php', $directory, 65 + $index);
        }
        foreach ($files as $file) {
            file_put_contents($this->root . '/' . $file, '<?php');
        }
        sort($files);

        return $files;
    }

    /** @param array<string, float> $weights */
    private function weigh(array $weights): void
    {
        new Filesystem()->dumpFile($this->root . '/tests/ci-test-weights.json', json_encode($weights, \JSON_THROW_ON_ERROR));
    }

    /** @return list<string> */
    private function shard(int $shard): array
    {
        $process = $this->script(['--shard=' . $shard, '--of=' . self::SHARDS, '--list']);
        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());

        return array_values(array_filter(explode("\n", $process->getOutput())));
    }

    /** @param list<string> $arguments */
    private function script(array $arguments): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/ci-test-shard', '--root=' . $this->root, ...$arguments]);
        $process->run();

        return $process;
    }
}
