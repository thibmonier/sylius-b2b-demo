<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures\Passport;

/**
 * The three mandatory sustainability fields of a demo passport. An absent or blank field fails the fixture load by name:
 * the demo never stores a default text in place of missing data (US-235).
 */
final readonly class PassportSheet
{
    private function __construct(
        public string $originMaterials,
        public string $recyclability,
        public string $repairability,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(string $productCode, array $data): self
    {
        return new self(
            self::required($productCode, $data, 'originMaterials'),
            self::required($productCode, $data, 'recyclability'),
            self::required($productCode, $data, 'repairability'),
        );
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
