<?php

declare(strict_types=1);

namespace App\Demo\Proof\Check;

/**
 * Compares the figures of proof.json the demo can check without the network (US-168): its own test files and the list
 * of plugins of the catalogue. The plugin facts (versions, CI) stay with bin/proof-refresh, which reads the public sources.
 */
final class ProofFreshness
{
    /**
     * @param array<array-key, mixed> $proof the decoded proof.json
     * @param list<string>            $slugs the plugins of config/demo/plugins.yaml
     *
     * @return list<ProofGap>
     */
    public static function gaps(array $proof, int $tests, array $slugs): array
    {
        return array_values(array_filter([self::testGap($proof, $tests), self::pluginGap($proof, $slugs)]));
    }

    /** @param array<array-key, mixed> $proof */
    private static function testGap(array $proof, int $tests): ?ProofGap
    {
        $delivery = \is_array($proof['delivery'] ?? null) ? $proof['delivery'] : [];
        $fact = \is_array($delivery['tests'] ?? null) ? $delivery['tests'] : [];
        $published = \is_int($fact['value'] ?? null) ? $fact['value'] : null;

        return $published === $tests ? null : new ProofGap('test files', $published, $tests);
    }

    /**
     * @param array<array-key, mixed> $proof
     * @param list<string>            $slugs
     */
    private static function pluginGap(array $proof, array $slugs): ?ProofGap
    {
        $published = array_map('strval', array_keys(\is_array($proof['plugins'] ?? null) ? $proof['plugins'] : []));
        $missing = array_values(array_diff($slugs, $published));
        $removed = array_values(array_diff($published, $slugs));
        if ([] === $missing && [] === $removed) {
            return null;
        }
        $detail = array_filter([
            [] === $missing ? '' : 'missing from the proof: ' . implode(', ', $missing),
            [] === $removed ? '' : 'no longer in the catalogue: ' . implode(', ', $removed),
        ]);

        return new ProofGap('plugins', \count($published), \count($slugs), implode('; ', $detail));
    }
}
