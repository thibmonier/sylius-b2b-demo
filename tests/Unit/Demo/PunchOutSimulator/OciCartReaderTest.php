<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\OciCartReader;
use App\Demo\PunchOutSimulator\SimulatorException;
use App\Tests\Support\OciPostedBody;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/** US-053: the NEW_ITEM-* fields the plugin posts to the HOOK_URL are read back as a cart. */
final class OciCartReaderTest extends TestCase
{
    public function testTheFieldsOfTheTwoLinesAreReadWithTheirQuantitiesAndPrices(): void
    {
        $cart = (new OciCartReader())->read(OciPostedBody::of(
            new TransferLine('GANT-1', 'v1:GANT-1', 'Gants nitrile', 2, '12.50', 'EUR'),
            new TransferLine('CASQ-1', 'v1:CASQ-1', 'Casque de chantier', 3, '4.2', 'EUR'),
        ));

        self::assertCount(2, $cart->lines);
        self::assertSame('Gants nitrile', $cart->lines[0]->description);
        self::assertSame(2, $cart->lines[0]->quantity);
        self::assertSame('12.50', $cart->lines[0]->unitPrice);
        self::assertSame('GANT-1', $cart->lines[0]->supplierPartId);
        self::assertSame('v1:GANT-1', $cart->lines[0]->supplierPartAuxId);
        self::assertSame(3, $cart->lines[1]->quantity);
        self::assertSame('EUR', $cart->currency);
        self::assertSame('37.60', $cart->total);
    }

    public function testACartWithoutAnyLineIsEmptyAndNotAnError(): void
    {
        $cart = (new OciCartReader())->read(['~OkCode' => 'ADD', '~caller' => 'CTLG']);

        self::assertTrue($cart->isEmpty());
        self::assertSame('0.00', $cart->total);
    }

    /**
     * @param array<string, mixed> $fields
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('malformedFields')]
    public function testAMalformedLineIsRefused(array $fields): void
    {
        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('invalid_oci_return');

        (new OciCartReader())->read($fields);
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function malformedFields(): iterable
    {
        $line = ['NEW_ITEM-DESCRIPTION' => [1 => 'Gants'], 'NEW_ITEM-QUANTITY' => [1 => '2'], 'NEW_ITEM-PRICE' => [1 => '1.00'], 'NEW_ITEM-CURRENCY' => [1 => 'EUR']];

        yield 'quantity is not a number' => [array_replace($line, ['NEW_ITEM-QUANTITY' => [1 => 'many']])];
        yield 'quantity is zero' => [array_replace($line, ['NEW_ITEM-QUANTITY' => [1 => '0']])];
        yield 'price is not a decimal' => [array_replace($line, ['NEW_ITEM-PRICE' => [1 => '1,00 EUR']])];
        yield 'quantity is missing' => [array_diff_key($line, ['NEW_ITEM-QUANTITY' => 1])];
        yield 'field is not an indexed list' => [array_replace($line, ['NEW_ITEM-DESCRIPTION' => 'Gants'])];
        yield 'too many lines' => [self::lines(201)];
    }

    /** @return array<string, array<int, string>> */
    private static function lines(int $count): array
    {
        $fields = ['NEW_ITEM-DESCRIPTION' => [], 'NEW_ITEM-QUANTITY' => [], 'NEW_ITEM-PRICE' => [], 'NEW_ITEM-CURRENCY' => []];
        for ($n = 1; $n <= $count; ++$n) {
            $fields['NEW_ITEM-DESCRIPTION'][$n] = 'Item';
            $fields['NEW_ITEM-QUANTITY'][$n] = '1';
            $fields['NEW_ITEM-PRICE'][$n] = '1.00';
            $fields['NEW_ITEM-CURRENCY'][$n] = 'EUR';
        }

        return $fields;
    }
}
