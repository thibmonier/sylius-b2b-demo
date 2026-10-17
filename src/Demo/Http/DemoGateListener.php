<?php

declare(strict_types=1);

namespace App\Demo\Http;

use App\Demo\Webhook\DemoWebhookTarget;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Private demo (US-032): before anything else, a request is refused unless it carries valid basic
 * authentication credentials or the internal token of the simulator. The healthchecks stay open. When the
 * demo is misconfigured (gate without credentials, external mailer) nothing is served: fail closed.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 512)]
final readonly class DemoGateListener
{
    public const OPEN_PATHS = ['/health', '/healthz'];

    /** Exact paths that skip the basic auth because the request proves itself otherwise (US-029: Standard Webhooks signature). */
    public const SIGNED_PATHS = [DemoWebhookTarget::PATH];

    public function __construct(
        private BasicAuthGate $gate,
        private DemoConfigurationGuard $guard,
        private InternalRequestToken $internalToken,
        private VisitorIpResolver $visitors,
        private RateLimiterFactory $demoGateLimiter,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$this->demoMode || \in_array($request->getPathInfo(), self::OPEN_PATHS, true)) {
            return;
        }
        if ([] !== $this->guard->violations()) {
            $event->setResponse($this->plain('Service unavailable: the demo is not configured safely.', Response::HTTP_SERVICE_UNAVAILABLE));

            return;
        }
        if (!$this->gate->isActive() || $this->internalToken->isValid($request) || $this->isSignedRequest($request)) {
            return;
        }

        $response = $this->challenge($request);
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /** The receiver of the webhooks verifies the signature itself; only a POST on the exact path skips the gate. */
    private function isSignedRequest(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST) && \in_array($request->getPathInfo(), self::SIGNED_PATHS, true);
    }

    private function challenge(Request $request): ?Response
    {
        $limiter = $this->demoGateLimiter->create($this->visitors->resolve($request));
        if ($this->gate->hasCredentials($request)) {
            // Peek without consuming: only wrong passwords count, and the limit is the number of failures allowed.
            if ($limiter->consume(0)->getRemainingTokens() < 1) {
                return $this->plain('Too many attempts.', Response::HTTP_TOO_MANY_REQUESTS);
            }
            if ($this->gate->authorizes($request)) {
                return null;
            }
            $limiter->consume();
        }

        $response = $this->plain('Authentication required.', Response::HTTP_UNAUTHORIZED);
        $response->headers->set('WWW-Authenticate', 'Basic realm="Demo", charset="UTF-8"');

        return $response;
    }

    private function plain(string $body, int $status): Response
    {
        return new Response($body, $status, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
