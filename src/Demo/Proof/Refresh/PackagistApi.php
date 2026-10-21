<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

interface PackagistApi
{
    /**
     * @return array<array-key, mixed>|null the package as Packagist describes it, null when it does not exist (404)
     *
     * @throws SourceUnavailable
     */
    public function package(string $name): ?array;
}
