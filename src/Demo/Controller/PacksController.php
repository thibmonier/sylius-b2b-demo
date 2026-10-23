<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Packs\PacksReader;
use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * "Install a pack" (US-178): the metapackages of the plugins, what each one installs and what is left to do by hand.
 * Reads a versioned file only: no call to GitHub or Packagist while the page is served.
 */
final readonly class PacksController
{
    public function __construct(
        private Environment $twig,
        private DemoPluginCatalog $plugins,
        private PacksReader $reader,
    ) {
    }

    #[Route('/{_locale}/packs', name: 'app_demo_packs', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        $names = [];
        foreach ($this->plugins->all() as $plugin) {
            $names[$plugin->slug] = $plugin->name;
        }

        return new Response($this->twig->render('demo/packs.html.twig', ['report' => $this->reader->report(), 'names' => $names]));
    }
}
