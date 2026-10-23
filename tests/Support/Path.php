<?php

declare(strict_types=1);

namespace App\Tests\Support;

/** Reads and rewrites a nested array by a dotted path ("plugins.slug.ci.php"), so tests never index into `mixed`. */
final class Path
{
    /** @param array<array-key, mixed> $data */
    public static function get(array $data, string $path): mixed
    {
        $current = $data;
        foreach (explode('.', $path) as $key) {
            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<string> the keys of the array found at the path
     */
    public static function keys(array $data, string $path): array
    {
        $found = self::get($data, $path);

        return \is_array($found) ? array_map('strval', array_keys($found)) : [];
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed> a copy where the path holds the value
     */
    public static function with(array $data, string $path, mixed $value): array
    {
        $parts = explode('.', $path, 2);
        $head = $parts[0];
        $rest = $parts[1] ?? null;
        if (null === $rest) {
            $data[$head] = $value;

            return $data;
        }
        $child = $data[$head] ?? [];
        $data[$head] = self::with(\is_array($child) ? $child : [], $rest, $value);

        return $data;
    }
}
