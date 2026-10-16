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
 */
final readonly class ResetStatus
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/demo-reset-failed')]
        private string $markerFile,
    ) {
    }

    public function markFailed(): void
    {
        file_put_contents($this->markerFile, (new \DateTimeImmutable())->format(\DATE_ATOM));
    }

    public function markSucceeded(): void
    {
        if (is_file($this->markerFile)) {
            unlink($this->markerFile);
        }
    }

    public function hasFailed(): bool
    {
        return is_file($this->markerFile);
    }
}
