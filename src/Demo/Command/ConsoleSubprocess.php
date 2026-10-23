<?php

declare(strict_types=1);

namespace App\Demo\Command;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

/**
 * Runs a console command of this application in a PHP process of its own (US-049). The nightly reset warms the cache
 * up this way: the fixtures leave the memory of their process full, and a "memory exhausted" fatal error of the
 * warm-up (2026-10-17) then killed the whole reset without a trace.
 *
 * The sub-process is the same PHP binary, runs as the same user (the cache files stay writable by the web server),
 * inherits the environment (APP_ENV, DATABASE_URL, APP_CACHE_DIR…) and the memory limit of the parent, so the
 * `-d memory_limit=1G` of the crontab applies to it. Arguments are passed as a list: no shell, nothing to escape.
 */
final readonly class ConsoleSubprocess
{
    /** Per sub-process; ResetStatus::MAX_RUN_SECONDS stays above the sum of the worst case of a live reset. */
    public const TIMEOUT_SECONDS = 300;

    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private string $projectDir,
        #[Autowire(param: 'kernel.environment')]
        private string $environment,
    ) {
    }

    /**
     * @param list<string> $options
     *
     * @return int the exit code of the sub-process (255 after a PHP fatal error)
     */
    public function run(string $command, array $options, OutputInterface $output): int
    {
        $errors = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $process = new Process(
            [\PHP_BINARY, '-d', 'memory_limit=' . ini_get('memory_limit'), $this->projectDir . '/bin/console', $command, '--env=' . $this->environment, '--no-interaction', ...$options],
            $this->projectDir,
            timeout: self::TIMEOUT_SECONDS,
        );

        return $process->run(static function (string $type, string $buffer) use ($output, $errors): void {
            (Process::ERR === $type ? $errors : $output)->write($buffer);
        });
    }
}
