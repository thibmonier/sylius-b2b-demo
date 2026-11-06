<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures\Passport;

/**
 * GS1 check digit (mod 10) for the fictional GTIN of the demo passports. The plugin refuses a GTIN whose key is wrong,
 * so the fixture checks it as well and fails by name instead of leaving an "invalid GTIN" tab in the demo.
 */
final class Gtin
{
    /** Lengths the plugin accepts (GTIN-8, 12, 13 and 14). */
    private const LENGTHS = [8, 12, 13, 14];

    public static function checkDigit(string $body): int
    {
        $sum = 0;
        foreach (array_reverse(str_split($body)) as $index => $digit) {
            $sum += (int) $digit * (0 === $index % 2 ? 3 : 1);
        }

        return (10 - $sum % 10) % 10;
    }

    public static function withCheckDigit(string $body): string
    {
        return $body . self::checkDigit($body);
    }

    public static function isValid(string $gtin): bool
    {
        if (!\in_array(\strlen($gtin), self::LENGTHS, true) || !ctype_digit($gtin)) {
            return false;
        }

        return self::withCheckDigit(substr($gtin, 0, -1)) === $gtin;
    }
}
