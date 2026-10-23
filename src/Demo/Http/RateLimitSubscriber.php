<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Per-visitor rate limits of the public demo (US-036): login, demo pages, registration and public forms,
 * PunchOut simulator, exports, guided tours, equipment fleet, API token and sign-up endpoints, MCP, health probes. The key is the visitor IP resolved through the trusted proxies, never the proxy.
 * A refused request answers 429 with Retry-After. Runs after the router (route name known) and before
 * the firewall (a login flood never reaches the authenticators).
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final readonly class RateLimitSubscriber
{
    /** Route name => [HTTP method (null = any), limiter]. */
    private const RULES = [
        'sylius_shop_login_check' => ['POST', 'login'],
        'sylius_shop_json_login_check' => ['POST', 'login'],
        'sylius_admin_login_check' => ['POST', 'login'],
        'app_demo_health' => ['GET', 'health'],
        'app_demo_healthz' => ['GET', 'health'],
        'app_demo_accounts' => [null, 'public'],
        'app_demo_mails' => [null, 'public'],
        'app_demo_mail' => [null, 'public'],
        'sylius_shop_register' => ['POST', 'forms'],
        'sylius_shop_request_password_reset_token' => ['POST', 'forms'],
        'sylius_admin_request_password_reset' => ['POST', 'forms'],
        'sylius_shop_user_request_verification_token' => ['POST', 'forms'],
        'sylius_shop_contact_request' => ['POST', 'forms'],
        'tmonier_quote_request_shop_new' => ['POST', 'forms'],
        'sylius_shop_product_review_create' => ['POST', 'forms'],
        'sylius_api_shop_authentication_token' => ['POST', 'api_auth'],
        'sylius_api_admin_authentication_token' => ['POST', 'api_auth'],
        'tmonier_b2b_mcp_oauth_token' => ['POST', 'api_auth'],
        'sylius_api_shop_customer_post' => ['POST', 'api_write'],
        'sylius_api_shop_order_post' => ['POST', 'api_write'],
        'tmonier_b2b_mcp_oauth_register' => ['POST', 'api_write'],
        '_mcp_endpoint' => ['POST', 'mcp'],
        'app_demo_punchout_simulator_start' => ['POST', 'punchout_start'],
        'app_demo_punchout_simulator_order' => ['POST', 'punchout_order'],
        'app_demo_punchout_simulator_oci_start' => ['POST', 'punchout_start'],
        'app_demo_punchout_simulator_oci_return' => ['POST', 'punchout_oci_return'],
        'tmonier_company_insights_shop_export' => ['GET', 'export'],
        'app_demo_journey_advance' => ['POST', 'journey'],
        'app_demo_journey_launch' => ['POST', 'journey'],
        'tmonier_equipment_shop_fleet_create' => ['POST', 'fleet'],
        'tmonier_equipment_shop_fleet_update' => ['POST', 'fleet'],
        'tmonier_equipment_shop_fleet_delete' => ['POST', 'fleet'],
    ];

    public function __construct(
        private VisitorIpResolver $visitors,
        private RateLimiterFactoryInterface $demoLoginLimiter,
        private RateLimiterFactoryInterface $demoPublicLimiter,
        private RateLimiterFactoryInterface $demoFormsLimiter,
        private RateLimiterFactoryInterface $demoPunchoutStartLimiter,
        private RateLimiterFactoryInterface $demoPunchoutOrderLimiter,
        private RateLimiterFactoryInterface $demoPunchoutOciReturnLimiter,
        private RateLimiterFactoryInterface $demoApiAuthLimiter,
        private RateLimiterFactoryInterface $demoApiWriteLimiter,
        private RateLimiterFactoryInterface $demoMcpLimiter,
        private RateLimiterFactoryInterface $demoHealthLimiter,
        private RateLimiterFactoryInterface $demoExportLimiter,
        private RateLimiterFactoryInterface $demoJourneyLimiter,
        private RateLimiterFactoryInterface $demoFleetLimiter,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $rule = self::RULES[$request->attributes->getString('_route')] ?? null;
        if (!$event->isMainRequest() || !$this->demoMode || null === $rule || (null !== $rule[0] && $rule[0] !== $request->getMethod())) {
            return;
        }

        $limit = $this->factory($rule[1])->create($this->visitors->resolve($request))->consume();
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

    private function factory(string $name): RateLimiterFactoryInterface
    {
        return match ($name) {
            'login' => $this->demoLoginLimiter,
            'public' => $this->demoPublicLimiter,
            'forms' => $this->demoFormsLimiter,
            'punchout_start' => $this->demoPunchoutStartLimiter,
            'punchout_oci_return' => $this->demoPunchoutOciReturnLimiter,
            'api_auth' => $this->demoApiAuthLimiter,
            'api_write' => $this->demoApiWriteLimiter,
            'mcp' => $this->demoMcpLimiter,
            'health' => $this->demoHealthLimiter,
            'export' => $this->demoExportLimiter,
            'journey' => $this->demoJourneyLimiter,
            'fleet' => $this->demoFleetLimiter,
            default => $this->demoPunchoutOrderLimiter,
        };
    }
}
