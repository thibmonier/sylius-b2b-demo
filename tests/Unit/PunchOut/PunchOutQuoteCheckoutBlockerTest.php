<?php

declare(strict_types=1);

namespace App\Tests\Unit\PunchOut;

use App\PunchOut\PunchOutQuoteCheckoutBlocker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Session\CurrentPunchoutSession;
use Tmonier\SyliusPunchoutCxmlPlugin\Session\PunchoutSession;

/** Fix I2: no quote checkout (cart swap leading to a Sylius order) during a PunchOut session. */
final class PunchOutQuoteCheckoutBlockerTest extends TestCase
{
    public function testQuoteCheckoutIsRedirectedToTheCartDuringAPunchOutSession(): void
    {
        $event = $this->dispatch(PunchOutQuoteCheckoutBlocker::BLOCKED_ROUTE, true);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertSame('/cart', $event->getResponse()->headers->get('Location'));
        $session = $event->getRequest()->getSession();
        self::assertInstanceOf(Session::class, $session);
        self::assertSame(['info' => ['tmonier_punchout.ui.session.checkout_blocked']], $session->getFlashBag()->all());
    }

    public function testQuoteCheckoutIsAllowedOutsideAPunchOutSession(): void
    {
        self::assertNull($this->dispatch(PunchOutQuoteCheckoutBlocker::BLOCKED_ROUTE, false)->getResponse());
    }

    public function testOtherRoutesAreLeftToThePlugin(): void
    {
        self::assertNull($this->dispatch('tmonier_quote_request_shop_show_by_token', true)->getResponse());
        self::assertNull($this->dispatch('tmonier_quick_order_shop_add', true)->getResponse());
    }

    public function testSubRequestsAreIgnored(): void
    {
        self::assertNull($this->dispatch(PunchOutQuoteCheckoutBlocker::BLOCKED_ROUTE, true, HttpKernelInterface::SUB_REQUEST)->getResponse());
    }

    public function testListensBeforeTheCheckoutResolverLikeThePluginListener(): void
    {
        self::assertSame(['onKernelRequest', 4], PunchOutQuoteCheckoutBlocker::getSubscribedEvents()['kernel.request']);
    }

    private function dispatch(string $route, bool $punchOutSessionActive, int $type = HttpKernelInterface::MAIN_REQUEST): RequestEvent
    {
        $customer = new Customer();
        $user = new ShopUser();
        $user->setCustomer($customer);
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'shop', []));

        $buyer = new Buyer();
        $buyer->setEnabled(true);
        $buyer->setCustomer($customer);
        $punchOutSession = new PunchoutSession();
        $punchOutSession->setStatus(PunchoutSession::STATUS_ACTIVE);
        $punchOutSession->setBuyer($buyer);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('find')->willReturn($punchOutSession);

        $session = new Session(new MockArraySessionStorage());
        if ($punchOutSessionActive) {
            $session->set(CurrentPunchoutSession::SESSION_KEY, 1);
        }
        $request = new Request();
        $request->attributes->set('_route', $route);
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/cart');

        $event = new RequestEvent($this->createStub(HttpKernelInterface::class), $request, $type);
        (new PunchOutQuoteCheckoutBlocker(new CurrentPunchoutSession($requestStack, $entityManager, $tokenStorage), $urlGenerator))->onKernelRequest($event);

        return $event;
    }
}
