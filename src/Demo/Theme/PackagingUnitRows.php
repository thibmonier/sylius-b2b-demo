<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** Source of the `packaging_unit` attribute values, all products at once. */
interface PackagingUnitRows
{
    /** @return list<array{productId: int|string, locale: string, text: string|null}> */
    public function rows(): array;
}
