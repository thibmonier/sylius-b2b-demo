<?php

declare(strict_types=1);

namespace App\Demo\Command;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Remembers a failed nightly reset until a reset succeeds (US-033). Railway sends no e-mail for a failed cron job,
 * so /healthz answers 503 meanwhile and an external uptime monitor on /healthz raises the alert.
 *
 * A marker file outside the database: the reset runs in one transaction that a failure rolls back, and the web
 * server must see the marker written by the cron job (same container). A new deployment starts without it.
 *
 * The marker is written when the reset STARTS and removed only when it succeeds (US-049): a fatal error such as
 * "Allowed memory size exhausted" is not an exception, so nothing could report it afterwards. Two nets cover it:
 * a shutdown function turns the marker into a failure at once when the process ends without reporting (fatal
 * error, exit), and a marker that stays "running" longer than MAX_RUN_SECONDS counts as a failure (SIGKILL, kill by
 * the out-of-memory killer, container stop: no PHP code runs then). A reset in progress is not a failure: the
 * monitor must not alert every night at 03:00.
 */
final class ResetStatus
{
    /**
     * A nightly reset lasts minutes; beyond this a "running" marker means the process died without a word. Above the
     * worst case of a live reset (two sub-processes of ConsoleSubprocess::TIMEOUT_SECONDS, fixtures, margin), see ResetStatusTest.
     */
    public const MAX_RUN_SECONDS = 1800;

    private const RUNNING = 'running';

    private const FAILED = 'failed';

    private const STAMP_SUFFIX = '.last-success';

    private bool $reported = false;

    private bool $watching = false;

    public function __construct(
        #[Autowire('%demo.reset_marker_file%')]
        private readonly string $markerFile,
    ) {
    }

    public function markStarted(): void
    {
        $this->reported = false;
        $this->watchForDeathWithoutReport();
        // The previous failure stays visible until a reset succeeds.
        if ($this->content() !== self::FAILED) {
            $this->write(self::RUNNING);
        }
    }

    public function markFailed(): void
    {
        $this->reported = true;
        $this->write(self::FAILED);
    }

    public function markSucceeded(): void
    {
        $this->reported = true;
        if (is_file($this->markerFile)) {
            unlink($this->markerFile);
        }
        $this->stamp();
    }

    /**
     * Identifies the last successful reset (empty before the first one): a guided journey (US-105) compares it with the one
     * seen when the visitor started, to send them back to step 1 once the demo has been reset.
     */
    public function lastSuccess(): string
    {
        $content = @file_get_contents($this->markerFile . self::STAMP_SUFFIX);

        return false === $content ? '' : trim($content);
    }

    public function hasFailed(): bool
    {
        $state = $this->content();
        if (null === $state) {
            return false;
        }

        if (self::RUNNING !== $state) {
            return true;
        }
        // Unknown age (the marker vanished meanwhile): never a false alert.
        $modifiedAt = @filemtime($this->markerFile);

        return false !== $modifiedAt && (time() - $modifiedAt) > self::MAX_RUN_SECONDS;
    }

    private function stamp(): void
    {
        $temporary = $this->markerFile . self::STAMP_SUFFIX . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($temporary, bin2hex(random_bytes(6)));
        rename($temporary, $this->markerFile . self::STAMP_SUFFIX);
    }

    private function watchForDeathWithoutReport(): void
    {
        if ($this->watching) {
            return;
        }
        $this->watching = true;
        register_shutdown_function(function (): void {
            if (!$this->reported) {
                $this->markFailed();
            }
        });
    }

    private function write(string $state): void
    {
        // Written aside then renamed: the web server never reads a half-written marker.
        $temporary = $this->markerFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($temporary, $state . ' ' . (new \DateTimeImmutable())->format(\DATE_ATOM));
        rename($temporary, $this->markerFile);
    }

    /** The state word of the marker (running or failed), null without marker. */
    private function content(): ?string
    {
        $content = is_file($this->markerFile) ? file_get_contents($this->markerFile) : false;
        if (false === $content) {
            return null;
        }

        // Empty: read between the creation and the content of a write, the reset is still running.
        return '' === $content || str_starts_with($content, self::RUNNING) ? self::RUNNING : self::FAILED;
    }
}
