<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Tmonier\SyliusB2bMcpPlugin\OAuth\McpEndpoint;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\VisitorApprovalInterface;

/**
 * US-020 — An AI assistant has the access of the customer it is connected to. The MCP plugin only checks that the
 * account is enabled; a customer whose registration is pending or rejected is turned away here, with the access
 * tokens he got before (after the MCP authentication has set the security token, priority 7).
 */
#[AsEventListener(event: 'kernel.request', priority: 6)]
final readonly class McpApprovalGuard
{
    public function __construct(
        #[Autowire(service: 'tmonier_b2b_mcp.endpoint')]
        private McpEndpoint $endpoint,
        private VisitorApprovalInterface $approval,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->endpoint->isEndpoint($event->getRequest()) || null === $this->approval->restrictedStatus()) {
            return;
        }

        $event->setResponse(new JsonResponse(
            ['error' => 'access_denied', 'error_description' => 'The registration of this account is not approved.'],
            Response::HTTP_FORBIDDEN,
            ['Cache-Control' => 'private, no-store'],
        ));
    }
}
