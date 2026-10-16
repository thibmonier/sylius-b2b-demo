<?php

declare(strict_types=1);

namespace App\Demo\Plugin;

/** One showcased plugin of the demo (US-014). */
final readonly class DemoPlugin
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $category,
        public string $descriptionFr,
        public string $descriptionEn,
        public ?string $demoRoute,
    ) {
    }

    /** Short description in the given locale ("fr_FR", "fr"...); English for any other language. */
    public function description(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->descriptionFr : $this->descriptionEn;
    }

    public function githubUrl(): string
    {
        return 'https://github.com/thibmonier/' . $this->slug;
    }

    public function composerName(): string
    {
        return 'tmonier/' . $this->slug;
    }
}
