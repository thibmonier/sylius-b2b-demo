<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** The description, the canonical address, the title, the language alternates and the structured data of the page, for the head of the shop layout (US-078, US-207). */
final class SeoTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly MetaDescription $description,
        private readonly CanonicalUrl $canonical,
        private readonly PageTaxon $taxon,
        private readonly ShowcaseLinks $links,
        private readonly StructuredData $structuredData,
        private readonly ShowcaseTitle $title,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_seo_description', $this->description->resolve(...)),
            new TwigFunction('app_seo_plain_text', $this->description->plain(...)),
            new TwigFunction('app_seo_taxon', $this->taxon->current(...)),
            new TwigFunction('app_seo_canonical', $this->canonical->forPath(...)),
            new TwigFunction('app_seo_alternates', $this->links->alternates(...)),
            new TwigFunction('app_seo_structured_data', $this->structuredData->json(...)),
            new TwigFunction('app_seo_title', $this->title->forRoute(...)),
        ];
    }
}
