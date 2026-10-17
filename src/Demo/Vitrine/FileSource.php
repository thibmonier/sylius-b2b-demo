<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * Read-only access to the files of one plugin repository, from the working tree or from a git reference.
 */
interface FileSource
{
    public function read(string $name): ?string;
}
