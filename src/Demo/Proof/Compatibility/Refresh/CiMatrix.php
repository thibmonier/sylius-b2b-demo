<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * The versions a CI really runs (US-171): the values of the combinations of the build matrix, once "exclude" has removed
 * some and "include" has added others. A value that only excluded combinations use is not reported.
 */
final class CiMatrix
{
    private const array AXES = ['php', 'sylius', 'symfony', 'database'];

    /** @return array{php: list<string>, sylius: list<string>, symfony: list<string>, database: list<string>}|null null when the workflow has no matrix or cannot be read */
    public static function axes(string $workflow): ?array
    {
        try {
            $data = Yaml::parse($workflow);
        } catch (ParseException) {
            return null;
        }
        $jobs = \is_array($data) && \is_array($data['jobs'] ?? null) ? $data['jobs'] : [];
        $found = false;
        $values = array_fill_keys(self::AXES, []);
        foreach ($jobs as $job) {
            $strategy = \is_array($job) && \is_array($job['strategy'] ?? null) ? $job['strategy'] : [];
            if (!\is_array($strategy['matrix'] ?? null)) {
                continue;
            }
            $found = true;
            foreach (self::combinations($strategy['matrix']) as $combination) {
                foreach (self::AXES as $axis) {
                    if (isset($combination[$axis])) {
                        $values[$axis][self::normalize($axis, $combination[$axis])] = true;
                    }
                }
            }
        }

        return $found ? self::sorted($values) : null;
    }

    /**
     * @param array<array-key, mixed> $matrix
     *
     * @return list<array<string, string>>
     */
    private static function combinations(array $matrix): array
    {
        $combinations = [[]];
        $axes = [];
        foreach ($matrix as $key => $values) {
            if (!\in_array($key, ['include', 'exclude'], true)) {
                $axes[(string) $key] = self::scalars(\is_array($values) ? $values : [$values]);
            }
        }
        foreach ($axes as $key => $values) {
            $next = [];
            foreach ($combinations as $combination) {
                foreach ($values as $value) {
                    $next[] = $combination + [$key => $value];
                }
            }
            $combinations = $next;
        }
        $combinations = [] === $axes ? [] : $combinations;
        $excluded = self::entries($matrix['exclude'] ?? null);
        $combinations = array_values(array_filter($combinations, static function (array $combination) use ($excluded): bool {
            foreach ($excluded as $exclusion) {
                if ([] !== $exclusion && array_intersect_assoc($exclusion, $combination) === $exclusion) {
                    return false;
                }
            }

            return true;
        }));

        return [...$combinations, ...self::entries($matrix['include'] ?? null)];
    }

    /** @return list<array<string, string>> */
    private static function entries(mixed $entries): array
    {
        $result = [];
        foreach (\is_array($entries) ? $entries : [] as $entry) {
            $result[] = self::scalarMap(\is_array($entry) ? $entry : []);
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<string, string>
     */
    private static function scalarMap(array $values): array
    {
        $map = [];
        foreach ($values as $key => $value) {
            if (\is_scalar($value) && !str_contains((string) $value, '${{')) {
                $map[(string) $key] = (string) $value;
            }
        }

        return $map;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return list<string>
     */
    private static function scalars(array $values): array
    {
        return array_values(self::scalarMap($values));
    }

    private static function normalize(string $axis, string $value): string
    {
        if ('database' === $axis) {
            return $value;
        }

        return 1 === preg_match('/\d+(?:\.\d+)?/', $value, $matches) ? $matches[0] : $value;
    }

    /**
     * @param array<string, array<string, true>> $values
     *
     * @return array{php: list<string>, sylius: list<string>, symfony: list<string>, database: list<string>}
     */
    private static function sorted(array $values): array
    {
        return [
            'php' => self::versions($values['php']),
            'sylius' => self::versions($values['sylius']),
            'symfony' => self::versions($values['symfony']),
            'database' => self::names($values['database']),
        ];
    }

    /**
     * @param array<string, true> $values
     *
     * @return list<string>
     */
    private static function versions(array $values): array
    {
        $list = array_map('strval', array_keys($values));
        usort($list, static fn (string $a, string $b): int => version_compare($a, $b));

        return $list;
    }

    /**
     * @param array<string, true> $values
     *
     * @return list<string>
     */
    private static function names(array $values): array
    {
        $list = array_map('strval', array_keys($values));
        sort($list);

        return $list;
    }
}
