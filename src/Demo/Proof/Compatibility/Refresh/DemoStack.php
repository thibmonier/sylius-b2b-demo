<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Refresh\SourceUnavailable;

/** The combination the demo really runs (US-171), read from its composer.lock: nothing is declared by hand. */
final class DemoStack
{
    /** @return array{sylius: string, symfony: string, php: ?string, plugins: array<string, string>} */
    public static function fromLock(string $lock): array
    {
        try {
            $data = json_decode($lock, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SourceUnavailable('composer.lock is not readable: ' . $exception->getMessage(), 0, $exception);
        }
        $versions = [];
        foreach (\is_array($data) && \is_array($data['packages'] ?? null) ? $data['packages'] : [] as $package) {
            if (\is_array($package) && \is_string($package['name'] ?? null) && \is_string($package['version'] ?? null)) {
                $versions[$package['name']] = $package['version'];
            }
        }
        if (!isset($versions['sylius/sylius'], $versions['symfony/framework-bundle'])) {
            throw new SourceUnavailable('composer.lock holds no Sylius or Symfony');
        }
        $plugins = [];
        foreach ($versions as $name => $version) {
            if (str_starts_with($name, 'tmonier/')) {
                $plugins[substr($name, 8)] = $version;
            }
        }
        $platform = \is_array($data) && \is_array($data['platform'] ?? null) ? $data['platform'] : [];

        return [
            'sylius' => ltrim($versions['sylius/sylius'], 'v'),
            'symfony' => ltrim($versions['symfony/framework-bundle'], 'v'),
            'php' => \is_string($platform['php'] ?? null) ? $platform['php'] : null,
            'plugins' => $plugins,
        ];
    }
}
