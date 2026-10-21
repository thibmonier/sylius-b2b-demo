<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/** The public Packagist API: no account, no token. Packagist refuses requests without an identifying User-Agent. */
final readonly class HttpPackagistApi implements PackagistApi
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function package(string $name): ?array
    {
        try {
            $response = $this->client->request('GET', 'https://packagist.org/packages/' . $name . '.json', [
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
        $package = $data['package'] ?? null;

        return \is_array($package) ? $package : throw new SourceUnavailable('Packagist sent an unexpected answer for ' . $name);
    }
}
