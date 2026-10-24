<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

/**
 * US-184: the simulated outage of the fictitious platform. It lasts until an administrator relaunches a send: the relaunch
 * calls recover() in the same process, so that nothing is stored between two requests and the scenario is the same after
 * every nightly reset.
 */
final class FictitiousPlatformOutage
{
    private bool $over = false;

    public function recover(): void
    {
        $this->over = true;
    }

    public function isOver(): bool
    {
        return $this->over;
    }
}
