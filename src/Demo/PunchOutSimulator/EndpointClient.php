<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use App\Demo\Http\InternalRequestToken;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Posts a cXML document to the shop's own PunchOut endpoint (POST /punchout/cxml), as the procurement
 * tool would. The connection goes to the local server (or to DEMO_SIMULATOR_BASE_URL when the app cannot
 * reach itself on loopback): the Host header only tells the shop which public name it is served under, it
 * is never a connection target, so the simulator cannot be used to reach another machine.
 *
 * The call carries the IP of the visitor (X-Forwarded-For, honoured because loopback is a trusted proxy) so
 * that the plugin limits each visitor on its own instead of every visitor as 127.0.0.1, and the internal
 * token that lets it through the private gate and the PunchOut endpoint guard.
 */
final readonly class EndpointClient
{
    public const PATH = '/punchout/cxml';

    public function __construct(
        private HttpClientInterface $httpClient,
        private InternalRequestToken $internalToken,
        #[Autowire(env: 'default::DEMO_SIMULATOR_BASE_URL')]
        private ?string $baseUrl = null,
    ) {
    }

    /** @throws SimulatorException */
    public function post(string $xml, string $publicHost, string $publicScheme, string $localPort, string $visitorIp): string
    {
        $base = (null !== $this->baseUrl && '' !== $this->baseUrl) ? rtrim($this->baseUrl, '/') : 'http://127.0.0.1:' . $localPort;

        try {
            $response = $this->httpClient->request('POST', $base . self::PATH, [
                'body' => $xml,
                'headers' => [
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'Host' => $publicHost,
                    'X-Forwarded-Proto' => $publicScheme,
                    'X-Forwarded-For' => $visitorIp,
                    InternalRequestToken::HEADER => $this->internalToken->value(),
                ],
                'max_redirects' => 0,
                'timeout' => 10,
            ]);
            if (200 !== $response->getStatusCode()) {
                throw new SimulatorException('endpoint_status');
            }

            return $response->getContent();
        } catch (ExceptionInterface) {
            throw new SimulatorException('endpoint_unreachable');
        }
    }
}
