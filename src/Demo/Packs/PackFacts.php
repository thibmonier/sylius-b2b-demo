<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** What was read for one pack: the facts that could be verified and the sources that failed. */
final readonly class PackFacts
{
    /**
     * @param array<string, mixed> $facts    the entry of config/demo/packs.json
     * @param list<string>         $failures
     */
    public function __construct(public array $facts, public array $failures)
    {
    }
}
