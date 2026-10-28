<?php

declare(strict_types=1);

namespace App\Demo\Http;

use App\Demo\Channel\DemoChannelContext;
use App\Demo\Seo\IndexablePages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * US-050: the demo holds no real data, so by default no search engine should list it: every answer of the demo mode
 * carries the header, the refusals of the gate included. US-207: the showcase pages of the whitelist (IndexablePages)
 * are the only exception, once the indexing setting is on; the consumer channel (cookie) and every other route,
 * including a route added later, keep the header.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -255)]
final readonly class NoIndexSubscriber
{
    public const VALUE = 'noindex, nofollow';

    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        private IndexablePages $pages,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$this->demoMode || !$event->isMainRequest() || $this->isIndexable($event->getRequest())) {
            return;
        }

        $event->getResponse()->headers->set('X-Robots-Tag', self::VALUE);
    }

    private function isIndexable(Request $request): bool
    {
        $route = $request->attributes->get('_route');
        $consumer = DemoChannelContext::B2C === DemoChannelContext::codeFrom($request->cookies->get(DemoChannelContext::COOKIE));

        return !$consumer && \is_string($route) && $this->pages->allows($route);
    }
}
