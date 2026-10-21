<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** "Integrate these plugins in your project" (US-107): formulas, questions asked first, typical architecture, lead times. */
final readonly class IntegrateController
{
    public function __construct(private Environment $twig)
    {
    }

    #[Route('/{_locale}/integrer', name: 'app_demo_integrate', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/integrate.html.twig'));
    }
}
