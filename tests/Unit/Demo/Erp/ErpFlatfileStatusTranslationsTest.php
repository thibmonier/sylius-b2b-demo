<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\ExchangeFileStatus;
use App\Tests\Support\Typed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-193: every situation of the excerpt that is not a file has its neutral message, in French and in English. */
final class ErpFlatfileStatusTranslationsTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr'];
        yield 'English' => ['en'];
    }

    #[DataProvider('locales')]
    public function testEveryStatusButShownHasANeutralMessageWithoutPath(string $locale): void
    {
        $messages = Yaml::parseFile(__DIR__ . '/../../../../translations/erp_flatfile.' . $locale . '.yaml');

        foreach (ExchangeFileStatus::cases() as $status) {
            if (ExchangeFileStatus::Shown === $status) {
                continue;
            }
            $text = Typed::at($messages, 'sample', 'status', $status->value);
            self::assertIsString($text);
            self::assertNotSame('', trim($text));
            self::assertStringNotContainsString('/', $text, 'no path');
        }
    }
}
