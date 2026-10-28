<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Plugin\DemoPluginCatalog;
use App\Demo\Proof\ProofReader;
use App\Demo\Proof\ProofSummary;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Technical proof (US-111): version, Sylius compatibility and quality of every plugin, and how the demo is delivered.
 * Reads a versioned file only: no call to Packagist or GitHub while the page is served.
 */
final readonly class ProofController
{
    public function __construct(
        private Environment $twig,
        private DemoPluginCatalog $plugins,
        private ProofReader $reader,
    ) {
    }

    #[Route('/{_locale}/preuve', name: 'app_demo_proof', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        $plugins = $this->plugins->all();
        $report = $this->reader->report();

        return new Response($this->twig->render('demo/proof.html.twig', [
            'plugins' => $plugins,
            'report' => $report,
            'summary' => ProofSummary::of($report, array_map(static fn (DemoPlugin $plugin): string => $plugin->slug, $plugins)),
        ]));
    }
}
