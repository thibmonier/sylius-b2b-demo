<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * US-198: the sort of the product list is a plain field of the toolbar form (`sort=price-asc`), which works without JavaScript. The grid of the
 * core reads `sorting[price]=asc`: this turns the first into the second, once the route is known, so that the pagination links keep the core form.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final class ListSortParameter
{
    public const ROUTE = 'sylius_shop_product_index';

    /** @var array<string, array<string, string>> */
    public const CHOICES = [
        'name-asc' => ['name' => 'asc'],
        'name-desc' => ['name' => 'desc'],
        'price-asc' => ['price' => 'asc'],
        'price-desc' => ['price' => 'desc'],
        'newest' => ['createdAt' => 'desc'],
    ];

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || self::ROUTE !== $request->attributes->get('_route') || !$request->query->has('sort')) {
            return;
        }

        $choice = $request->query->all()['sort'] ?? null;
        $request->query->remove('sort');
        if (\is_string($choice) && isset(self::CHOICES[$choice])) {
            $request->query->set('sorting', self::CHOICES[$choice]);
        }
    }
}
