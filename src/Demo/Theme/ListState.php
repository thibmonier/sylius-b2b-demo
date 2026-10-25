<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * What the toolbar of the product list reads from the request (US-198), validated once: the search typed, the display (grid or list) and
 * the sort chosen. Anything else in the query string is ignored, never echoed.
 */
final readonly class ListState
{
    public const GRID = 'grid';

    public const LIST = 'list';

    private const MAX_SEARCH_LENGTH = 100;

    public function __construct(private RequestStack $requests)
    {
    }

    public function search(): string
    {
        $criteria = $this->query()['criteria'] ?? null;
        $search = \is_array($criteria) && \is_array($criteria['search'] ?? null) ? ($criteria['search']['value'] ?? null) : null;

        return \is_string($search) ? mb_substr(trim($search), 0, self::MAX_SEARCH_LENGTH) : '';
    }

    public function view(): string
    {
        return self::LIST === ($this->query()['view'] ?? null) ? self::LIST : self::GRID;
    }

    /** The key of ListSortParameter::CHOICES that matches the sorting of the request, or '' (the order of the category). */
    public function sortChoice(): string
    {
        $sorting = $this->query()['sorting'] ?? null;
        if (!\is_array($sorting)) {
            return '';
        }
        foreach (ListSortParameter::CHOICES as $choice => $expected) {
            if ($sorting === $expected) {
                return $choice;
            }
        }

        return '';
    }

    /** @return array<string, mixed> */
    private function query(): array
    {
        return $this->requests->getMainRequest()?->query->all() ?? [];
    }
}
