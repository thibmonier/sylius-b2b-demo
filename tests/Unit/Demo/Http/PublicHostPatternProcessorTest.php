<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Http\PublicHostPatternProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicHostPatternProcessorTest extends TestCase
{
    public function testAConfiguredHostBecomesAnAnchoredEscapedPattern(): void
    {
        $pattern = $this->pattern('Sylius-B2B.Demo.example.test');

        self::assertSame(1, preg_match('{' . $pattern . '}i', 'sylius-b2b.demo.example.test'));
        self::assertSame(0, preg_match('{' . $pattern . '}i', 'sylius-b2bXdemo.example.test'), 'Dots are escaped.');
        self::assertSame(0, preg_match('{' . $pattern . '}i', 'evil.sylius-b2b.demo.example.test'));
    }

    #[DataProvider('unusableValues')]
    public function testNoValidHostMatchesNothing(string $value): void
    {
        $pattern = $this->pattern($value);

        self::assertSame(0, preg_match('{' . $pattern . '}i', 'sylius-b2b.demo.example.test'));
        self::assertSame(0, preg_match('{' . $pattern . '}i', ''));
        self::assertSame(0, preg_match('{' . $pattern . '}i', 'anything.test'));
    }

    /** @return iterable<string, array{string}> */
    public static function unusableValues(): iterable
    {
        yield 'empty' => [''];
        yield 'blank' => ['   '];
        yield 'regex' => ['^.*$'];
        yield 'with a scheme' => ['https://demo.example.test'];
        yield 'with a path' => ['demo.example.test/x'];
    }

    private function pattern(string $value): string
    {
        $processor = new PublicHostPatternProcessor();

        return $processor->getEnv('demo_host_pattern', 'DEMO_PUBLIC_HOST', static fn (): string => $value);
    }
}
