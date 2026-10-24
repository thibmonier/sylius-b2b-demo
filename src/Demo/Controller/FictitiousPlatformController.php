<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Einvoice\DemoTransmissionScenario;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** The page of the fictitious platform (US-183): what it simulates, the four scenarios of the demo data, the banner that says it is a simulation. */
final readonly class FictitiousPlatformController
{
    public function __construct(private Environment $twig)
    {
    }

    #[Route('/{_locale}/pa-fictive', name: 'app_demo_fictitious_platform', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/fictitious_platform.html.twig', ['scenarios' => DemoTransmissionScenario::cases()]));
    }
}
