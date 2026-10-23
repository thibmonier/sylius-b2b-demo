<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility;

/** What the compatibility file says: the stack of the demo and, per plugin, links, tested versions and demo version. */
final readonly class CompatibilityReport
{
    /** @param array<string, PluginCompatibility> $plugins */
    public function __construct(
        private ?string $generatedAt,
        private ?DemoEnvironment $demo = null,
        private array $plugins = [],
    ) {
    }

    public function generatedAt(): ?string
    {
        return $this->generatedAt;
    }

    public function demo(): ?DemoEnvironment
    {
        return $this->demo;
    }

    /** @return list<string> the slugs the file holds */
    public function slugs(): array
    {
        return array_map('strval', array_keys($this->plugins));
    }

    public function plugin(string $slug): PluginCompatibility
    {
        return $this->plugins[$slug] ?? new PluginCompatibility();
    }
}
