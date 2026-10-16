<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** Read-only public pages: legal notice and about (US-018). */
final readonly class LegalPagesController
{
    /**
     * @param list<string> $plugins
     */
    public function __construct(
        private Environment $twig,
        #[Autowire(param: 'demo.legal.publisher')]
        private string $publisher,
        #[Autowire(param: 'demo.legal.host')]
        private string $host,
        #[Autowire(param: 'demo.plugins')]
        private array $plugins,
    ) {
    }

    #[Route('/{_locale}/mentions-legales', name: 'app_demo_legal_notice', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function legalNotice(): Response
    {
        return new Response($this->twig->render('shop/legal/legal_notice.html.twig', [
            'publisher' => trim($this->publisher),
            'host' => trim($this->host),
        ]));
    }

    #[Route('/{_locale}/a-propos', name: 'app_demo_about', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function about(): Response
    {
        return new Response($this->twig->render('shop/legal/about.html.twig', [
            'plugins' => $this->plugins,
        ]));
    }
}
