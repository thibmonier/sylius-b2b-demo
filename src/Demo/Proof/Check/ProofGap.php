<?php

declare(strict_types=1);

namespace App\Demo\Proof\Check;

/** A figure of proof.json that differs from the repository (US-168). */
final readonly class ProofGap
{
    public function __construct(
        public string $figure,
        public ?int $published,
        public int $actual,
        public string $detail = '',
    ) {
    }

    public function difference(): string
    {
        if (null === $this->published) {
            return 'not published';
        }

        return \sprintf('%+d', $this->actual - $this->published);
    }

    public function describe(): string
    {
        $line = \sprintf('%s: published %s, actual %d (difference %s)', $this->figure, $this->published ?? 'nothing', $this->actual, $this->difference());

        return '' === $this->detail ? $line : $line . '; ' . $this->detail;
    }
}
