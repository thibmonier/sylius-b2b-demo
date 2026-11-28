<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-323: bin/ci-test-report reads the JUnit logs of the shards: slowest files for the job summary, weights for the
 * split, and the check that no test was lost between the shards and the full suite.
 */
final class CiTestReportTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ci-test-report-' . bin2hex(random_bytes(4));
        new Filesystem()->mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->directory);
    }

    public function testTheSlowestFilesComeFirstWithTheirDuration(): void
    {
        $first = $this->junit('a', ['tests/Unit/FastTest.php' => [0.2, 0.3], 'tests/Unit/SlowTest.php' => [4.0, 3.5]]);
        $second = $this->junit('b', ['tests/Functional/MiddleTest.php' => [2.0]]);

        $process = $this->report(['slowest', '--top=2', $first, $second]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $output = $process->getOutput();
        self::assertLessThan(strpos($output, 'MiddleTest'), strpos($output, 'SlowTest'));
        self::assertStringContainsString('| 1 | `tests/Unit/SlowTest.php` | 2 | 7.50 |', $output);
        self::assertStringNotContainsString('FastTest', $output);
    }

    public function testTheWeightsAreTheSecondsPerFile(): void
    {
        $report = $this->junit('a', ['tests/Unit/SlowTest.php' => [4.0, 3.5]]);

        $weights = json_decode($this->report(['weights', $report])->getOutput(), true, flags: \JSON_THROW_ON_ERROR);

        self::assertSame(['tests/Unit/SlowTest.php' => 7.5], $weights);
    }

    public function testItAcceptsShardsThatRunTheWholeSuite(): void
    {
        $list = $this->declared(['tests/Unit/ATest.php' => 2, 'tests/Unit/BTest.php' => 1]);
        $first = $this->junit('a', ['tests/Unit/ATest.php' => [0.1, 0.1]]);
        $second = $this->junit('b', ['tests/Unit/BTest.php' => [0.1]]);

        $process = $this->report(['verify', '--expected=' . $list, $first, $second]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertStringContainsString('Tests run by the shards: 3, tests of the full suite: 3', $process->getOutput());
    }

    public function testAFileThatNoShardRanFailsTheCheck(): void
    {
        $list = $this->declared(['tests/Unit/ATest.php' => 2, 'tests/Unit/BTest.php' => 1]);
        $only = $this->junit('a', ['tests/Unit/ATest.php' => [0.1, 0.1]]);

        $process = $this->report(['verify', '--expected=' . $list, $only]);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('`tests/Unit/BTest.php`: 1 tests declared, 0 run', $process->getErrorOutput());
    }

    public function testATestRunTwiceFailsTheCheck(): void
    {
        $list = $this->declared(['tests/Unit/ATest.php' => 1]);
        $first = $this->junit('a', ['tests/Unit/ATest.php' => [0.1]]);
        $second = $this->junit('b', ['tests/Unit/ATest.php' => [0.1]]);

        $process = $this->report(['verify', '--expected=' . $list, $first, $second]);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('`tests/Unit/ATest.php`: 1 tests declared, 2 run', $process->getErrorOutput());
    }

    public function testAnUnreadableReportIsAnError(): void
    {
        self::assertSame(2, $this->report(['slowest', $this->directory . '/absent.xml'])->getExitCode());
        self::assertSame(2, $this->report(['verify', $this->directory . '/absent.xml'])->getExitCode());
    }

    /** @param array<string, list<float>> $files the duration of each test case, by test file */
    private function junit(string $name, array $files): string
    {
        $cases = '';
        foreach ($files as $file => $times) {
            foreach ($times as $index => $time) {
                $cases .= \sprintf('<testcase name="test%d" file="%s/%s" time="%s"/>', $index, $this->directory, $file, $time);
            }
        }
        $path = $this->directory . '/junit-' . $name . '.xml';
        file_put_contents($path, '<?xml version="1.0"?><testsuites><testsuite name="s">' . $cases . '</testsuite></testsuites>');

        return $path;
    }

    /** @param array<string, int> $files the number of tests of each test file */
    private function declared(array $files): string
    {
        $classes = '';
        foreach ($files as $file => $count) {
            $classes .= \sprintf('<testClass name="X" file="%s/%s">%s</testClass>', $this->directory, $file, str_repeat('<testMethod id="x" name="x"/>', $count));
        }
        $path = $this->directory . '/list.xml';
        file_put_contents($path, '<?xml version="1.0"?><testSuite xmlns="https://xml.phpunit.de/testSuite"><tests>' . $classes . '</tests></testSuite>');

        return $path;
    }

    /** @param list<string> $arguments */
    private function report(array $arguments): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/ci-test-report', '--root=' . $this->directory, ...$arguments]);
        $process->run();

        return $process;
    }
}
