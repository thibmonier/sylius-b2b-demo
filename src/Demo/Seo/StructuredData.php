<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Plugin\DemoPluginCatalog;
use App\Demo\Site\SiteLinks;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * US-207: the structured data (JSON-LD) of the indexed pages. Only verifiable facts: the public repositories of the
 * catalogue (the plugins it flags out are left out) and the author with the profile already public. No rating, review
 * or price. The block is data, not a script: it has no effect on the content security policy.
 */
final readonly class StructuredData
{
    private const DOMAIN = 'seo';

    public function __construct(
        private IndexablePages $pages,
        private DemoPluginCatalog $plugins,
        private SiteLinks $site,
        private CanonicalUrl $canonical,
        private TranslatorInterface $translator,
        #[Autowire(param: 'demo.seo.author_name')]
        private string $authorName,
        #[Autowire(param: 'demo.seo.demo_repository')]
        private string $demoRepository,
    ) {
    }

    /** @return string the JSON of the block, or '' for a page without structured data */
    public function json(?string $route, string $path, string $locale): string
    {
        if (!$this->pages->allows($route)) {
            return '';
        }

        $nodes = match ($route) {
            'sylius_shop_homepage' => [$this->website($path, $locale), $this->person()],
            'app_demo_about' => [$this->person()],
            'app_demo_plugins' => array_map(fn (DemoPlugin $plugin): array => $this->pluginRepository($plugin, $locale), $this->plugins->described()),
            'app_demo_architecture' => [$this->demoRepository($locale)],
            default => [],
        };
        if ([] === $nodes) {
            return '';
        }

        return json_encode(['@context' => 'https://schema.org', '@graph' => $nodes], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_HEX_TAG | \JSON_HEX_AMP);
    }

    /** @return array<string, mixed> */
    private function website(string $path, string $locale): array
    {
        return [
            '@type' => 'WebSite',
            'name' => $this->translator->trans('structured.site_name', [], self::DOMAIN, $locale),
            'url' => $this->canonical->forPath($path),
            'inLanguage' => str_replace('_', '-', $locale),
            'description' => $this->translator->trans('description.sylius_shop_homepage', ['%name%' => ''], self::DOMAIN, $locale),
        ];
    }

    /** @return array<string, mixed> */
    private function person(): array
    {
        $person = ['@type' => 'Person', 'name' => $this->authorName];
        if (null !== $this->site->siteUrl()) {
            $person['url'] = $this->site->siteUrl();
        }
        if (null !== $this->site->profileUrl()) {
            $person['sameAs'] = $this->site->profileUrl();
        }

        return $person;
    }

    /** @return array<string, mixed> */
    private function pluginRepository(DemoPlugin $plugin, string $locale): array
    {
        return [
            '@type' => 'SoftwareSourceCode',
            'name' => $plugin->name,
            'description' => $plugin->description($locale),
            'codeRepository' => $plugin->githubUrl(),
            'programmingLanguage' => 'PHP',
            'author' => ['@type' => 'Person', 'name' => $this->authorName],
        ];
    }

    /** @return array<string, mixed> */
    private function demoRepository(string $locale): array
    {
        return [
            '@type' => 'SoftwareSourceCode',
            'name' => $this->translator->trans('structured.demo_name', [], self::DOMAIN, $locale),
            'description' => $this->translator->trans('description.app_demo_architecture', [], self::DOMAIN, $locale),
            'codeRepository' => $this->demoRepository,
            'programmingLanguage' => 'PHP',
            'author' => ['@type' => 'Person', 'name' => $this->authorName],
        ];
    }
}
