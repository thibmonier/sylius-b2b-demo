<?php

declare(strict_types=1);

namespace App\Demo\Command;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Keeps two resets (the 03:00 cron and a manual run) from working on the same database together (US-049). A non
 * blocking flock on a file: the system drops it when the process ends, whatever the way (SIGTERM, SIGKILL, fatal
 * error), so a lock file left behind never blocks the next reset.
 */
final class ResetLock
{
    /** @var resource|null */
    private $handle;

    public function __construct(
        #[Autowire('%demo.reset_marker_file%.lock')]
        private readonly string $lockFile,
    ) {
    }

    /** False when another process holds the lock; it answers at once. */
    public function acquire(): bool
    {
        $handle = fopen($this->lockFile, 'c');
        if (false === $handle) {
            return false;
        }
        if (!flock($handle, \LOCK_EX | \LOCK_NB)) {
            fclose($handle);

            return false;
        }
        $this->handle = $handle;

        return true;
    }

    public function release(): void
    {
        if (null === $this->handle) {
            return;
        }
        flock($this->handle, \LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }
}
