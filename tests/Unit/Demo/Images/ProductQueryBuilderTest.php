<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\ProductQueryBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductQueryBuilderTest extends TestCase
{
    #[DataProvider('products')]
    public function testTheSearchTermsComeFromTheProductNotFromItsBrandOrDimensions(string $name, string $taxon, string $expected, string $fallback): void
    {
        self::assertSame([$expected, $fallback], (new ProductQueryBuilder())(['sku' => 'X', 'name_en' => $name, 'taxon' => $taxon]));
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function products(): iterable
    {
        yield 'hard hat' => ['MSA V-Gard Hard Hat White', 'protection-tete', 'hard hat', 'safety helmet'];
        yield 'welding gloves' => ['Stokker Tigerflex TIG Welding Split Leather Gloves', 'protection-mains', 'welding gloves', 'work gloves'];
        yield 'hex bolt' => ['Hex bolt M6x20 stainless A2', 'visserie', 'hex bolt', 'screws and bolts'];
        yield 'unknown name falls on the category' => ['Something odd', 'outillage', 'hand tools', 'hand tools'];
    }
}
