<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The shared secret of the demo buyers is published, so on the public demo the PunchOut entries
 * (POST /punchout/cxml and the OCI entry POST /punchout/oci/<code>) only answer to the simulator, which proves its
 * origin with the internal token.
 * DEMO_PUNCHOUT_PUBLIC_ENDPOINT=1 opens it to every client again (private tests, plugin development).
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final readonly class PunchOutEndpointGuard
{
    private const ROUTES = ['tmonier_punchout_cxml_endpoint', 'tmonier_punchout_oci_entry'];

    public function __construct(
        private InternalRequestToken $internalToken,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.punchout.public_endpoint')]
        private bool $publicEndpoint,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$this->demoMode || $this->publicEndpoint || !\in_array($request->attributes->get('_route'), self::ROUTES, true) || $this->internalToken->isValid($request)) {
            return;
        }

        $event->setResponse(new Response('The PunchOut endpoint is reserved to the demo simulator on this public demo.', Response::HTTP_FORBIDDEN, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']));
    }
}
