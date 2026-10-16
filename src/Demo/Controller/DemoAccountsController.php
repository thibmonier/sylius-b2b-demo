<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Account\DemoAccountCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Exposes the /demo/accounts page: a public page listing all demo credentials
 * (emails, passwords, roles) for live demonstrations. Never shows real data.
 * The accounts come from DemoAccountCatalog; the page answers 404 when the demo mode is off.
 */
#[Route('/demo/accounts', name: 'app_demo_accounts', methods: ['GET'])]
final readonly class DemoAccountsController
{
    public function __construct(
        private Environment $twig,
        private DemoAccountCatalog $catalog,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->demoMode) {
            throw new NotFoundHttpException();
        }

        return new Response(
            $this->twig->render('demo/accounts.html.twig', [
                'password' => $this->catalog->password(),
                'companies' => $this->catalog->companies(),
                'consumers' => $this->catalog->consumers(),
                'admins' => $this->catalog->adminUsers(),
            ]),
        );
    }
}
