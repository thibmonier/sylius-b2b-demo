<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

/** Reads the `packaging_unit` attribute of the catalogue CSV ("boîte de 100", "lot de 25", "unité"…). */
final class PackagingUnitParser
{
    public const KIND_BOX = 'box';

    public const KIND_LOT = 'lot';

    public const KIND_OTHER = 'other';

    /** Number of sold units in one case; 1 when the packaging states no count. */
    public function unitsPerCase(?string $packagingUnit): int
    {
        if (1 !== \preg_match('/\sde\s+(\d+)\s*$/u', $this->normalized($packagingUnit), $matches)) {
            return 1;
        }

        return \max(1, (int) $matches[1]);
    }

    /** @return self::KIND_* */
    public function kind(?string $packagingUnit): string
    {
        $packaging = $this->normalized($packagingUnit);

        return match (true) {
            \str_starts_with($packaging, 'boîte') => self::KIND_BOX,
            \str_starts_with($packaging, 'lot') => self::KIND_LOT,
            default => self::KIND_OTHER,
        };
    }

    /** Trimmed and in NFC: a CSV saved on macOS may carry "î" as "i" plus a combining accent, which "boîte" would not match. */
    private function normalized(?string $packagingUnit): string
    {
        $packaging = \trim((string) $packagingUnit);

        // normalize() returns false for invalid UTF-8: the text is then left as it is.
        return \Normalizer::normalize($packaging, \Normalizer::FORM_C) ?: $packaging;
    }
}
