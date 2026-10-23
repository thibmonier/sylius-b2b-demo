<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Narrows a shared data provider to the arguments a test method takes (US-174): PHPUnit 13 warns when a data set has
 * more arguments than the method accepts, so a method that needs only the locale gets a provider of one argument.
 */
final class DataSets
{
    /**
     * @param iterable<array-key, array<array-key, mixed>> $sets
     *
     * @return iterable<array-key, list<mixed>>
     */
    public static function firstArguments(iterable $sets, int $count): iterable
    {
        foreach ($sets as $name => $arguments) {
            yield $name => \array_slice(array_values($arguments), 0, $count);
        }
    }
}
