<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Images\ImageCredits;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** US-061: credits of the demo images (author, source, licence), generated from the manifest. */
final readonly class ImageCreditsController
{
    public function __construct(private Environment $twig, private ImageCredits $credits)
    {
    }

    #[Route('/{_locale}/credits-images', name: 'app_demo_image_credits', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('shop/legal/image_credits.html.twig', ['credits' => $this->credits->all()]));
    }
}
