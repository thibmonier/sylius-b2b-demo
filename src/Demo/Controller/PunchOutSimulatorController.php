<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Http\VisitorIpResolver;
use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\CxmlResponse;
use App\Demo\PunchOutSimulator\EndpointClient;
use App\Demo\PunchOutSimulator\OrderMessageParser;
use App\Demo\PunchOutSimulator\OrderRequestBuilder;
use App\Demo\PunchOutSimulator\ReceivedCart;
use App\Demo\PunchOutSimulator\ReceivedLine;
use App\Demo\PunchOutSimulator\ResponseParser;
use App\Demo\PunchOutSimulator\ReturnAuthenticator;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use App\Demo\PunchOutSimulator\SimulatorException;
use App\Demo\PunchOutSimulator\SimulatorOrderGate;
use App\Demo\PunchOutSimulator\SimulatorStep;
use App\Order\OrderPoReferenceAwareInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

/**
 * US-027: simulates the e-procurement tool of Acme (Ariba/SAP) against the demo's own PunchOut plugin.
 *
 * Flow: PunchOutSetupRequest posted to the plugin endpoint (POST /punchout/cxml) -> the visitor is sent to
 * the StartPage (/punchout/start/{token}) logged in as the buyer -> the plugin posts the
 * PunchOutOrderMessage back to {@see self::receive()} -> the visitor can send an OrderRequest to the same
 * endpoint. The shared secret never leaves the server. Everything answers 404 outside the demo mode.
 */
#[Route('/demo/punchout-simulator')]
final readonly class PunchOutSimulatorController
{
    private const CSRF_START = 'demo_punchout_simulator_start';

    private const CSRF_ORDER = 'demo_punchout_simulator_order';

    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private BuyerCredentialsProvider $credentials,
        private SetupRequestBuilder $setupBuilder,
        private OrderRequestBuilder $orderBuilder,
        private OrderMessageParser $messageParser,
        private ResponseParser $responseParser,
        private EndpointClient $endpoint,
        private SignedState $state,
        private SimulatorOrderGate $orderGate,
        private ReturnAuthenticator $returns,
        private VisitorIpResolver $visitors,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    #[Route('', name: 'app_demo_punchout_simulator', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->guard();

        return $this->page($request);
    }

    #[Route('/start', name: 'app_demo_punchout_simulator_start', methods: ['POST'])]
    public function start(Request $request): Response
    {
        $this->guard();
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_START, $request->request->getString('_token')))) {
            return $this->page($request, ['error' => ['key' => 'csrf']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $response = $this->setup($request);
        } catch (SimulatorException $exception) {
            return $this->page($request, ['error' => ['key' => $exception->getMessage()]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $token = $response->isOk() ? $response->startToken() : null;
        if (null === $token) {
            return $this->page($request, ['error' => $this->refusal($response, $request)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Rebuilt from the token only: the redirect always targets the plugin start page of this shop.
        return new RedirectResponse($this->urlGenerator->generate('tmonier_punchout_start', ['token' => $token]));
    }

    /** Target of the BrowserFormPost: receives the cart the shop sends back (field "cXML-base64"). */
    #[Route('/return', name: 'app_demo_punchout_simulator_return', methods: ['POST'])]
    public function receive(Request $request): Response
    {
        $this->guard();

        $encoded = $request->request->getString('cXML-base64');

        try {
            $cart = $this->messageParser->parseBase64($encoded);
        } catch (SimulatorException $exception) {
            return $this->page($request, ['error' => ['key' => $exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
        if (null === $this->state->verify($cart->buyerCookie)) {
            return $this->page($request, ['error' => ['key' => 'session_expired']], Response::HTTP_GONE);
        }
        $refusal = $this->returnRefusal($request, $this->returns->claim((string) base64_decode($encoded, true)));
        if ($refusal instanceof Response) {
            return $refusal;
        }
        if ($cart->isEmpty()) {
            return $this->page($request, ['error' => ['key' => 'empty_cart']]);
        }

        return $this->page($request, ['cart' => $cart, 'state' => $this->state->sign($this->cartData($cart)), 'orderId' => 'PO-' . strtoupper(bin2hex(random_bytes(3)))]);
    }

    #[Route('/order', name: 'app_demo_punchout_simulator_order', methods: ['POST'])]
    public function order(Request $request): Response
    {
        $this->guard();
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_ORDER, $request->request->getString('_token')))) {
            return $this->page($request, ['error' => ['key' => 'csrf']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $cart = $this->cartFromState($request->request->getString('state'));
        if (!$cart instanceof ReceivedCart) {
            return $this->page($request, ['error' => ['key' => 'session_expired']], Response::HTTP_GONE);
        }
        $orderId = trim($request->request->getString('orderId'));
        $problem = $this->orderProblem($cart, $orderId);
        if (null !== $problem) {
            return $this->page($request, ['error' => ['key' => $problem], 'cart' => $cart, 'state' => $request->request->getString('state'), 'orderId' => $orderId], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $nonce = $this->sessionNonce($cart);
        $claim = null === $nonce ? null : $this->orderGate->claim($nonce);
        if (SimulatorOrderGate::CLAIMED !== $claim) {
            return $this->claimRefusal($request, $claim);
        }

        try {
            $xml = $this->orderBuilder->build($this->credentials->provide(), $cart, $orderId);
            $response = $this->responseParser->parse($this->post($xml, $request));
        } catch (SimulatorException $exception) {
            $this->orderGate->release((string) $nonce);

            return $this->page($request, ['error' => ['key' => $exception->getMessage()]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (!$response->isOk()) {
            $this->orderGate->release((string) $nonce);

            return $this->page($request, ['error' => $this->refusal($response, $request), 'cart' => $cart, 'state' => $request->request->getString('state'), 'orderId' => $orderId], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->page($request, ['sent' => ['orderId' => $orderId, 'message' => $response->message], 'cart' => $cart]);
    }

    private function returnRefusal(Request $request, string $verdict): ?Response
    {
        return match ($verdict) {
            ReturnAuthenticator::FORGED => $this->page($request, ['error' => ['key' => 'forged_return']], Response::HTTP_FORBIDDEN),
            ReturnAuthenticator::EXPIRED => $this->page($request, ['error' => ['key' => 'session_expired']], Response::HTTP_GONE),
            ReturnAuthenticator::REPLAYED => $this->page($request, ['error' => ['key' => 'return_replayed']], Response::HTTP_GONE),
            default => null,
        };
    }

    /** The one-time identity of the session: the nonce inside the signed BuyerCookie the cart came back with. */
    private function sessionNonce(ReceivedCart $cart): ?string
    {
        $nonce = $this->state->verify($cart->buyerCookie)['nonce'] ?? null;

        return \is_string($nonce) && '' !== $nonce ? $nonce : null;
    }

    private function claimRefusal(Request $request, ?string $claim): Response
    {
        return match ($claim) {
            SimulatorOrderGate::REPLAYED => $this->page($request, ['error' => ['key' => 'already_sent']], Response::HTTP_GONE),
            SimulatorOrderGate::CAP_REACHED => $this->page($request, ['error' => ['key' => 'daily_cap']], Response::HTTP_TOO_MANY_REQUESTS),
            default => $this->page($request, ['error' => ['key' => 'session_expired']], Response::HTTP_GONE),
        };
    }

    private function setup(Request $request): CxmlResponse
    {
        $returnUrl = $this->urlGenerator->generate('app_demo_punchout_simulator_return', ['lang' => $this->locale($request)], UrlGeneratorInterface::ABSOLUTE_URL);
        if ($request->request->getBoolean('foreign_domain')) {
            // Demonstrates the whitelist of return domains: the plugin must refuse this one.
            $returnUrl = 'https://not-allowed.example' . parse_url($returnUrl, \PHP_URL_PATH);
        }
        $xml = $this->setupBuilder->build(
            $this->credentials->provide($request->request->getBoolean('wrong_secret')),
            $this->state->sign(['nonce' => bin2hex(random_bytes(8))]),
            $returnUrl,
        );

        return $this->responseParser->parse($this->post($xml, $request));
    }

    private function post(string $xml, Request $request): string
    {
        return $this->endpoint->post($xml, $request->getHttpHost(), $request->getScheme(), $request->server->getString('SERVER_PORT', '80'), $this->visitors->resolve($request));
    }

    /** @return array{key: string, status?: int, detail?: string} */
    private function refusal(CxmlResponse $response, Request $request): array
    {
        return [
            'key' => 'status_' . (in_array($response->code, [400, 401, 403, 429], true) ? $response->code : 'other'),
            'status' => $response->code,
            'detail' => $response->message,
            'host' => $request->getHost(),
        ];
    }

    private function orderProblem(ReceivedCart $cart, string $orderId): ?string
    {
        if ($cart->isEmpty()) {
            return 'empty_cart_order';
        }
        $valid = '' !== $orderId &&
            mb_strlen($orderId) <= OrderPoReferenceAwareInterface::PO_REFERENCE_MAX_LENGTH &&
            1 === preg_match(OrderPoReferenceAwareInterface::PO_REFERENCE_PATTERN, $orderId);

        return $valid ? null : 'invalid_order_id';
    }

    /** @return array<string, mixed> */
    private function cartData(ReceivedCart $cart): array
    {
        return [
            'cookie' => $cart->buyerCookie,
            'currency' => $cart->currency,
            'total' => $cart->total,
            'lines' => array_map(static fn (ReceivedLine $line): array => $line->toArray(), $cart->lines),
        ];
    }

    private function cartFromState(string $value): ?ReceivedCart
    {
        $data = $this->state->verify($value);
        if (null === $data || !is_string($data['cookie'] ?? null) || !is_string($data['currency'] ?? null) || !is_string($data['total'] ?? null) || !is_array($data['lines'] ?? null)) {
            return null;
        }
        $lines = [];
        foreach ($data['lines'] as $row) {
            $line = is_array($row) ? ReceivedLine::fromArray($row) : null;
            if (!$line instanceof ReceivedLine) {
                return null;
            }
            $lines[] = $line;
        }

        return new ReceivedCart($data['cookie'], $data['currency'], $data['total'], $lines);
    }

    private function locale(Request $request): string
    {
        // `lang` from the simulator's own links, `_locale` from the "See it in the demo" button of the plugins page (US-243).
        $lang = $request->query->get('lang') ?? substr((string) $request->query->get('_locale'), 0, 2);

        return 'en' === $lang ? 'en' : 'fr';
    }

    /** @param array<string, mixed> $parameters */
    private function page(Request $request, array $parameters = [], int $status = Response::HTTP_OK): Response
    {
        $locale = $this->locale($request);
        $response = new Response($this->twig->render('demo/punchout_simulator/simulator.html.twig', $parameters + [
            'step' => SimulatorStep::of($parameters),
            'locale' => $locale,
            'csrf' => $this->csrfTokenManager->getToken(self::CSRF_START)->getValue(),
            'csrf_order' => $this->csrfTokenManager->getToken(self::CSRF_ORDER)->getValue(),
            'domains' => $this->safeDomains(),
            'host' => $request->getHost(),
        ]), $status);
        // Carries a signed cart and form tokens: never cached.
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    /** @return list<string> */
    private function safeDomains(): array
    {
        try {
            return $this->credentials->returnDomains();
        } catch (SimulatorException) {
            return [];
        }
    }

    private function guard(): void
    {
        if (!$this->demoMode) {
            throw new NotFoundHttpException();
        }
    }
}
