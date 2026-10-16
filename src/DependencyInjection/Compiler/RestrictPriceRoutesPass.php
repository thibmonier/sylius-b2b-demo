<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * US-019 — Adds the routes of the other plugins that show or use prices to the routes the channel access plugin
 * refuses to a visitor who may not see prices (on top of the cart, the checkout and the orders). A signed-in
 * customer still waiting for approval (US-020) is turned away from them the same way.
 *
 * The plugin has no configuration key for this yet, hence the parameters it builds its matcher from.
 */
final class RestrictPriceRoutesPass implements CompilerPassInterface
{
    /** @var list<string> routes that give prices or prepare a priced document */
    public const ROUTES = [
        // Quote request form: its lines show the catalogue price of the products.
        'tmonier_quote_request_shop_new',
        'tmonier_quote_request_shop_basket_add',
        'tmonier_quote_request_shop_from_cart',
        'tmonier_quote_request_shop_release_cart',
    ];

    /** @var list<string> */
    public const PATHS = [
        // Volume prices of a variant (tmonier/sylius-tier-pricing-plugin): unit price per quantity break.
        '%sylius.security.api_shop_route%/product-variants/*/tier-prices',
    ];

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('tmonier_channel_access.restricted_routes')) {
            return;
        }

        $container->setParameter('tmonier_channel_access.restricted_routes', array_values(array_unique([...$this->listOf($container, 'tmonier_channel_access.restricted_routes'), ...self::ROUTES])));
        $container->setParameter('tmonier_channel_access.restricted_paths', array_values(array_unique([...$this->listOf($container, 'tmonier_channel_access.restricted_paths'), ...self::PATHS])));
    }

    /** @return list<string> */
    private function listOf(ContainerBuilder $container, string $parameter): array
    {
        $value = $container->getParameter($parameter);

        return \is_array($value) ? array_values(array_map(strval(...), $value)) : [];
    }
}
