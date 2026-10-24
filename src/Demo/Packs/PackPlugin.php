<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** A plugin of a pack: its slug, its version bound (composer.json) and its role (README, in both languages). */
final readonly class PackPlugin
{
    public function __construct(public string $slug, public string $bound, private string $roleEn, private string $roleFr)
    {
    }

    public function role(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->roleFr : $this->roleEn;
    }
}
