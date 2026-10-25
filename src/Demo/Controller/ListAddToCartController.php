<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tmonier\SyliusQuickOrderPlugin\Adder\BulkAddMode;
use Tmonier\SyliusQuickOrderPlugin\Adder\BulkCartAdderInterface;
use Tmonier\SyliusQuickOrderPlugin\Input\QuickOrderInput;
use Tmonier\SyliusQuickOrderPlugin\Input\QuickOrderLine;

/**
 * "Add to cart" of a card of the product list (US-198): one reference, a quantity, back to the list. The cart is filled by the adder of the
 * quick order plugin (stock, price, locked cart, limits: the same checks as a pasted list), not by a second implementation.
 */
final readonly class ListAddToCartController
{
    public const CSRF_ID = 'b2b_list_add';

    private const MAX_QUANTITY = 999;

    public function __construct(
        private CartContextInterface $cartContext,
        private BulkCartAdderInterface $adder,
        private CsrfTokenManagerInterface $csrf,
        private UrlGeneratorInterface $urls,
        private AuthorizationCheckerInterface $security,
    ) {
    }

    #[Route('/{_locale}/cart/add-from-list', name: 'app_b2b_list_add', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('TMONIER_CHANNEL_ACCESS_SEE_PRICES')) {
            return new RedirectResponse($this->urls->generate('sylius_shop_login'));
        }

        $back = new RedirectResponse($this->returnPath($request));
        if (!$this->csrf->isTokenValid(new CsrfToken(self::CSRF_ID, (string) $request->request->get('_csrf_token')))) {
            $this->flash($request, 'error', 'app.list.invalid_token');

            return $back;
        }

        $code = trim((string) $request->request->get('code'));
        $quantity = max(1, min(self::MAX_QUANTITY, (int) $request->request->get('quantity', 1)));
        $cart = $this->cartContext->getCart();
        if (!$cart instanceof OrderInterface) {
            $this->flash($request, 'error', 'app.list.not_added');

            return $back;
        }
        $result = $this->adder->add($cart, new QuickOrderInput([new QuickOrderLine($code, $quantity, [1])]), BulkAddMode::Partial);
        if ($result->hasErrors() || 1 > $result->countAdded()) {
            $this->flash($request, 'error', 'app.list.not_added');

            return $back;
        }
        $this->flash($request, 'success', 'app.list.added');

        return $back;
    }

    /** Only a path of the site: a full URL or a protocol-relative one is never followed. */
    private function returnPath(Request $request): string
    {
        $path = (string) $request->request->get('return');
        if (1 === preg_match('#^/(?!/)[^\\\\\r\n]*$#', $path)) {
            return $path;
        }

        return $this->urls->generate('sylius_shop_cart_summary');
    }

    private function flash(Request $request, string $type, string $message): void
    {
        $session = $request->hasSession() ? $request->getSession() : null;
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
