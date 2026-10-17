<?php

declare(strict_types=1);

namespace App\Demo\Http;

use App\Demo\Account\DemoAccountCatalog;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * US-050: the demo customer accounts are shared, their password is on /demo/accounts. Whoever is signed in with one
 * cannot change its password or its e-mail (that would lock every other visitor out); addresses, members and credit stay
 * editable, the nightly reset restores them. The refusal is a flash message and a redirect to the same page. Runs after
 * the firewall (priority 8), which sets the token.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
final readonly class SharedDemoAccountGuardListener
{
    private const PASSWORD_ROUTE = 'sylius_shop_account_change_password';

    private const PROFILE_ROUTE = 'sylius_shop_account_profile_update';

    private const PROFILE_FORM = 'sylius_shop_customer_profile';

    private const MESSAGE = 'Compte de démo partagé : son mot de passe et son e-mail ne peuvent pas être modifiés. / Shared demo account: its password and e-mail cannot be changed.';

    public function __construct(
        private TokenStorageInterface $tokens,
        private DemoAccountCatalog $catalog,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$this->demoMode || $request->isMethodSafe()) {
            return;
        }
        $email = $this->signedInEmail();
        if (null === $email || !$this->catalog->isSharedShopAccount($email) || !$this->isRefused($request, $email)) {
            return;
        }

        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('error', self::MESSAGE);
        }
        $event->setResponse(new RedirectResponse($request->getRequestUri()));
    }

    private function isRefused(Request $request, string $email): bool
    {
        return match ($request->attributes->getString('_route')) {
            self::PASSWORD_ROUTE => true,
            self::PROFILE_ROUTE => 0 !== strcasecmp($email, trim($this->submittedEmail($request))),
            default => false,
        };
    }

    private function submittedEmail(Request $request): string
    {
        $email = $request->request->all(self::PROFILE_FORM)['email'] ?? '';

        return \is_string($email) ? $email : '';
    }

    private function signedInEmail(): ?string
    {
        $user = $this->tokens->getToken()?->getUser();

        return $user instanceof ShopUserInterface ? $user->getCustomer()?->getEmail() : null;
    }
}
