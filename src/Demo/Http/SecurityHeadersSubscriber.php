<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Security headers on every response of the application (shop, admin, errors, redirects, API). The CSP
 * is the compatible flavour: the Stimulus controllers and the admin keep their inline scripts and styles,
 * everything else is restricted to the origin. HSTS is only sent when the effective request is HTTPS.
 * Pages vary on the cookies (channel, badges, session): `Vary: Cookie` keeps any cache added later honest.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -255)]
final class SecurityHeadersSubscriber
{
    public const CSP = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";

    private const HEADERS = [
        'Content-Security-Policy' => self::CSP,
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ];

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $headers = $event->getResponse()->headers;
        foreach (self::HEADERS as $name => $value) {
            $headers->set($name, $value);
        }
        $event->getResponse()->setVary('Cookie', false);
        if ($event->getRequest()->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
