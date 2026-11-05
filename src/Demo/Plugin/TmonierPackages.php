<?php

declare(strict_types=1);

namespace App\Demo\Plugin;

/**
 * The one rule that tells a showcased plugin from the other tmonier/* packages of composer.json. A theme is not a plugin:
 * it is not in the catalogue, not counted among the plugins and not part of the compatibility matrix (US-228).
 */
final class TmonierPackages
{
    private const string PREFIX = 'tmonier/';

    /** Published themes of the vendor: installed by Composer, never counted as plugins. */
    private const array THEMES = ['tmonier/sylius-industry-trade-theme'];

    public static function isPlugin(string $package): bool
    {
        return str_starts_with($package, self::PREFIX) && !\in_array($package, self::THEMES, true);
    }

    /** The slug of a plugin package ("tmonier/sylius-x-plugin" gives "sylius-x-plugin"). */
    public static function slug(string $package): string
    {
        return substr($package, \strlen(self::PREFIX));
    }
}
