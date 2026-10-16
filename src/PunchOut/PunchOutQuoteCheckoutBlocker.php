<?php

declare(strict_types=1);

namespace App\PunchOut;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Session\CurrentPunchoutSession;

/**
 * Fix I2: the PunchOut plugin sends the checkout routes back to the cart during a session, but not the
 * quote checkout (`/quotes/{token}/checkout`), which swaps the PunchOut cart for the quote cart and leads
 * to a Sylius order. The same redirect and flash message as the plugin's CheckoutBlockerListener apply.
 *
 * Quick-order only adds lines to the current cart: the order itself goes through the (blocked) checkout.
 * To be moved to the quote-request plugin (or the PunchOut one) when patched upstream.
 */
final readonly class PunchOutQuoteCheckoutBlocker implements EventSubscriberInterface
{
    public const BLOCKED_ROUTE = 'tmonier_quote_request_shop_checkout';

    private const FLASH_MESSAGE = 'tmonier_punchout.ui.session.checkout_blocked';

    public function __construct(
        private CurrentPunchoutSession $currentPunchoutSession,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /** Priority 4 like the plugin listener: after the firewall (8) and the router (32). */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 4]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || self::BLOCKED_ROUTE !== $request->attributes->getString('_route')) {
            return;
        }
        if (null === $this->currentPunchoutSession->getCurrent()) {
            return;
        }

        $session = $request->hasSession() ? $request->getSession() : null;
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('info', self::FLASH_MESSAGE);
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('sylius_shop_cart_summary', ['_locale' => $request->getLocale()])));
    }
}
