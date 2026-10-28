<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Seo\CanonicalUrl;
use App\Demo\Seo\IndexablePages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * US-050: robots.txt depends on the mode, so it is an answer of the application, not a file in public/ (Caddy would
 * serve the file before PHP). By default, in the demo mode, the whole site is disallowed (the `X-Robots-Tag` header of
 * NoIndexSubscriber covers the crawlers that read it anyway). US-207: once the indexing setting is on, only the zones
 * without interest to crawl are disallowed (what must leave the index passes by the header, which a crawler must be
 * able to read) and the sitemap is announced. Outside the demo mode nothing is disallowed. In the private mode the gate
 * answers first, so only the holder of the password sees this, and the whole site stays disallowed.
 */
final readonly class RobotsController
{
    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        private IndexablePages $pages,
        private CanonicalUrl $canonical,
        #[Autowire(param: 'sylius_admin.path_name')]
        private string $adminPath,
    ) {
    }

    #[Route('/robots.txt', name: 'app_demo_robots', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->body(), Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function body(): string
    {
        if (!$this->demoMode) {
            return "User-agent: *\nDisallow:\n";
        }
        if (!$this->pages->isOpen()) {
            return "User-agent: *\nDisallow: /\n";
        }

        $zones = ['/' . $this->adminPath, '/demo', '/api', '/*/cart', '/*/checkout', '/*/account', '/*?*sorting'];

        return "User-agent: *\n" . implode('', array_map(static fn (string $zone): string => 'Disallow: ' . $zone . "\n", $zones))
            . "\nSitemap: " . $this->canonical->forPath('/sitemap.xml') . "\n";
    }
}
