<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Refresh\SourceUnavailable;

/** What a plugin needs from the other plugins (US-171): "require" is mandatory, "suggest" is a suggestion. */
final class ComposerLinks
{
    private const string PREFIX = 'tmonier/';

    /** @return array{requires: list<string>, suggests: list<string>} plugin slugs, sorted */
    public static function parse(string $composerJson): array
    {
        try {
            $data = json_decode($composerJson, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SourceUnavailable('composer.json is not readable: ' . $exception->getMessage(), 0, $exception);
        }
        if (!\is_array($data)) {
            throw new SourceUnavailable('composer.json is not an object');
        }
        $requires = self::slugs($data['require'] ?? null);

        return ['requires' => $requires, 'suggests' => array_values(array_diff(self::slugs($data['suggest'] ?? null), $requires))];
    }

    /** @return list<string> */
    private static function slugs(mixed $section): array
    {
        $slugs = [];
        foreach (\is_array($section) ? array_keys($section) : [] as $name) {
            if (str_starts_with((string) $name, self::PREFIX)) {
                $slugs[] = substr((string) $name, \strlen(self::PREFIX));
            }
        }
        sort($slugs);

        return $slugs;
    }
}
