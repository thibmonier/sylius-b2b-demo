<?php

declare(strict_types=1);

namespace App\Demo\Proof;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reads the versioned proof file (config/demo/proof.json, written by bin/proof-refresh, never by the page). The page
 * makes no network call: when the file is missing or broken the report is simply empty, so there is no error 500.
 * A fact is kept only if it has a value, a public source (https, packagist.org or github.com, no credentials, no query)
 * and a date; anything else is dropped.
 */
final class ProofReader
{
    private const HOSTS = ['packagist.org', 'github.com'];

    private const STATES = ['unpublished'];

    private ?ProofReport $report = null;

    public function __construct(
        #[Autowire(env: 'DEMO_PROOF_FILE')]
        private readonly string $file,
    ) {
    }

    public function report(): ProofReport
    {
        return $this->report ??= $this->load();
    }

    /** The only way a source reaches the page: used by the refresh command too, so what is written is what is shown. */
    public static function isPublicSource(mixed $source): bool
    {
        if (!\is_string($source) || 1 === preg_match('/[\x00-\x20\x7f]/', $source)) {
            return false;
        }
        $parts = parse_url($source);

        return false !== $parts &&
            'https' === ($parts['scheme'] ?? null) &&
            \in_array($parts['host'] ?? '', self::HOSTS, true) &&
            [] === array_intersect_key($parts, array_flip(['user', 'pass', 'query', 'fragment', 'port']));
    }

    private function load(): ProofReport
    {
        $content = is_file($this->file) ? file_get_contents($this->file) : false;
        if (false === $content) {
            return new ProofReport(null);
        }

        try {
            $data = json_decode($content, true, 16, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new ProofReport(null);
        }
        if (!\is_array($data) || array_is_list($data)) {
            return new ProofReport(null);
        }

        return new ProofReport(
            \is_string($data['generated_at'] ?? null) ? $data['generated_at'] : null,
            self::plugins($data['plugins'] ?? null),
            self::facts($data['delivery'] ?? null),
        );
    }

    /** @return array<string, array<string, ProofFact>> */
    private static function plugins(mixed $plugins): array
    {
        if (!\is_array($plugins)) {
            return [];
        }
        $result = [];
        foreach ($plugins as $slug => $facts) {
            $result[(string) $slug] = self::facts($facts);
        }

        return $result;
    }

    /** @return array<string, ProofFact> */
    private static function facts(mixed $facts): array
    {
        if (!\is_array($facts)) {
            return [];
        }
        $result = [];
        foreach ($facts as $key => $raw) {
            $fact = \is_array($raw) ? self::fact($raw) : null;
            if (null !== $fact) {
                $result[(string) $key] = $fact;
            }
        }

        return $result;
    }

    /** @param array<array-key, mixed> $raw */
    private static function fact(array $raw): ?ProofFact
    {
        $source = $raw['source'] ?? null;
        $checkedAt = $raw['checked_at'] ?? null;
        if (!self::isPublicSource($source) || !\is_string($checkedAt) || 1 !== preg_match('/^\d{4}-\d{2}-\d{2}/', $checkedAt)) {
            return null;
        }
        \assert(\is_string($source));
        if (\in_array($raw['state'] ?? null, self::STATES, true)) {
            return ProofFact::unpublished($source, $checkedAt);
        }
        $value = $raw['value'] ?? null;
        if (!\is_string($value) && !\is_int($value) && !\is_bool($value)) {
            return null;
        }

        return ProofFact::verified($value, $source, $checkedAt, true === ($raw['stale'] ?? false));
    }
}
