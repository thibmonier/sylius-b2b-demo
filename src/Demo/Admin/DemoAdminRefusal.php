<?php

declare(strict_types=1);

namespace App\Demo\Admin;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Answer to a refused admin write (US-035): the demo message, in the locale of the back office, as a flash
 * on the page the visitor came from, or as a 403 (page or JSON) when there is no such page to go back to.
 */
final class DemoAdminRefusal
{
    public const TRANSLATION_DOMAIN = 'demo_admin';

    public const MESSAGE_KEY = 'demo_admin.refused';

    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly LocaleContextInterface $localeContext,
        #[Autowire('/%sylius_admin.path_name%')]
        private readonly string $adminPath,
    ) {
    }

    /**
     * @param bool $flashOnOriginPage false for the pages that do not show flashes (public admin pages): always a 403
     */
    public function respond(Request $request, bool $flashOnOriginPage = true): Response
    {
        $message = $this->translator->trans(self::MESSAGE_KEY, [], self::TRANSLATION_DOMAIN, $this->localeCode());

        if ($request->isXmlHttpRequest() || $this->prefersJson($request)) {
            return new JsonResponse(['code' => Response::HTTP_FORBIDDEN, 'message' => $message], Response::HTTP_FORBIDDEN);
        }

        $origin = $flashOnOriginPage ? $this->originPage($request) : null;
        if (null !== $origin && $request->hasSession() && $request->getSession() instanceof FlashBagAwareSessionInterface) {
            $request->getSession()->getFlashBag()->add('error', $message);

            return new RedirectResponse($origin);
        }

        return new Response($this->twig->render('admin/demo/forbidden.html.twig', ['message' => $message]), Response::HTTP_FORBIDDEN);
    }

    private function localeCode(): string
    {
        try {
            return $this->localeContext->getLocaleCode();
        } catch (\Throwable) {
            return 'fr_FR';
        }
    }

    private function prefersJson(Request $request): bool
    {
        return !\in_array('text/html', $request->getAcceptableContentTypes(), true) &&
            \in_array('application/json', $request->getAcceptableContentTypes(), true);
    }

    /** The admin page the form was posted from, when it is on this host (never an open redirect). */
    private function originPage(Request $request): ?string
    {
        $referer = $request->headers->get('Referer');
        $parts = null === $referer ? false : parse_url($referer);
        if (!\is_array($parts) || ($parts['host'] ?? null) !== $request->getHost()) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if ($path !== $this->adminPath && !str_starts_with($path, $this->adminPath . '/')) {
            return null;
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
