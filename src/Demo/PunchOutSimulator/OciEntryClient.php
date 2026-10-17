<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use App\Demo\Http\InternalRequestToken;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Posts the OCI entry to the shop's own route (POST /punchout/oci/<buyer code>), as SAP would. Like EndpointClient, the
 * connection goes to the local server (or DEMO_SIMULATOR_BASE_URL) and the Host header is never a connection target, so the
 * simulator cannot be used to reach another machine. The call carries the IP of the visitor and the internal token that
 * lets it through the private gate and the PunchOut endpoint guard. The shop answers with the session cookie and a redirect:
 * both are handed back to relay to the visitor's browser.
 */
final readonly class OciEntryClient
{
    public const PATH = '/punchout/oci/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private InternalRequestToken $internalToken,
        #[Autowire(env: 'default::DEMO_SIMULATOR_BASE_URL')]
        private ?string $baseUrl = null,
    ) {
    }

    /** @throws SimulatorException */
    public function post(OciCredentials $credentials, string $hookUrl, string $publicHost, string $publicScheme, string $localPort, string $visitorIp): OciEntryResult
    {
        $base = (null !== $this->baseUrl && '' !== $this->baseUrl) ? rtrim($this->baseUrl, '/') : 'http://127.0.0.1:' . $localPort;

        try {
            $response = $this->httpClient->request('POST', $base . self::PATH . rawurlencode($credentials->buyerCode), [
                'body' => $credentials->entryFields($hookUrl),
                'headers' => [
                    'Host' => $publicHost,
                    'X-Forwarded-Proto' => $publicScheme,
                    'X-Forwarded-For' => $visitorIp,
                    InternalRequestToken::HEADER => $this->internalToken->value(),
                ],
                'max_redirects' => 0,
                'timeout' => 10,
            ]);
            $headers = $response->getHeaders(false);

            return new OciEntryResult(
                $response->getStatusCode(),
                ($headers['location'][0] ?? null),
                array_map(static fn (string $line): Cookie => Cookie::fromString($line), $headers['set-cookie'] ?? []),
            );
        } catch (ExceptionInterface) {
            throw new SimulatorException('endpoint_unreachable');
        }
    }
}
