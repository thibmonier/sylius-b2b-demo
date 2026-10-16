<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mcp;

use App\Demo\Mcp\McpApprovalGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tmonier\SyliusB2bMcpPlugin\OAuth\McpEndpoint;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\ApprovalStatus;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\VisitorApprovalInterface;

final class McpApprovalGuardTest extends TestCase
{
    public function testAPendingOrRejectedCustomerIsRefusedOnTheEndpoint(): void
    {
        foreach ([ApprovalStatus::Pending, ApprovalStatus::Rejected] as $status) {
            $event = $this->event(true, HttpKernelInterface::MAIN_REQUEST);

            $this->guard($status)($event);

            self::assertSame(403, $event->getResponse()?->getStatusCode(), $status->value);
            self::assertStringContainsString('private', (string) $event->getResponse()->headers->get('Cache-Control'));
        }
    }

    public function testAnApprovedCustomerPasses(): void
    {
        $event = $this->event(true, HttpKernelInterface::MAIN_REQUEST);

        $this->guard(null)($event);

        self::assertNull($event->getResponse());
    }

    public function testOtherRequestsAndSubRequestsAreLeftAlone(): void
    {
        foreach ([$this->event(false, HttpKernelInterface::MAIN_REQUEST), $this->event(true, HttpKernelInterface::SUB_REQUEST)] as $event) {
            $this->guard(ApprovalStatus::Pending)($event);

            self::assertNull($event->getResponse());
        }
    }

    private function guard(?ApprovalStatus $status): McpApprovalGuard
    {
        $endpoint = new McpEndpoint('/_mcp');
        $approval = $this->createStub(VisitorApprovalInterface::class);
        $approval->method('restrictedStatus')->willReturn($status);

        return new McpApprovalGuard($endpoint, $approval);
    }

    private function event(bool $mcp, int $type): RequestEvent
    {
        return new RequestEvent($this->createStub(HttpKernelInterface::class), Request::create($mcp ? '/_mcp' : '/fr_FR/'), $type);
    }
}
