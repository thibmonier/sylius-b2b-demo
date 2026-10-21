<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/** HTTP status of a URL, 0 when it cannot be reached. */
interface LinkProbe
{
    public function status(string $url): int;
}
