<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Plugin\DemoPluginCatalog;
use App\Demo\Proof\Compatibility\CompatibilityReader;
use App\Demo\Proof\Compatibility\DependencyGraph;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Compatibility matrix and dependency graph of the plugins (US-171): the versions each plugin's CI runs, the combination
 * of the demo, and which plugin needs which. Reads a versioned file only: no call to GitHub or Packagist while the page is served.
 */
final readonly class CompatibilityController
{
    public function __construct(
        private Environment $twig,
        private DemoPluginCatalog $plugins,
        private CompatibilityReader $reader,
    ) {
    }

    #[Route('/{_locale}/compatibilite', name: 'app_demo_compatibility', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        $plugins = $this->plugins->all();
        $names = [];
        foreach ($plugins as $plugin) {
            $names[$plugin->slug] = $plugin->name;
        }
        $report = $this->reader->report();

        return new Response($this->twig->render('demo/compatibility.html.twig', [
            'plugins' => $plugins,
            'names' => $names,
            'report' => $report,
            'graph' => DependencyGraph::build($report, $names),
        ]));
    }
}
