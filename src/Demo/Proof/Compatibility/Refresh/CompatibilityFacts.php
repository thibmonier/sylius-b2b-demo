<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

/** What was read for one plugin: the facts that could be verified and the sources that failed. */
final readonly class CompatibilityFacts
{
    /**
     * @param array<string, mixed> $facts    dependencies, ci, fingerprint
     * @param list<string>         $failures
     */
    public function __construct(public array $facts, public array $failures)
    {
    }
}
