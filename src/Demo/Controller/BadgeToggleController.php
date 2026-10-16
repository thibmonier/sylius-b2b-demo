<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Badge\BadgePreference;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** Shows or hides the plugin badges (US-016/US-017) and goes back to the page the visitor was on. */
final readonly class BadgeToggleController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[Route('/demo/badges/{state}', name: 'app_demo_badges_toggle', requirements: ['state' => 'on|off'], methods: ['GET'])]
    public function __invoke(Request $request, string $state): RedirectResponse
    {
        $response = new RedirectResponse($this->backTo($request));
        $response->headers->setCookie(
            Cookie::create(BadgePreference::COOKIE, 'off' === $state ? BadgePreference::HIDDEN : 'on')
                ->withSameSite(Cookie::SAMESITE_LAX)
                ->withHttpOnly(true)
                ->withExpires(new \DateTimeImmutable('+30 days')),
        );

        return $response;
    }

    /** Same-origin path of the referer only: never an open redirect. */
    private function backTo(Request $request): string
    {
        $referer = $request->headers->get('Referer');
        $parts = null === $referer ? false : parse_url($referer);
        if (\is_array($parts) && ($parts['host'] ?? null) === $request->getHost() && isset($parts['path']) && self::isLocalPath($parts['path'])) {
            return $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
        }

        return $this->urlGenerator->generate('sylius_shop_homepage');
    }

    /** A single leading slash: "//host" is protocol-relative and a backslash may be read as a slash by browsers. */
    private static function isLocalPath(string $path): bool
    {
        return str_starts_with($path, '/') && !str_starts_with($path, '//') && !str_contains($path, '\\');
    }
}
