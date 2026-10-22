<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Integration\IntegrationMap;
use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** "Plugin integration guide" (US-107): prerequisites, typical installation, questions asked first, typical architecture, integrations and their state (US-108), repositories. */
final readonly class IntegrateController
{
    public function __construct(private Environment $twig, private DemoPluginCatalog $plugins, private IntegrationMap $integrations)
    {
    }

    #[Route('/{_locale}/integrer', name: 'app_demo_integrate', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/integrate.html.twig', ['plugins' => $this->plugins->all(), 'integrations' => $this->integrations->all()]));
    }
}
