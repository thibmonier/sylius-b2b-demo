<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/**
 * What the proof check compares (US-178) to tell that a composer.json or a README changed since the last generation.
 * The same value comes from the files read through the API and from a local clone.
 */
final class PackFingerprint
{
    public static function of(string $composerJson, string $readme, string $readmeFr): string
    {
        return hash('sha256', $composerJson . "\n--readme--\n" . $readme . "\n--readme.fr--\n" . $readmeFr);
    }

    public static function ofDirectory(string $directory): ?string
    {
        if (!is_file($directory . '/composer.json')) {
            return null;
        }

        return self::of(self::content($directory . '/composer.json'), self::content($directory . '/README.md'), self::content($directory . '/README.fr.md'));
    }

    private static function content(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
