<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility;

/** What the data file says about one plugin (US-171); a section the file does not hold, or holds without proof, is empty. */
final readonly class PluginCompatibility
{
    /**
     * @param list<string>                                                                                      $requires
     * @param list<string>                                                                                      $suggests
     * @param array{php: list<string>, sylius: list<string>, symfony: list<string>, database: list<string>}|null $ci
     */
    public function __construct(
        public bool $known = false,
        public array $requires = [],
        public array $suggests = [],
        public ?string $dependenciesSource = null,
        public ?string $dependenciesCheckedAt = null,
        public bool $dependenciesStale = false,
        public ?array $ci = null,
        public ?string $ciSource = null,
        public ?string $ciCheckedAt = null,
        public bool $ciStale = false,
        public ?string $demoVersion = null,
        public ?string $demoSource = null,
        public ?string $demoCheckedAt = null,
    ) {
    }

    public function hasDependencies(): bool
    {
        return null !== $this->dependenciesSource;
    }

    public function hasCi(): bool
    {
        return null !== $this->ci;
    }

    public function inDemo(): bool
    {
        return null !== $this->demoVersion;
    }
}
