<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/**
 * Reads the OCI 5.0 fields the shop posts back (NEW_ITEM-DESCRIPTION[n], -QUANTITY[n], -PRICE[n], -CURRENCY[n], -UNIT[n],
 * -VENDORMAT[n], -EXT_PRODUCT_ID[n]) as the cart the simulator shows. A cart without NEW_ITEM field is an empty cart (the
 * buyer cancelled), not an error. Everything read is bounded and typed: the fields come from the visitor's browser.
 */
final readonly class OciCartReader
{
    private const MAX_LINES = 200;

    private const MAX_QUANTITY = 9999;

    /** @param array<array-key, mixed> $fields the parsed body of the POST */
    public function read(array $fields): ReceivedCart
    {
        $descriptions = $this->column($fields, 'DESCRIPTION');
        if ([] === $descriptions) {
            return new ReceivedCart('', '', '0.00', []);
        }
        if (\count($descriptions) > self::MAX_LINES) {
            throw new SimulatorException('invalid_oci_return');
        }

        $lines = [];
        $total = 0;
        foreach (array_keys($descriptions) as $index) {
            $line = $this->line($fields, $index);
            $lines[] = $line;
            $total += $line->quantity * $this->microUnits($line->unitPrice);
        }

        return new ReceivedCart('', $lines[0]->currency, $this->format($total), $lines);
    }

    /** @param array<array-key, mixed> $fields */
    private function line(array $fields, int|string $index): ReceivedLine
    {
        $value = fn (string $name): ?string => $this->column($fields, $name)[$index] ?? null;
        $quantity = $value('QUANTITY');
        $price = $value('PRICE');
        $currency = $value('CURRENCY');
        $valid = null !== $quantity && 1 === preg_match('/^[1-9]\d{0,3}$/', $quantity) && (int) $quantity <= self::MAX_QUANTITY &&
            null !== $price && 1 === preg_match('/^\d{1,9}(\.\d{1,4})?$/', $price) &&
            null !== $currency && 1 === preg_match('/^[A-Z]{3}$/', $currency);
        if (!$valid) {
            throw new SimulatorException('invalid_oci_return');
        }

        return new ReceivedLine(
            mb_substr($value('VENDORMAT') ?? '', 0, 40),
            mb_substr($value('EXT_PRODUCT_ID') ?? '', 0, 40),
            mb_substr($value('DESCRIPTION') ?? '', 0, 200),
            (int) $quantity,
            $price,
            $currency,
            mb_substr($value('UNIT') ?? '', 0, 3),
        );
    }

    /**
     * @param array<array-key, mixed> $fields
     *
     * @return array<int|string, string> the values of NEW_ITEM-<name>[n] by n
     */
    private function column(array $fields, string $name): array
    {
        $values = $fields['NEW_ITEM-' . $name] ?? [];
        if (!\is_array($values)) {
            throw new SimulatorException('invalid_oci_return');
        }
        foreach ($values as $value) {
            if (!\is_string($value)) {
                throw new SimulatorException('invalid_oci_return');
            }
        }

        return $values;
    }

    private function microUnits(string $price): int
    {
        [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '');

        return (int) $whole * 10000 + (int) str_pad($fraction, 4, '0');
    }

    /** The sum in ten-thousandths, rounded to the cent. */
    private function format(int $microUnits): string
    {
        $cents = intdiv($microUnits + 50, 100);

        return \sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}
