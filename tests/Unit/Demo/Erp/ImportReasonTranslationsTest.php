<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Tests\Support\Typed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-187 (B): every reason code of the report of the import has a sentence in French and in English on the page of the exchanges. */
final class ImportReasonTranslationsTest extends TestCase
{
    private const REASONS = ['columns', 'duplicate', 'unknown_variant', 'channel', 'currency', 'price', 'on_hand', 'below_on_hold', 'empty', 'encoding', 'truncated', 'header'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr'];
        yield 'English' => ['en'];
    }

    #[DataProvider('locales')]
    public function testEveryReasonOfThePluginIsExplained(string $locale): void
    {
        $messages = Yaml::parseFile(__DIR__ . '/../../../../translations/demo_erp.' . $locale . '.yaml');

        foreach (self::REASONS as $reason) {
            $text = Typed::at($messages, 'erp', 'reasons', $reason);
            self::assertIsString($text);
            self::assertNotSame('', trim($text));
            self::assertDoesNotMatchRegularExpression('/^' . $reason . '$/', trim($text), 'a sentence, not the code');
        }
    }

    #[DataProvider('locales')]
    public function testTheKindsAndTheLineLabelAreTranslated(string $locale): void
    {
        $messages = Yaml::parseFile(__DIR__ . '/../../../../translations/demo_erp.' . $locale . '.yaml');

        foreach (['import-prices', 'import-stock', 'orders', 'acknowledgement'] as $kind) {
            self::assertIsString(Typed::at($messages, 'erp', 'kinds', $kind));
        }
        self::assertIsString(Typed::at($messages, 'erp', 'report_line'));
        self::assertStringContainsString('%line%', (string) Typed::at($messages, 'erp', 'report_line'));
    }
}
