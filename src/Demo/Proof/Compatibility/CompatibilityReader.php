<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility;

use App\Demo\Proof\ProofReader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reads config/demo/compatibility.json (US-171), written by bin/compat-refresh. A section without a public source or a
 * date is dropped; a missing or broken file is an empty report, never an error. No network call, ever.
 */
final class CompatibilityReader
{
    private ?CompatibilityReport $report = null;

    public function __construct(
        #[Autowire(env: 'DEMO_COMPATIBILITY_FILE')]
        private readonly string $file,
    ) {
    }

    public function report(): CompatibilityReport
    {
        return $this->report ??= $this->load();
    }

    private function load(): CompatibilityReport
    {
        $content = is_file($this->file) ? file_get_contents($this->file) : false;
        if (false === $content) {
            return new CompatibilityReport(null);
        }

        try {
            $data = json_decode($content, true, 16, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new CompatibilityReport(null);
        }
        if (!\is_array($data) || array_is_list($data)) {
            return new CompatibilityReport(null);
        }
        $plugins = [];
        foreach (\is_array($data['plugins'] ?? null) ? $data['plugins'] : [] as $slug => $sections) {
            if (\is_array($sections)) {
                $plugins[(string) $slug] = self::plugin($sections);
            }
        }

        return new CompatibilityReport(\is_string($data['generated_at'] ?? null) ? $data['generated_at'] : null, self::demo($data['demo'] ?? null), $plugins);
    }

    private static function demo(mixed $raw): ?DemoEnvironment
    {
        $section = self::section($raw);
        $sylius = self::text($section['values']['sylius'] ?? null);
        $symfony = self::text($section['values']['symfony'] ?? null);
        if (null === $section || null === $sylius || null === $symfony) {
            return null;
        }

        return new DemoEnvironment($sylius, $symfony, self::text($section['values']['php'] ?? null), $section['source'], $section['checked_at'], $section['stale']);
    }

    /** @param array<array-key, mixed> $sections */
    private static function plugin(array $sections): PluginCompatibility
    {
        $dependencies = self::section($sections['dependencies'] ?? null);
        $ci = self::section($sections['ci'] ?? null);
        $demo = self::section($sections['demo'] ?? null);
        $version = self::text($demo['values']['version'] ?? null);
        $demo = null === $version ? null : $demo;

        return new PluginCompatibility(
            known: true,
            requires: self::strings($dependencies['values']['requires'] ?? null),
            suggests: self::strings($dependencies['values']['suggests'] ?? null),
            dependenciesSource: $dependencies['source'] ?? null,
            dependenciesCheckedAt: $dependencies['checked_at'] ?? null,
            dependenciesStale: $dependencies['stale'] ?? false,
            ci: null === $ci ? null : self::axes($ci['values']),
            ciSource: $ci['source'] ?? null,
            ciCheckedAt: $ci['checked_at'] ?? null,
            ciStale: $ci['stale'] ?? false,
            demoVersion: $version,
            demoSource: $demo['source'] ?? null,
            demoCheckedAt: $demo['checked_at'] ?? null,
        );
    }

    /**
     * @param array<array-key, mixed> $ci
     *
     * @return array{php: list<string>, sylius: list<string>, symfony: list<string>, database: list<string>}
     */
    private static function axes(array $ci): array
    {
        return [
            'php' => self::strings($ci['php'] ?? null),
            'sylius' => self::strings($ci['sylius'] ?? null),
            'symfony' => self::strings($ci['symfony'] ?? null),
            'database' => self::strings($ci['database'] ?? null),
        ];
    }

    /**
     * A section is kept only with a public source and a check date.
     *
     * @return array{source: string, checked_at: string, stale: bool, values: array<array-key, mixed>}|null
     */
    private static function section(mixed $raw): ?array
    {
        if (!\is_array($raw)) {
            return null;
        }
        $source = $raw['source'] ?? null;
        $checkedAt = $raw['checked_at'] ?? null;
        if (!\is_string($source) || !ProofReader::isPublicSource($source) || !\is_string($checkedAt) || 1 !== preg_match('/^\d{4}-\d{2}-\d{2}/', $checkedAt)) {
            return null;
        }

        return ['source' => $source, 'checked_at' => $checkedAt, 'stale' => true === ($raw['stale'] ?? false), 'values' => $raw];
    }

    private static function text(mixed $value): ?string
    {
        return \is_string($value) ? $value : null;
    }

    /** @return list<string> */
    private static function strings(mixed $values): array
    {
        return array_values(array_filter(\is_array($values) ? $values : [], \is_string(...)));
    }
}
