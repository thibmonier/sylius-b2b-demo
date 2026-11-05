<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures\Passport;

/**
 * The GTIN and the three mandatory sustainability fields of a demo passport. An absent or blank field fails the fixture load by name:
 * the demo never stores a default text in place of missing data (US-235).
 */
final readonly class PassportSheet
{
    /** Reserved by GS1 for examples and documentation: never a real product. */
    public const GTIN_PREFIX = '0950600';

    private function __construct(
        public string $gtin,
        public string $originMaterials,
        public string $recyclability,
        public string $repairability,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(string $productCode, array $data): self
    {
        return new self(
            self::gtin($productCode, $data),
            self::required($productCode, $data, 'originMaterials'),
            self::required($productCode, $data, 'recyclability'),
            self::required($productCode, $data, 'repairability'),
        );
    }

    /** @param array<string, mixed> $data */
    private static function gtin(string $productCode, array $data): string
    {
        $gtin = self::required($productCode, $data, 'gtin');
        if (!Gtin::isValid($gtin)) {
            throw new \InvalidArgumentException(\sprintf('Digital passport fixture "%s": GTIN "%s" is not valid (length or check digit).', $productCode, $gtin));
        }
        if (!str_starts_with($gtin, self::GTIN_PREFIX)) {
            throw new \InvalidArgumentException(\sprintf('Digital passport fixture "%s": GTIN "%s" must start with the GS1 example prefix "%s".', $productCode, $gtin, self::GTIN_PREFIX));
        }

        return $gtin;
    }

    /** @param array<string, mixed> $data */
    private static function required(string $productCode, array $data, string $field): string
    {
        $value = $data[$field] ?? null;
        if (!\is_string($value) || '' === trim($value)) {
            throw new \InvalidArgumentException(\sprintf('Digital passport fixture "%s": required field "%s" is missing or blank.', $productCode, $field));
        }

        return $value;
    }
}
