<?php

declare(strict_types=1);

namespace App\Wishlist;

use Sylius\WishlistPlugin\Resolver\WishlistCookieTokenResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * US-048 / T-048-07: a visitor without the wishlist cookie gets a new random token at every call of the plugin's
 * resolver. The plugin's Twig functions cache the wishlists by user and token, so every product card of a list missed
 * that cache and queried the wishlists again (28 queries at 50 products). The token, once generated for a request,
 * stays the same for that request; the cookie and the request attribute still win when present.
 */
#[AsDecorator(decorates: 'sylius_wishlist_plugin.resolver.wishlist_cookie_token_resolver')]
final class RequestStableWishlistTokenResolver implements WishlistCookieTokenResolverInterface
{
    /** @var \WeakMap<object, string> generated tokens by main request: they die with the request */
    private \WeakMap $generated;

    public function __construct(
        #[AutowireDecorated]
        private readonly WishlistCookieTokenResolverInterface $inner,
        private readonly RequestStack $requestStack,
        #[Autowire(param: 'sylius_wishlist_plugin.parameters.wishlist_cookie_token')]
        private readonly string $cookieName,
    ) {
        $this->generated = new \WeakMap();
    }

    public function resolve(): string
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request || $request->cookies->has($this->cookieName) || $request->attributes->has($this->cookieName)) {
            return $this->inner->resolve();
        }

        return $this->generated[$request] ??= $this->inner->resolve();
    }
}
