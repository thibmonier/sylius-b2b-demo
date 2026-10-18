<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * US-078: the category a listing page shows. The Sylius page does not hand it to the layout (its breadcrumb finds it
 * by the slug of the route), so the layout reads it the same way, once per request.
 */
final class PageTaxon
{
    private ?TaxonInterface $taxon = null;

    private bool $resolved = false;

    /**
     * @param TaxonRepositoryInterface<TaxonInterface> $taxons
     */
    public function __construct(
        private readonly RequestStack $requests,
        #[Autowire(service: 'sylius.repository.taxon')]
        private readonly TaxonRepositoryInterface $taxons,
    ) {
    }

    public function current(): ?TaxonInterface
    {
        if ($this->resolved) {
            return $this->taxon;
        }

        $request = $this->requests->getMainRequest();
        $slug = $request?->attributes->get('slug');
        if ('sylius_shop_product_index' === $request?->attributes->get('_route') && \is_string($slug)) {
            $this->taxon = $this->taxons->findOneBySlug($slug, $request->getLocale());
        }
        $this->resolved = true;

        return $this->taxon;
    }
}
