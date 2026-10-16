<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Http\BasicAuthGate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/** Step-by-step guide to connect an AI assistant to the demo (US-030), on both channels. */
final readonly class McpGuideController
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private BasicAuthGate $gate,
    ) {
    }

    #[Route('/{_locale}/mcp', name: 'app_demo_mcp_guide', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/mcp.html.twig', [
            'server_url' => $this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // A hosted assistant (Claude, ChatGPT) cannot pass the HTTP basic authentication of the private demo.
            'private_demo' => $this->gate->isActive(),
        ]));
    }
}
