<?php

declare(strict_types=1);

namespace App\Demo\Packs;

use App\Demo\Proof\ProofReader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reads config/demo/packs.json (US-178), written by bin/packs-refresh. A pack without a public source, a check date, a
 * title or a plugin is dropped; a missing or broken file is an empty report, never an error. No network call, ever.
 */
final class PacksReader
{
    private ?PacksReport $report = null;

    public function __construct(
        #[Autowire(env: 'DEMO_PACKS_FILE')]
        private readonly string $file,
    ) {
    }

    public function report(): PacksReport
    {
        return $this->report ??= $this->load();
    }

    private function load(): PacksReport
    {
        $content = is_file($this->file) ? file_get_contents($this->file) : false;
        if (false === $content) {
            return new PacksReport(null);
        }

        try {
            $data = json_decode($content, true, 16, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new PacksReport(null);
        }
        if (!\is_array($data) || array_is_list($data)) {
            return new PacksReport(null);
        }
        $packs = [];
        foreach (\is_array($data['packs'] ?? null) ? $data['packs'] : [] as $repository => $entry) {
            $pack = \is_array($entry) ? self::pack((string) $repository, $entry) : null;
            if (null !== $pack) {
                $packs[] = $pack;
            }
        }

        return new PacksReport(\is_string($data['generated_at'] ?? null) ? $data['generated_at'] : null, $packs);
    }

    /** @param array<array-key, mixed> $entry */
    private static function pack(string $repository, array $entry): ?Pack
    {
        $composer = self::text($entry['composer'] ?? null);
        $command = self::text($entry['command'] ?? null);
        $checkedAt = self::text($entry['checked_at'] ?? null);
        $source = $entry['source'] ?? null;
        $composerSource = $entry['composer_source'] ?? null;
        $title = self::pair($entry['title'] ?? null);
        $plugins = self::plugins($entry['plugins'] ?? null);
        if (null === $composer || null === $command || null === $title || [] === $plugins || !\is_string($source) || !\is_string($composerSource)) {
            return null;
        }
        if (!ProofReader::isPublicSource($source) || !ProofReader::isPublicSource($composerSource) || null === $checkedAt || 1 !== preg_match('/^\d{4}-\d{2}-\d{2}/', $checkedAt)) {
            return null;
        }
        $summary = self::pair($entry['summary'] ?? null) ?? ['en' => '', 'fr' => ''];

        return new Pack(
            $repository,
            $composer,
            $command,
            $title['en'],
            $title['fr'],
            $summary['en'],
            $summary['fr'],
            $plugins,
            self::suggestions($entry['suggests'] ?? null),
            self::steps($entry['steps'] ?? null),
            $source,
            $composerSource,
            $checkedAt,
            true === ($entry['stale'] ?? false),
        );
    }

    /** @return list<PackPlugin> */
    private static function plugins(mixed $raw): array
    {
        $plugins = [];
        foreach (\is_array($raw) ? $raw : [] as $item) {
            $slug = \is_array($item) ? self::text($item['slug'] ?? null) : null;
            $bound = \is_array($item) ? self::text($item['bound'] ?? null) : null;
            $role = \is_array($item) ? self::pair($item['role'] ?? null) : null;
            if (null !== $slug && null !== $bound && null !== $role && 1 === preg_match('/^sylius-[\w-]+-plugin$/', $slug)) {
                $plugins[] = new PackPlugin($slug, $bound, $role['en'], $role['fr']);
            }
        }

        return $plugins;
    }

    /** @return list<PackSuggestion> */
    private static function suggestions(mixed $raw): array
    {
        $suggestions = [];
        foreach (\is_array($raw) ? $raw : [] as $item) {
            $slug = \is_array($item) ? self::text($item['slug'] ?? null) : null;
            if (null !== $slug && 1 === preg_match('/^sylius-[\w-]+-plugin$/', $slug)) {
                $reason = self::pair($item['reason'] ?? null) ?? ['en' => '', 'fr' => ''];
                $suggestions[] = new PackSuggestion($slug, $reason['en'], $reason['fr']);
            }
        }

        return $suggestions;
    }

    /** @return list<PackStep> */
    private static function steps(mixed $raw): array
    {
        $steps = [];
        foreach (\is_array($raw) ? $raw : [] as $item) {
            $slug = \is_array($item) ? self::text($item['slug'] ?? null) : null;
            if (null === $slug || 1 !== preg_match('/^sylius-[\w-]+-plugin$/', $slug)) {
                continue;
            }
            $other = self::pair($item['other'] ?? null) ?? ['en' => '', 'fr' => ''];
            $steps[] = new PackStep($slug, self::strings($item['bundles'] ?? null), self::strings($item['config'] ?? null), self::strings($item['routes'] ?? null), true === ($item['migrations'] ?? false), $other['en'], $other['fr']);
        }

        return $steps;
    }

    /** @return array{en: string, fr: string}|null */
    private static function pair(mixed $raw): ?array
    {
        $en = \is_array($raw) ? self::text($raw['en'] ?? null) : null;
        $fr = \is_array($raw) ? self::text($raw['fr'] ?? null) : null;

        return null === $en || null === $fr ? null : ['en' => $en, 'fr' => $fr];
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
