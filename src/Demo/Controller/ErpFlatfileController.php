<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Channel\DemoChannelContext;
use App\Demo\Erp\ExchangeFileSample;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Twig\Environment;

/** The page "Flat-file exchange" (US-190): the contract of the order file and an excerpt of the last file really deposited, for the channel of the visitor (US-193). */
final readonly class ErpFlatfileController
{
    public function __construct(private Environment $twig, private ExchangeFileSample $sample, private ChannelContextInterface $channelContext)
    {
    }

    #[Route('/{_locale}/echange-fichier', name: 'app_demo_erp_flatfile', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $channel = $this->channelContext->getChannel()->getCode() ?? DemoChannelContext::B2B;
        } catch (ChannelNotFoundException) {
            $channel = DemoChannelContext::B2B;
        }

        return new Response($this->twig->render('demo/erp_flatfile.html.twig', ['columns' => OrderLinesCsv::HEADER, 'excerpt' => $this->sample->forChannel($channel)]));
    }
}
