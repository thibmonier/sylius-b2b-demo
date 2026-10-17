<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Http\VisitorIpResolver;
use App\Demo\PunchOutSimulator\OciBuyerProvider;
use App\Demo\PunchOutSimulator\OciCartReader;
use App\Demo\PunchOutSimulator\OciEntryClient;
use App\Demo\PunchOutSimulator\OciEntryResult;
use App\Demo\PunchOutSimulator\OciHookUrl;
use App\Demo\PunchOutSimulator\OciReturnGate;
use App\Demo\PunchOutSimulator\SignedState;
use App\Demo\PunchOutSimulator\SimulatorException;
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
 * The SAP OCI half of the PunchOut simulator (US-053). The visitor plays the SAP user: the form posts the OCI entry
 * (USERNAME, PASSWORD, HOOK_URL) to the shop, which opens the catalog session; the cart the shop transfers comes back as
 * NEW_ITEM-* fields on the HOOK_URL, which is this simulator's own return. An OCI return creates no order, so there is no
 * daily order cap: the guards are the per-visitor limiters, the HOOK_URL whitelist and the one-time signed state.
 */
#[Route('/demo/punchout-simulator/oci')]
final readonly class PunchOutOciSimulatorController
{
    private const CSRF_START = 'demo_punchout_simulator_oci_start';

    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private OciBuyerProvider $buyers,
        private OciHookUrl $hookUrls,
        private OciEntryClient $entry,
        private OciCartReader $reader,
        private OciReturnGate $returns,
        private SignedState $state,
        private VisitorIpResolver $visitors,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    #[Route('', name: 'app_demo_punchout_simulator_oci', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->guard();

        return $this->page($request);
    }

    #[Route('/start', name: 'app_demo_punchout_simulator_oci_start', methods: ['POST'])]
    public function start(Request $request): Response
    {
        $this->guard();
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_START, $request->request->getString('_token')))) {
            return $this->failure($request, 'csrf', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $buyer = $this->buyers->buyer();
            $hookUrl = $this->hookUrls->withState(trim($request->request->getString('HOOK_URL')), $this->state->sign(['nonce' => bin2hex(random_bytes(16)), 'oci' => true]), $buyer);
            $result = $this->entry->post(
                $this->buyers->credentials($request->request->getBoolean('wrong_secret')),
                $hookUrl,
                $request->getHttpHost(),
                $request->getScheme(),
                $request->server->getString('SERVER_PORT', '80'),
                $this->visitors->resolve($request),
            );
        } catch (SimulatorException $exception) {
            return $this->failure($request, $exception->getMessage(), 'hook_url_not_allowed' === $exception->getMessage() ? Response::HTTP_FORBIDDEN : Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $result->isSession() ? $this->session($result) : $this->refusal($request, $result);
    }

    #[Route('/return', name: 'app_demo_punchout_simulator_oci_return', methods: ['POST'])]
    public function receive(Request $request): Response
    {
        $this->guard();
        $state = $this->state->verify($request->query->getString('state')) ?? [];
        $nonce = $state['nonce'] ?? null;
        if (!\is_string($nonce) || '' === $nonce || true !== ($state['oci'] ?? null)) {
            return $this->failure($request, 'session_expired', Response::HTTP_GONE);
        }

        try {
            $cart = $this->reader->read($request->request->all());
        } catch (SimulatorException $exception) {
            return $this->failure($request, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        if (!$this->returns->claim($nonce)) {
            return $this->failure($request, 'return_replayed', Response::HTTP_GONE);
        }

        return $this->page($request, ['cart' => $cart, 'received' => true]);
    }

    private function session(OciEntryResult $result): Response
    {
        $response = new RedirectResponse((string) $result->location);
        foreach ($result->cookies as $cookie) {
            $response->headers->setCookie($cookie);
        }
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function refusal(Request $request, OciEntryResult $result): Response
    {
        $known = \in_array($result->status, [400, 401, 403, 429], true);

        return $this->page($request, ['error' => [
            'key' => 'status_' . ($known ? $result->status : 'other'),
            'status' => $result->status,
            'host' => $request->getHost(),
        ]], 429 === $result->status ? Response::HTTP_TOO_MANY_REQUESTS : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function failure(Request $request, string $key, int $status): Response
    {
        return $this->page($request, ['error' => ['key' => $key, 'host' => $request->getHost()]], $status);
    }

    /** @param array<string, mixed> $parameters */
    private function page(Request $request, array $parameters = [], int $status = Response::HTTP_OK): Response
    {
        $lang = 'en' === $request->query->get('lang') ? 'en' : 'fr';
        $response = new Response($this->twig->render('demo/punchout_simulator/simulator.html.twig', $parameters + [
            'protocol' => 'oci',
            'locale' => $lang,
            'csrf' => $this->csrfTokenManager->getToken(self::CSRF_START)->getValue(),
            'domains' => $this->buyers->returnDomains(),
            'host' => $request->getHost(),
            'hookUrl' => $this->urlGenerator->generate('app_demo_punchout_simulator_oci_return', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]), $status);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function guard(): void
    {
        if (!$this->demoMode) {
            throw new NotFoundHttpException();
        }
    }
}
