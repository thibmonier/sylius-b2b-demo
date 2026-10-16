<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mail;

use App\Demo\Mail\MailTwigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailMaskTest extends TestCase
{
    #[DataProvider('provideAddresses')]
    public function testItKeepsTheFirstLetterAndTheDomainOfEveryAddress(string $input, string $expected): void
    {
        self::assertSame($expected, MailTwigExtension::maskAddresses($input));
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideAddresses(): iterable
    {
        yield 'plain address' => ['claire@acme.example', 'c***@acme.example'];
        yield 'with a name' => ['Claire Dupont <claire@acme.example>', 'Claire Dupont <c***@acme.example>'];
        yield 'several recipients' => ['a@x.example, bob@y.example', 'a***@x.example, b***@y.example'];
        yield 'no address' => ['undisclosed recipients', 'undisclosed recipients'];
        yield 'empty' => ['', ''];
        yield 'plus tag and dots' => ['jean.dupont+demo@acme.example', 'j***@acme.example'];
    }
}
