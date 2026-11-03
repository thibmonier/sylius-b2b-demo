<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/** Semver type of an update, shared by the demo report and the plugins section of the weekly version watch. */
final class SemverUpdate
{
    /**
     * Before 1.0, a minor bump may break the API, so it counts as major.
     *
     * @return 'patch'|'minor'|'major'
     */
    public static function type(string $current, string $latest): string
    {
        [$currentMajor, $currentMinor] = explode('.', ltrim($current, 'v^~') . '.0');
        [$latestMajor, $latestMinor] = explode('.', ltrim($latest, 'v^~') . '.0');

        if ($currentMajor !== $latestMajor || ('0' === $currentMajor && $currentMinor !== $latestMinor)) {
            return 'major';
        }

        return $currentMinor === $latestMinor ? 'patch' : 'minor';
    }

    /**
     * Type of a Dependabot pull request, read from its title ("from 1.2.3 to 1.3.0", "requirement from ^10.5 to ^13.4").
     * A grouped pull request names no version: null, it has to be reviewed.
     *
     * @return 'patch'|'minor'|'major'|null
     */
    public static function ofDependabotTitle(string $title): ?string
    {
        if (1 !== preg_match('/\bfrom\s+(\S+)\s+to\s+(\S+)/', $title, $match)) {
            return null;
        }

        return self::type($match[1], $match[2]);
    }
}
