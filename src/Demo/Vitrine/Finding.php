<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * One problem found in a plugin README. A line of 0 means the whole file.
 */
final readonly class Finding implements \Stringable
{
    public function __construct(
        public string $file,
        public int $line,
        public string $severity,
        public string $message,
    ) {
    }

    public function __toString(): string
    {
        return \sprintf('%s%s: %s', $this->file, $this->line > 0 ? ':' . $this->line : '', $this->message);
    }
}
