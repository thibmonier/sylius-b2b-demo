<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Plugin\DemoPluginCatalog;
use App\Demo\Proof\ProofReader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** Read-only public page presenting every showcased plugin (US-014), on both channels; the facts of the badges come from the proof file (US-204). */
final readonly class PluginsController
{
    public function __construct(
        private Environment $twig,
        private DemoPluginCatalog $catalog,
        private ProofReader $proof,
    ) {
    }

    #[Route('/{_locale}/plugins', name: 'app_demo_plugins', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('shop/plugins/index.html.twig', [
            'groups' => $this->catalog->listedByCategory(),
            'proof' => $this->proof->report(),
        ]));
    }
}
