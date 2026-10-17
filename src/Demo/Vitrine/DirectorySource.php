<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

final readonly class DirectorySource implements FileSource
{
    public function __construct(private string $directory)
    {
    }

    public function read(string $name): ?string
    {
        $path = $this->directory . '/' . $name;
        if (!\is_file($path)) {
            return null;
        }
        $content = \file_get_contents($path);

        return false === $content ? null : $content;
    }
}
