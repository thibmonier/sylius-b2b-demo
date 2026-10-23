<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Checked reads of decoded YAML and JSON (US-174): a test fails with the path it expected instead of indexing into
 * `mixed`. Keys are passed one by one because configuration keys hold dots ("demo.password").
 */
final class Typed
{
    public static function at(mixed $data, string|int ...$keys): mixed
    {
        $path = [];
        foreach ($keys as $key) {
            $path[] = (string) $key;
            if (!\is_array($data) || !\array_key_exists($key, $data)) {
                throw new \UnexpectedValueException('Missing ' . implode(' > ', $path));
            }
            $data = $data[$key];
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public static function map(mixed $value, string $what): array
    {
        if (!\is_array($value) || ([] !== $value && array_is_list($value))) {
            throw new \UnexpectedValueException($what . ' is not a map');
        }
        $map = [];
        foreach ($value as $key => $item) {
            $map[(string) $key] = $item;
        }

        return $map;
    }

    /** @return list<array<string, mixed>> */
    public static function listOfMaps(mixed $value, string $what): array
    {
        if (!\is_array($value) || !array_is_list($value)) {
            throw new \UnexpectedValueException($what . ' is not a list');
        }

        return array_map(static fn (mixed $item): array => self::map($item, $what . ' entry'), $value);
    }

    public static function string(mixed $value, string $what): string
    {
        if (!\is_string($value)) {
            throw new \UnexpectedValueException($what . ' is not a string');
        }

        return $value;
    }
}
