<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Tests\Support\DataSets;
use PHPUnit\Framework\TestCase;

/** US-174: a shared provider narrowed to the first arguments keeps the names of its data sets. */
final class DataSetsTest extends TestCase
{
    public function testItKeepsTheNamesAndTheFirstArguments(): void
    {
        $sets = ['French' => ['fr_FR', 'Bonjour', 3], 'English' => ['en_US', 'Hello', 4]];

        self::assertSame(['French' => ['fr_FR'], 'English' => ['en_US']], iterator_to_array(DataSets::firstArguments($sets, 1)));
        self::assertSame(['French' => ['fr_FR', 'Bonjour']], iterator_to_array(DataSets::firstArguments(['French' => $sets['French']], 2)));
    }
}
