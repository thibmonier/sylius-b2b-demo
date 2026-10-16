<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A Host header outside TRUSTED_HOSTS answers a plain 400 before anything else runs. Left to the error
 * pages, the rejected host makes the layout (which reads the host again) fail inside the error page
 * itself, in a loop that exhausts the memory.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 1024)]
final class UntrustedHostListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        try {
            $event->getRequest()->getHost();
        } catch (SuspiciousOperationException) {
            $event->setResponse(new Response('Bad request.', Response::HTTP_BAD_REQUEST, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']));
        }
    }
}
