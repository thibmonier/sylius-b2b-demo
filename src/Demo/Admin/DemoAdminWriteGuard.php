<?php

declare(strict_types=1);

namespace App\Demo\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Refuses, on the server side, the sensitive writes of the public back-office account (US-035).
 *
 * Runs right after the firewall (priority 8), so the user is known and nothing has reached a controller:
 * a direct POST with a valid CSRF token, a `_method` override or a bulk action meets the same wall as the form.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 7)]
final class DemoAdminWriteGuard
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * Forgotten-password routes are public: they would let anybody reset the password of the shared account.
     *
     * @var list<string>
     */
    private const PASSWORD_RESET_ROUTES = ['sylius_admin_request_password_reset', 'sylius_admin_password_reset'];

    public function __construct(
        private readonly Security $security,
        private readonly DemoAdminWritePolicy $policy,
        private readonly DemoAdminRefusal $refusal,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
        #[Autowire('/%sylius_admin.path_name%')]
        private readonly string $adminPath,
        #[Autowire('%sylius.security.api_admin_route%')]
        private readonly string $apiAdminPath,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->demoMode || !$event->isMainRequest() || !$this->isAdminArea($request)) {
            return;
        }

        $route = $request->attributes->getString('_route');
        $method = $this->unsafeMethod($request);
        if (null === $method || !$this->isRefused($request, $route, $method)) {
            return;
        }

        $event->setResponse($this->refusal->respond($request, !\in_array($route, self::PASSWORD_RESET_ROUTES, true)));
    }

    private function isRefused(Request $request, string $route, string $method): bool
    {
        if (\in_array($route, self::PASSWORD_RESET_ROUTES, true)) {
            return true;
        }

        if (!$this->security->isGranted(DemoAdminRole::NAME)) {
            return false;
        }

        return $this->isApiArea($request) || !$this->policy->allows($route, $method, $request->attributes->getString('code'));
    }

    private function isAdminArea(Request $request): bool
    {
        $path = $request->getPathInfo();

        return $this->isUnder($path, $this->adminPath) || $this->isApiArea($request);
    }

    private function isApiArea(Request $request): bool
    {
        return $this->isUnder($request->getPathInfo(), $this->apiAdminPath);
    }

    private function isUnder(string $path, string $prefix): bool
    {
        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }

    /**
     * The unsafe method of the request, or null for a read. Both the real HTTP method and the effective one
     * (after the `_method` / X-HTTP-Method-Override override) count: a POST is never taken for a read.
     */
    private function unsafeMethod(Request $request): ?string
    {
        $real = strtoupper($request->server->getString('REQUEST_METHOD', $request->getMethod()));
        $effective = $request->getMethod();

        foreach ([$effective, $real] as $method) {
            if (!\in_array($method, self::SAFE_METHODS, true)) {
                return $method;
            }
        }

        return null;
    }
}
