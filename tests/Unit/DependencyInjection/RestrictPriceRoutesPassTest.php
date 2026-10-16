<?php

declare(strict_types=1);

namespace App\Tests\Unit\DependencyInjection;

use App\DependencyInjection\Compiler\RestrictPriceRoutesPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RestrictPriceRoutesPassTest extends TestCase
{
    public function testItAddsTheRoutesToTheOnesOfTheAccessPlugin(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('tmonier_channel_access.restricted_routes', ['sylius_shop_cart_*']);
        $container->setParameter('tmonier_channel_access.restricted_paths', ['/api/orders']);

        (new RestrictPriceRoutesPass())->process($container);

        self::assertSame(['sylius_shop_cart_*', ...RestrictPriceRoutesPass::ROUTES], $container->getParameter('tmonier_channel_access.restricted_routes'));
        self::assertSame(['/api/orders', ...RestrictPriceRoutesPass::PATHS], $container->getParameter('tmonier_channel_access.restricted_paths'));
        self::assertContains('tmonier_quote_request_shop_new', RestrictPriceRoutesPass::ROUTES);
        self::assertContains('%sylius.security.api_shop_route%/product-variants/*/tier-prices', RestrictPriceRoutesPass::PATHS);
    }

    public function testItIsHarmlessWithoutTheAccessPlugin(): void
    {
        $container = new ContainerBuilder();

        (new RestrictPriceRoutesPass())->process($container);

        self::assertFalse($container->hasParameter('tmonier_channel_access.restricted_routes'));
    }
}
