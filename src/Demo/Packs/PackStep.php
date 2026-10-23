<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** What is left to do by hand for one plugin of a pack, after the "composer require" (README table). */
final readonly class PackStep
{
    /**
     * @param list<string> $bundles bundle classes to register
     * @param list<string> $config  configuration files to import
     * @param list<string> $routes  route files to import
     */
    public function __construct(
        public string $slug,
        public array $bundles,
        public array $config,
        public array $routes,
        public bool $migrations,
        private string $otherEn,
        private string $otherFr,
    ) {
    }

    public function other(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->otherFr : $this->otherEn;
    }
}
