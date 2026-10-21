<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Analytics;

use App\Demo\Analytics\UmamiEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-143: the `data-umami-event` attributes are built in one place: `demo-` prefix, no free text, no personal data. */
final class UmamiEventTest extends TestCase
{
    public function testTheEventIsPrefixed(): void
    {
        self::assertSame(' data-umami-event="demo-cta-profil"', UmamiEvent::attributes('cta-profil'));
    }

    public function testPropertiesBecomeDataAttributes(): void
    {
        self::assertSame(
            ' data-umami-event="demo-cta-profil" data-umami-event-emplacement="fiche-produit"',
            UmamiEvent::attributes('cta-profil', ['emplacement' => 'fiche-produit']),
        );
    }

    public function testThePrefixIsNotDuplicated(): void
    {
        self::assertSame(' data-umami-event="demo-quote-sent"', UmamiEvent::attributes('demo-quote-sent'));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['CTA'];
        yield 'space' => ['a b'];
        yield 'quote' => ['a"b'];
        yield 'markup' => ['<script>'];
        yield 'too long' => [str_repeat('a', 60)];
    }

    #[DataProvider('invalidNames')]
    public function testAnInvalidNameProducesNoAttribute(string $name): void
    {
        self::assertSame('', UmamiEvent::attributes($name));
    }

    /** @return iterable<string, array{string}> */
    public static function personalValues(): iterable
    {
        yield 'an e-mail address' => ['jane@example.com'];
        yield 'free text' => ['Société Dupont'];
        yield 'a quote' => ['a"b'];
        yield 'too long' => [str_repeat('a', 60)];
    }

    #[DataProvider('personalValues')]
    public function testAPropertyThatIsNotAPlainLabelIsDropped(string $value): void
    {
        self::assertSame(' data-umami-event="demo-x"', UmamiEvent::attributes('x', ['role' => $value]));
    }

    public function testAnInvalidPropertyNameIsDropped(): void
    {
        self::assertSame(' data-umami-event="demo-x"', UmamiEvent::attributes('x', ['Role"' => 'a']));
    }
}
