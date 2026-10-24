<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/**
 * The packs data against the repositories (US-178, same mechanism as the proof of US-168 and the matrix of US-171): the
 * packs the decision names and, when clones are at hand, the composer.json and READMEs they hold. No network, nothing written.
 */
final class PacksFreshness
{
    /**
     * @param array<string, mixed>  $data               the decoded packs file
     * @param list<string>          $repositories       the packs expected
     * @param array<string, string> $localFingerprints  fingerprint of each clone that is at hand, by repository
     *
     * @return list<PacksGap>
     */
    public static function gaps(array $data, array $repositories, array $localFingerprints = []): array
    {
        $packs = \is_array($data['packs'] ?? null) ? $data['packs'] : [];

        return array_values(array_filter([self::listGap(array_map('strval', array_keys($packs)), $repositories), self::sourceGap($packs, $repositories, $localFingerprints)]));
    }

    /**
     * @param list<string> $published
     * @param list<string> $expected
     */
    private static function listGap(array $published, array $expected): ?PacksGap
    {
        $missing = array_values(array_diff($expected, $published));
        $removed = array_values(array_diff($published, $expected));
        if ([] === $missing && [] === $removed) {
            return null;
        }
        $detail = array_filter([
            [] === $missing ? '' : 'missing from packs.json: ' . implode(', ', $missing),
            [] === $removed ? '' : 'no longer expected: ' . implode(', ', $removed),
        ]);

        return new PacksGap('packs', \sprintf('published %d, expected %d (difference %+d); %s', \count($published), \count($expected), \count($expected) - \count($published), implode('; ', $detail)));
    }

    /**
     * @param array<array-key, mixed> $packs
     * @param list<string>            $repositories
     * @param array<string, string>   $local
     */
    private static function sourceGap(array $packs, array $repositories, array $local): ?PacksGap
    {
        $changed = [];
        foreach ($repositories as $repository) {
            $entry = $packs[$repository] ?? null;
            $published = \is_array($entry) && \is_string($entry['fingerprint'] ?? null) ? $entry['fingerprint'] : null;
            if (null !== $published && isset($local[$repository]) && $local[$repository] !== $published) {
                $changed[] = $repository;
            }
        }

        return [] === $changed ? null : new PacksGap('packs sources', 'changed since the last generation: ' . implode(', ', $changed));
    }
}
