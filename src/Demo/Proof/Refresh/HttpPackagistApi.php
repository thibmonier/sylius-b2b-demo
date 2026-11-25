<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The public Packagist metadata: no account, no token. Packagist refuses requests without an identifying User-Agent.
 *
 * Reads the Composer v2 metadata (repo.packagist.org/p2), the source `composer install` resolves from, updated within a
 * minute of a release; the packagist.org/packages/*.json API sits behind a CDN cache of 12 hours. Only the tagged
 * versions are in this file (the dev branches live in `~dev.json`), which is all the proof uses.
 */
final readonly class HttpPackagistApi implements PackagistApi
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function package(string $name): ?array
    {
        try {
            $response = $this->client->request('GET', 'https://repo.packagist.org/p2/' . $name . '.json', [
                'headers' => ['User-Agent' => 'sylius-b2b-demo-proof/1.0 (+https://github.com/thibmonier/sylius-b2b-demo)', 'Accept' => 'application/json'],
                'timeout' => 20,
            ]);
            $status = $response->getStatusCode();
            if (404 === $status) {
                return null;
            }
            if (200 !== $status) {
                throw new SourceUnavailable(\sprintf('Packagist answered %d for %s', $status, $name));
            }
            $data = $response->toArray();
        } catch (SourceUnavailable $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new SourceUnavailable('Packagist is not reachable for ' . $name . ': ' . $exception->getMessage(), 0, $exception);
        }
        $versions = \is_array($data['packages'] ?? null) ? ($data['packages'][$name] ?? null) : null;
        if (!\is_array($versions)) {
            throw new SourceUnavailable('Packagist sent an unexpected answer for ' . $name);
        }

        return ['versions' => self::expanded($versions)];
    }

    /**
     * Composer's minified format ("composer/2.0"): every version after the first lists only the keys that changed,
     * "__unset" removes one.
     *
     * @param array<array-key, mixed> $minified
     *
     * @return array<string, array<array-key, mixed>> the full metadata of each version, keyed by version
     */
    private static function expanded(array $minified): array
    {
        $expanded = [];
        $current = [];
        foreach ($minified as $changes) {
            if (!\is_array($changes)) {
                throw new SourceUnavailable('Packagist sent a version that is not an object');
            }
            foreach ($changes as $key => $value) {
                if ('__unset' === $value) {
                    unset($current[$key]);
                } else {
                    $current[$key] = $value;
                }
            }
            if (\is_string($current['version'] ?? null)) {
                $expanded[$current['version']] = $current;
            }
        }

        return $expanded;
    }
}
