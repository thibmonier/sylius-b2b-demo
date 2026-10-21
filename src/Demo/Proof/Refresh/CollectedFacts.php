<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

/** The facts written to the proof file for one subject, and the sources that failed while collecting them. */
final readonly class CollectedFacts
{
    /**
     * @param array<string, array<string, mixed>> $facts
     * @param list<string>                        $failures
     */
    public function __construct(public array $facts, public array $failures)
    {
    }
}
