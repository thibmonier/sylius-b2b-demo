<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * US-050: the demo is public but not promoted, and holds no real data: no search engine should list it. Every answer
 * of the demo mode carries the header, the refusals of the gate included.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -255)]
final readonly class NoIndexSubscriber
{
    public const VALUE = 'noindex, nofollow';

    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if ($this->demoMode && $event->isMainRequest()) {
            $event->getResponse()->headers->set('X-Robots-Tag', self::VALUE);
        }
    }
}
