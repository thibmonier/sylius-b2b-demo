<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Client address behind the Railway edge (US-037, V1). Measured on the deployed demo: with a different
 * X-Forwarded-For on each request, the per-IP limits never triggered, because the edge keeps the address sent by
 * the visitor at the right of the list, where Symfony looks for the client. The edge overwrites X-Real-IP with the
 * connecting address, so X-Real-IP becomes the only forwarded address. Runs before the firewall (login throttling)
 * and the demo limiters. The loopback call of the PunchOut simulator keeps the visitor address it forwards; without
 * a usable X-Real-IP, no forwarded address is believed (the edge address is used: fail closed).
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 4096)]
final class RailwayClientIpListener
{
    private const LOOPBACK = ['127.0.0.1', '::1'];

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (\in_array($request->server->get('REMOTE_ADDR'), self::LOOPBACK, true)) {
            return;
        }

        $realIp = trim((string) $request->headers->get('X-Real-IP'));
        if (false === filter_var($realIp, \FILTER_VALIDATE_IP)) {
            $request->headers->remove('X-Forwarded-For');

            return;
        }
        $request->headers->set('X-Forwarded-For', $realIp);
    }
}
