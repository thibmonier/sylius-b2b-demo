<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Erp\ExchangeFileSample;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Twig\Environment;

/** The page "Flat-file exchange" (US-190): the contract of the order file and an excerpt produced from the placed orders of the demo. */
final readonly class ErpFlatfileController
{
    public function __construct(private Environment $twig, private ExchangeFileSample $sample)
    {
    }

    #[Route('/{_locale}/echange-fichier', name: 'app_demo_erp_flatfile', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/erp_flatfile.html.twig', ['columns' => OrderLinesCsv::HEADER, 'sample' => $this->sample->csv()]));
    }
}
