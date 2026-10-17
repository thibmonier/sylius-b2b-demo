<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Sylius\Bundle\FixturesBundle\Loader\FixtureLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteInterface;

/**
 * Test environment only (bin/profile-reset): when PROFILE_RESET_OUT names a file, appends one JSON line per fixture
 * (statements by kind, duration, memory) and a final "total" line. Without that variable it is a plain pass-through.
 */
final class FixtureProfiler implements FixtureLoaderInterface
{
    private readonly ?string $out;

    private float $startedAt;

    public function __construct(private readonly FixtureLoaderInterface $inner)
    {
        $out = $_SERVER['PROFILE_RESET_OUT'] ?? $_ENV['PROFILE_RESET_OUT'] ?? getenv('PROFILE_RESET_OUT');
        $this->out = \is_string($out) && '' !== $out ? $out : null;
        $this->startedAt = microtime(true);
        if (null !== $this->out) {
            register_shutdown_function($this->writeTotal(...));
        }
    }

    public function load(SuiteInterface $suite, FixtureInterface $fixture, array $options): void
    {
        if (null === $this->out) {
            $this->inner->load($suite, $fixture, $options);

            return;
        }

        $before = \count(QueryCounter::statements());
        $start = microtime(true);
        $this->inner->load($suite, $fixture, $options);
        $seconds = microtime(true) - $start;

        $statements = QueryCounter::statements();
        $this->write(['fixture' => $fixture->getName()] + $this->kinds(\array_slice($statements, $before)) + [
            'top' => $this->top(\array_slice($statements, $before)),
            'seconds' => round($seconds, 3),
            'memory_mb' => round(memory_get_usage(true) / 1048576, 1),
        ]);
    }

    private function writeTotal(): void
    {
        $statements = QueryCounter::statements();
        $this->write(['fixture' => '__total'] + $this->kinds($statements) + [
            'seconds' => round(microtime(true) - $this->startedAt, 3),
            'memory_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
        ]);
    }

    /**
     * @param list<string> $statements
     *
     * @return array{select: int, insert: int, update: int, delete: int, other: int}
     */
    private function kinds(array $statements): array
    {
        $kinds = ['select' => 0, 'insert' => 0, 'update' => 0, 'delete' => 0, 'other' => 0];
        foreach ($statements as $sql) {
            $kind = strtolower(strtok(ltrim($sql), " \t\n(") ?: '');
            ++$kinds[isset($kinds[$kind]) ? $kind : 'other'];
        }

        return $kinds;
    }

    /**
     * The most frequent statements once their parameters are blanked out: where a fixture spends its queries.
     *
     * @param list<string> $statements
     *
     * @return array<string, int>
     */
    private function top(array $statements): array
    {
        $counts = array_count_values(array_map(static fn (string $sql): string => substr($sql, 0, 110), $statements));
        arsort($counts);

        return \array_slice($counts, 0, 14, true);
    }

    /** @param array<string, mixed> $line */
    private function write(array $line): void
    {
        file_put_contents((string) $this->out, json_encode($line, \JSON_THROW_ON_ERROR) . "\n", \FILE_APPEND);
    }
}
