<?php

declare(strict_types=1);

namespace App\Demo\OverrideGuide;

/** One real example of the override guide: a file of the demo, the lines to show, and a command that checks it. */
final readonly class OverrideExample
{
    /** @param list<string> $related other files of the demo that take part in the example */
    public function __construct(
        public string $case,
        public string $path,
        public int $firstLine,
        public int $lastLine,
        public string $command,
        public array $related = [],
    ) {
    }
}
