<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * US-207: the canonical address of each indexable page in each language, on the host of DEFAULT_URI, with the
 * `hreflang` alternates (x-default is the default locale). Shared by the head of the pages and the sitemap.
 */
final readonly class ShowcaseLinks
{
    /** @param array<string, string> $locales URL locale => hreflang */
    public function __construct(
        private IndexablePages $pages,
        private UrlGeneratorInterface $urls,
        private CanonicalUrl $canonical,
        #[Autowire(param: 'demo.seo.locales')]
        private array $locales,
        #[Autowire(param: 'demo.seo.default_locale')]
        private string $defaultLocale,
    ) {
    }

    /** @return list<array{hreflang: string, url: string}> empty for a page that is not indexable */
    public function alternates(?string $route): array
    {
        if (!$this->pages->allows($route)) {
            return [];
        }

        $alternates = [];
        foreach ($this->locales as $locale => $hreflang) {
            $alternates[] = ['hreflang' => $hreflang, 'url' => $this->url((string) $route, $locale)];
        }
        $alternates[] = ['hreflang' => 'x-default', 'url' => $this->url((string) $route, $this->defaultLocale)];

        return $alternates;
    }

    /** @return list<array{loc: string, alternates: list<array{hreflang: string, url: string}>}> one entry per page and language */
    public function entries(): array
    {
        $entries = [];
        foreach ($this->pages->routes() as $route) {
            $alternates = $this->alternates($route);
            foreach (array_keys($this->locales) as $locale) {
                $entries[] = ['loc' => $this->url($route, $locale), 'alternates' => $alternates];
            }
        }

        return $entries;
    }

    private function url(string $route, string $locale): string
    {
        return $this->canonical->forPath($this->urls->generate($route, ['_locale' => $locale]));
    }
}
