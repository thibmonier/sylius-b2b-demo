<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * US-050: one ceiling per visitor IP on every PHP request of the demo (search, cart, image resize, CSV upload, PDF...),
 * on top of the specific limits of RateLimitSubscriber. The healthchecks, the signed webhooks and the internal calls of
 * the PunchOut simulator are out of it. The key is the visitor IP, which hangs on the header set by the Railway edge
 * (RailwayClientIpListener).
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
final readonly class ShopRateLimitListener
{
    public function __construct(
        private VisitorIpResolver $visitors,
        private InternalRequestToken $internalToken,
        private RateLimiterFactory $demoShopLimiter,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$this->demoMode || $this->isExempt($request)) {
            return;
        }

        $limit = $this->demoShopLimiter->create($this->visitors->resolve($request))->consume();
        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = max(1, $limit->getRetryAfter()->getTimestamp() - time());
        $event->setResponse(new Response(
            'Too many requests. Please try again later / Trop de requêtes, réessayez plus tard.',
            Response::HTTP_TOO_MANY_REQUESTS,
            ['Content-Type' => 'text/plain; charset=UTF-8', 'Retry-After' => (string) $retryAfter, 'Cache-Control' => 'no-store'],
        ));
    }

    private function isExempt(Request $request): bool
    {
        $path = $request->getPathInfo();

        return \in_array($path, DemoGateListener::OPEN_PATHS, true) ||
            \in_array($path, DemoGateListener::SIGNED_PATHS, true) ||
            $this->internalToken->isValid($request);
    }
}
