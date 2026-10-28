<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-207: the one list of the indexable pages (config/demo/seo.yaml), read by the noindex header, robots.txt and the
 * sitemap. Nothing is indexable outside the demo mode, behind the private gate, or while the indexing setting is off;
 * a route that is not in the list is refused (noindex), including a route added later.
 */
final readonly class IndexablePages
{
    /** @param list<string> $routes */
    public function __construct(
        #[Autowire(param: 'demo.seo.indexable_routes')]
        private array $routes,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.basic_auth.enabled')]
        private bool $gateClosed,
        #[Autowire(param: 'demo.indexing.enabled')]
        private bool $indexing,
    ) {
    }

    public function isOpen(): bool
    {
        return $this->demoMode && !$this->gateClosed && $this->indexing;
    }

    public function allows(?string $route): bool
    {
        return $this->isOpen() && null !== $route && \in_array($route, $this->routes, true);
    }

    /** @return list<string> */
    public function routes(): array
    {
        return $this->routes;
    }
}
