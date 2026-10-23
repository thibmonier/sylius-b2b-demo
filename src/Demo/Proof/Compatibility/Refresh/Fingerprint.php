<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

/**
 * What the proof check compares (US-171) to tell that a composer.json or a CI matrix changed since the last generation.
 * The same value comes from the files read through the API and from a local clone.
 */
final class Fingerprint
{
    public static function of(string $composerJson, ?string $workflow): string
    {
        return hash('sha256', $composerJson . "\n--workflow--\n" . ($workflow ?? '--none--'));
    }

    public static function ofDirectory(string $directory): ?string
    {
        $composer = $directory . '/composer.json';
        if (!is_file($composer)) {
            return null;
        }
        $names = [];
        foreach (glob($directory . '/.github/workflows/*') ?: [] as $path) {
            if (1 === preg_match('/^[\w.\-]+\.ya?ml$/', basename($path))) {
                $names[] = basename($path);
            }
        }
        $name = CiWorkflow::preferred($names);
        $workflow = null === $name ? null : file_get_contents($directory . '/.github/workflows/' . $name);

        return self::of((string) file_get_contents($composer), false === $workflow ? null : $workflow);
    }
}
