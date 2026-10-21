<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use Symfony\Component\Yaml\Yaml;

/** The plugins of config/demo/plugins.yaml read without booting the application (manual tooling and its tests). */
final class PluginCatalogFile
{
    /** @return list<array{slug: string, published: bool}> */
    public static function entries(string $path): array
    {
        $parsed = Yaml::parseFile($path);
        $parameters = \is_array($parsed) && \is_array($parsed['parameters'] ?? null) ? $parsed['parameters'] : [];
        $catalogue = \is_array($parameters['demo.plugin_catalog'] ?? null) ? $parameters['demo.plugin_catalog'] : [];
        $entries = [];
        foreach ($catalogue as $entry) {
            if (\is_array($entry) && \is_string($entry['slug'] ?? null)) {
                $entries[] = ['slug' => $entry['slug'], 'published' => false !== ($entry['published'] ?? true)];
            }
        }

        return $entries;
    }
}
