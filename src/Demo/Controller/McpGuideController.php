<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Http\BasicAuthGate;
use App\Demo\Mcp\InvalidTranscript;
use App\Demo\Mcp\McpServerUrl;
use App\Demo\Mcp\Transcript;
use App\Demo\Mcp\TranscriptLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/** Step-by-step guide to connect an AI assistant to the demo (US-030), on both channels, and the transcript of a real conversation (US-103). */
final readonly class McpGuideController
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private BasicAuthGate $gate,
        private McpServerUrl $serverUrl,
        private TranscriptLoader $transcripts,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/{_locale}/mcp', name: 'app_demo_mcp_guide', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/mcp.html.twig', [
            'server_url' => $this->serverUrl->forRequest($this->urlGenerator->generate('_mcp_endpoint', [], UrlGeneratorInterface::ABSOLUTE_URL)),
            // A hosted assistant (Claude, ChatGPT) cannot pass the HTTP basic authentication of the private demo.
            'private_demo' => $this->gate->isActive(),
            'transcript' => $this->transcript(),
        ]));
    }

    /** A refused transcript is logged and hidden: the guide never fails because of it. */
    private function transcript(): ?Transcript
    {
        try {
            return $this->transcripts->load();
        } catch (InvalidTranscript $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
