<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Closes the self-service registrations (US-040, M2). Every visitor reads the customer list of the shared
 * back-office account, so a stranger's personal data must not be able to get in once the demo is public:
 * with DEMO_REGISTRATION_OPEN=0 the shop form and the shop API refuse to create a customer. Open by default,
 * because the private demo shows the registration approval (US-020).
 *
 * Runs after the router (route name known) and before the firewall.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final readonly class RegistrationGateListener
{
    /** @var list<string> */
    private const ROUTES = ['sylius_shop_register', 'sylius_shop_register_after_checkout', 'sylius_api_shop_customer_post'];

    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.registration_open')]
        private bool $registrationOpen,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->demoMode || $this->registrationOpen) {
            return;
        }
        if (!\in_array($event->getRequest()->attributes->getString('_route'), self::ROUTES, true)) {
            return;
        }

        $event->setResponse(new Response(
            'Registrations are closed on this demo. / Les inscriptions sont fermées sur cette démo.',
            Response::HTTP_FORBIDDEN,
            ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store'],
        ));
    }
}
