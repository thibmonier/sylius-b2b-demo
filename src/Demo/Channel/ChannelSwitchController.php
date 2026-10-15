<?php

declare(strict_types=1);

namespace App\Demo\Channel;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** Switches between the B2B and B2C channels of the demo (US-007). */
final readonly class ChannelSwitchController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[Route('/demo/channel/{code}', name: 'app_demo_channel_switch', requirements: ['code' => 'B2B|B2C'], methods: ['GET'])]
    public function __invoke(string $code): RedirectResponse
    {
        $response = new RedirectResponse($this->urlGenerator->generate('sylius_shop_homepage'));
        $response->headers->setCookie(Cookie::create(DemoChannelContext::COOKIE, DemoChannelContext::codeFrom($code))->withSameSite(Cookie::SAMESITE_LAX)->withHttpOnly(true)->withExpires(new \DateTimeImmutable('+30 days')));

        return $response;
    }
}
