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
        public string $scenarioFr,
        public string $scenarioEn,
        public string $advisedAccount,
        public bool $published = true,
        public bool $installed = true,
        public bool $listed = true,
    ) {
    }

    /** Short description in the given locale ("fr_FR", "fr"...); English for any other language. */
    public function description(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->descriptionFr : $this->descriptionEn;
    }

    /** Demo scenario in the given locale; English for any other language than French. */
    public function scenario(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->scenarioFr : $this->scenarioEn;
    }

    /** Packagist page, or null while the package is not published (no broken link is ever shown). */
    /** Publication status shown on the card: null once published, 'in_progress' (installed here) or 'upcoming' (not installed yet). */
    public function status(): ?string
    {
        return match (true) {
            $this->published => null,
            $this->installed => 'in_progress',
            default => 'upcoming',
        };
    }

    public function packagistUrl(): ?string
    {
        return $this->published ? 'https://packagist.org/packages/' . $this->composerName() : null;
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
