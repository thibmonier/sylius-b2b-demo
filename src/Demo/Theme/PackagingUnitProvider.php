<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Locale\Context\LocaleContextInterface;

/**
 * Packaging unit of a product ("box of 100"…), read from the `packaging_unit` attribute.
 *
 * The values of every product are read in ONE query the first time and kept for the request, so a
 * catalogue page of N cards costs one query, not N lazy loads of the attribute collections.
 */
final class PackagingUnitProvider
{
    public const ATTRIBUTE_CODE = 'packaging_unit';

    /** @var array<int, array<string, string>>|null product id => locale => text */
    private ?array $values = null;

    public function __construct(
        private readonly PackagingUnitRows $rows,
        private readonly LocaleContextInterface $localeContext,
    ) {
    }

    public function forProductId(?int $productId): ?string
    {
        if (null === $productId) {
            return null;
        }
        $byLocale = $this->all()[$productId] ?? [];
        if ([] === $byLocale) {
            return null;
        }

        return $byLocale[$this->locale()] ?? array_values($byLocale)[0];
    }

    /** @return array<int, array<string, string>> */
    private function all(): array
    {
        if (null !== $this->values) {
            return $this->values;
        }
        $values = [];
        foreach ($this->rows->rows() as $row) {
            $text = trim((string) $row['text']);
            if ('' === $text) {
                continue;
            }
            $values[(int) $row['productId']][(string) $row['locale']] = $text;
        }

        return $this->values = $values;
    }

    private function locale(): string
    {
        try {
            return $this->localeContext->getLocaleCode();
        } catch (\Throwable) {
            return '';
        }
    }
}
