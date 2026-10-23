<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

/**
 * The compatibility data against the repository (US-171, same mechanism as the proof of US-168): the plugins of the
 * catalogue and, when clones are at hand, the composer.json and CI matrix they hold. No network, nothing written.
 */
final class CompatibilityFreshness
{
    /**
     * @param array<string, mixed>  $data               the decoded compatibility file
     * @param list<string>          $slugs              the plugins of the catalogue
     * @param array<string, string> $localFingerprints  fingerprint of each clone that is at hand, by slug
     *
     * @return list<CompatibilityGap>
     */
    public static function gaps(array $data, array $slugs, array $localFingerprints = []): array
    {
        $plugins = \is_array($data['plugins'] ?? null) ? $data['plugins'] : [];

        return array_values(array_filter([self::pluginGap(array_map('strval', array_keys($plugins)), $slugs), self::sourceGap($plugins, $slugs, $localFingerprints)]));
    }

    /**
     * @param list<string> $published
     * @param list<string> $slugs
     */
    private static function pluginGap(array $published, array $slugs): ?CompatibilityGap
    {
        $missing = array_values(array_diff($slugs, $published));
        $removed = array_values(array_diff($published, $slugs));
        if ([] === $missing && [] === $removed) {
            return null;
        }
        $detail = array_filter([
            [] === $missing ? '' : 'missing from the compatibility data: ' . implode(', ', $missing),
            [] === $removed ? '' : 'no longer in the catalogue: ' . implode(', ', $removed),
        ]);

        return new CompatibilityGap('compatibility plugins', \sprintf('published %d, actual %d (difference %+d); %s', \count($published), \count($slugs), \count($slugs) - \count($published), implode('; ', $detail)));
    }

    /**
     * @param array<array-key, mixed> $plugins
     * @param list<string>            $slugs
     * @param array<string, string>   $local
     */
    private static function sourceGap(array $plugins, array $slugs, array $local): ?CompatibilityGap
    {
        $changed = [];
        foreach ($slugs as $slug) {
            $entry = $plugins[$slug] ?? null;
            $published = \is_array($entry) && \is_string($entry['fingerprint'] ?? null) ? $entry['fingerprint'] : null;
            if (null !== $published && isset($local[$slug]) && $local[$slug] !== $published) {
                $changed[] = $slug;
            }
        }

        return [] === $changed ? null : new CompatibilityGap('compatibility sources', 'changed since the last generation: ' . implode(', ', $changed));
    }
}
