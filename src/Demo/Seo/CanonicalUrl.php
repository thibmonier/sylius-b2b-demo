<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-078: the canonical address of a page, on the host of DEFAULT_URI (the Railway domain, then the public
 * sub-domain once CANONICAL_IS_PUBLIC_HOST is on) whatever host the visitor came by, keeping only the page number of a listing.
 */
final readonly class CanonicalUrl
{
    public function __construct(
        #[Autowire(env: 'DEFAULT_URI')]
        private string $defaultUri,
    ) {
    }

    /** The page number of a listing is kept (page 2 is not page 1); every other parameter (sorting, filters) is dropped. */
    public function forPath(string $path, mixed $page = null): string
    {
        $url = rtrim($this->defaultUri, '/') . '/' . ltrim($path, '/');

        return \is_string($page) && 1 === preg_match('/^[2-9]\d*$/', $page) ? $url . '?page=' . $page : $url;
    }
}
