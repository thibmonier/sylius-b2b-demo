<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * US-207: the search title of an indexable page (translation `title.<route>` of the `seo` domain), written for the
 * intent of the search. Null for every other page, which keeps the title of its own template.
 */
final readonly class ShowcaseTitle
{
    private const DOMAIN = 'seo';

    public function __construct(
        private IndexablePages $pages,
        private TranslatorInterface $translator,
    ) {
    }

    public function forRoute(?string $route): ?string
    {
        if (!$this->pages->allows($route)) {
            return null;
        }

        $id = 'title.' . $route;
        $title = $this->translator->trans($id, [], self::DOMAIN);

        return $title === $id ? null : $title;
    }
}
