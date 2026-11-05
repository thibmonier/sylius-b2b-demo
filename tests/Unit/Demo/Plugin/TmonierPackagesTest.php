<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Plugin;

use App\Demo\Plugin\TmonierPackages;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TmonierPackagesTest extends TestCase
{
    #[DataProvider('packages')]
    public function testOnlyTheTmonierPluginsAreShowcasedPlugins(string $package, bool $expected): void
    {
        self::assertSame($expected, TmonierPackages::isPlugin($package));
    }

    /** @return iterable<string, array{string, bool}> */
    public static function packages(): iterable
    {
        yield 'plugin' => ['tmonier/sylius-quick-order-plugin', true];
        yield 'theme' => ['tmonier/sylius-industry-trade-theme', false];
        yield 'other vendor' => ['sylius/sylius', false];
        yield 'prefix lookalike' => ['tmonier-fork/sylius-x-plugin', false];
    }

    public function testTheSlugDropsTheVendor(): void
    {
        self::assertSame('sylius-quick-order-plugin', TmonierPackages::slug('tmonier/sylius-quick-order-plugin'));
    }
}
