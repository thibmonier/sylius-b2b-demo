<?php

declare(strict_types=1);

namespace App\Demo\Http;

use App\Demo\Analytics\UmamiTracker;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Security headers on every response of the application (shop, admin, errors, redirects, API). The CSP
 * is the compatible flavour: the Stimulus controllers and the admin keep their inline scripts and styles,
 * everything else is restricted to the origin. HSTS is only sent when the effective request is HTTPS.
 * Umami (US-143): when configured, its host is added to `script-src` and `connect-src` on the measured pages only.
 * Pages vary on the cookies (channel, badges, session): `Vary: Cookie` keeps any cache added later honest.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -255)]
final class SecurityHeadersSubscriber
{
    public const CSP = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";

    private const HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        // US-082. No COEP: every subresource is already same-origin (CSP) and nothing needs cross-origin isolation.
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ];

    public function __construct(private readonly UmamiTracker $umami)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $headers = $event->getResponse()->headers;
        $headers->set('Content-Security-Policy', $this->policyFor($event->getRequest()->getPathInfo()));
        foreach (self::HEADERS as $name => $value) {
            $headers->set($name, $value);
        }
        $event->getResponse()->setVary('Cookie', false);
        if ($event->getRequest()->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }

    private function policyFor(string $path): string
    {
        $origin = $this->umami->origin();
        if (null === $origin || !$this->umami->measures($path)) {
            return self::CSP;
        }

        return str_replace(["script-src 'self'", "connect-src 'self'"], ["script-src 'self' " . $origin, "connect-src 'self' " . $origin], self::CSP);
    }
}
