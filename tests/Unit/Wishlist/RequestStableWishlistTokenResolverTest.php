<?php

declare(strict_types=1);

namespace App\Tests\Unit\Wishlist;

use App\Wishlist\RequestStableWishlistTokenResolver;
use PHPUnit\Framework\TestCase;
use Sylius\WishlistPlugin\Resolver\WishlistCookieTokenResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestStableWishlistTokenResolverTest extends TestCase
{
    private const COOKIE = 'wishlist_token';

    public function testTheSameRequestKeepsTheTokenTheInnerResolverGeneratedFirst(): void
    {
        $stack = new RequestStack();
        $stack->push(new Request());
        $resolver = $this->resolver($stack);

        self::assertSame($resolver->resolve(), $resolver->resolve());
    }

    public function testTwoSuccessiveMainRequestsGetDifferentTokens(): void
    {
        $stack = new RequestStack();
        $resolver = $this->resolver($stack);

        $stack->push(new Request());
        $first = $resolver->resolve();
        $stack->pop();
        $stack->push(new Request());

        self::assertNotSame($first, $resolver->resolve());
    }

    public function testTheCookieWins(): void
    {
        $stack = new RequestStack();
        $stack->push(new Request(cookies: [self::COOKIE => 'from-cookie']));

        self::assertSame('from-cookie', $this->resolver($stack)->resolve());
    }

    public function testWithoutARequestTheDecoratedServiceAnswersEveryTime(): void
    {
        $resolver = $this->resolver(new RequestStack());

        self::assertNotSame($resolver->resolve(), $resolver->resolve());
    }

    private function resolver(RequestStack $stack): RequestStableWishlistTokenResolver
    {
        $inner = new class($stack) implements WishlistCookieTokenResolverInterface {
            public function __construct(private readonly RequestStack $stack)
            {
            }

            public function resolve(): string
            {
                return $this->stack->getMainRequest()?->cookies->get('wishlist_token') ?? bin2hex(random_bytes(8));
            }
        };

        return new RequestStableWishlistTokenResolver($inner, $stack, self::COOKIE);
    }
}
