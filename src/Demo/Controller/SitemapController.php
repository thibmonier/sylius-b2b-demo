<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Seo\IndexablePages;
use App\Demo\Seo\ShowcaseLinks;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * US-207: sitemap.xml is an answer of the application (never a file written at the build): it lists the indexable
 * pages of the whitelist, one entry per language with the alternates of the others, on the canonical host. It does not
 * exist while the indexing setting is off or behind the private gate.
 */
final readonly class SitemapController
{
    public function __construct(
        private IndexablePages $pages,
        private ShowcaseLinks $links,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_demo_sitemap', methods: ['GET'])]
    public function __invoke(): Response
    {
        if (!$this->pages->isOpen()) {
            throw new NotFoundHttpException();
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"/>');
        foreach ($this->links->entries() as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($entry['loc'], \ENT_XML1));
            foreach ($entry['alternates'] as $alternate) {
                $link = $url->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
                $link->addAttribute('rel', 'alternate');
                $link->addAttribute('hreflang', $alternate['hreflang']);
                $link->addAttribute('href', $alternate['url']);
            }
        }

        return new Response((string) $xml->asXML(), Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
