<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** A plugin the pack suggests and does not install (the "suggest" of its composer.json) with the reason of its README. */
final readonly class PackSuggestion
{
    public function __construct(public string $slug, private string $reasonEn, private string $reasonFr)
    {
    }

    public function reason(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->reasonFr : $this->reasonEn;
    }
}
