<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * US-050: with the registrations closed (DEMO_REGISTRATION_OPEN=0) a guest cannot go through the address step of the
 * checkout nor post a product review either: that is where a visitor types a name, an e-mail (and an address) that
 * become a customer in Sophie's list. A signed-in customer is not concerned. Runs after the firewall (priority 8), which sets the token.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
final readonly class GuestCheckoutGateListener
{
    /** The address step and the product review: both turn a typed name and e-mail into a guest customer. */
    private const ROUTES = ['sylius_shop_checkout_address', 'sylius_shop_product_review_create'];

    public function __construct(
        private TokenStorageInterface $tokens,
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
        if (null !== $this->tokens->getToken()?->getUser()) {
            return;
        }

        $event->setResponse(RegistrationClosedResponse::create());
    }
}
