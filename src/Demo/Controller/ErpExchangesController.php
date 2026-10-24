<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Erp\ErpExchangeLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** US-188: the exchanges with "ERP Démo (fictif)" in the back office, read only (same skeleton as the received webhooks and the e-invoice transmissions). */
final readonly class ErpExchangesController
{
    public function __construct(private Environment $twig, private ErpExchangeLog $log)
    {
    }

    #[Route('/%sylius_admin.path_name%/demo/erp', name: 'app_demo_admin_erp_exchanges', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('admin/demo/erp/index.html.twig', ['batches' => $this->log->batches()]));
    }
}
