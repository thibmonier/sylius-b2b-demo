<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** The description and the canonical address of the page, for the head of the shop layout (US-078). */
final class SeoTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly MetaDescription $description,
        private readonly CanonicalUrl $canonical,
        private readonly PageTaxon $taxon,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_seo_description', $this->description->resolve(...)),
            new TwigFunction('app_seo_plain_text', $this->description->plain(...)),
            new TwigFunction('app_seo_taxon', $this->taxon->current(...)),
            new TwigFunction('app_seo_canonical', $this->canonical->forPath(...)),
        ];
    }
}
