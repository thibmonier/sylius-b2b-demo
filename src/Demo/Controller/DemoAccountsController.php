<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Exposes the /demo/accounts page: a public page listing all demo credentials
 * (emails, passwords, roles) for live demonstrations. Never shows real data.
 */
#[Route('/demo/accounts', name: 'app_demo_accounts', methods: ['GET'])]
final readonly class DemoAccountsController
{
    public function __construct(private Environment $twig)
    {
    }

    public function __invoke(): Response
    {
        return new Response(
            $this->twig->render('demo/accounts.html.twig', [
                'password' => 'Demo1234!',
                'admin_password' => 'demo-sophie',
                'companies' => [
                    [
                        'name' => 'Acme Industries',
                        'members' => [
                            ['email' => 'claire@acme.example', 'name' => 'Claire Dupont', 'role' => 'Acheteuse / Buyer'],
                            ['email' => 'paul@acme.example', 'name' => 'Paul Martin', 'role' => 'Approbateur / Approver'],
                            ['email' => 'isabelle@acme.example', 'name' => 'Isabelle Renard', 'role' => 'Administratrice / Admin'],
                        ],
                    ],
                    [
                        'name' => 'TechParts SAS',
                        'members' => [
                            ['email' => 'marc@techparts.example', 'name' => 'Marc Lefebvre', 'role' => 'Acheteur / Buyer (aussi dans BuildCo SA)'],
                            ['email' => 'nathalie@techparts.example', 'name' => 'Nathalie Girard', 'role' => 'Administratrice / Admin'],
                        ],
                    ],
                    [
                        'name' => 'BuildCo SA',
                        'members' => [
                            ['email' => 'thomas@buildco.example', 'name' => 'Thomas Bernard', 'role' => 'Acheteur / Buyer — ⚠️ compte désactivé'],
                            ['email' => 'antoine@buildco.example', 'name' => 'Antoine Moreau', 'role' => 'Approbateur / Approver'],
                        ],
                    ],
                    [
                        'name' => 'GlobalMaint SA',
                        'members' => [
                            ['email' => 'henri@globalmaint.example', 'name' => 'Henri Dubois', 'role' => 'Acheteur / Buyer'],
                            ['email' => 'celine@globalmaint.example', 'name' => 'Céline Lambert', 'role' => 'Administratrice / Admin'],
                        ],
                    ],
                    [
                        'name' => 'IndusPro SARL',
                        'members' => [
                            ['email' => 'yves@induspro.example', 'name' => 'Yves Fontaine', 'role' => 'Acheteur / Buyer'],
                            ['email' => 'martine@induspro.example', 'name' => 'Martine Rousseau', 'role' => 'Approbatrice / Approver'],
                        ],
                    ],
                ],
                'other_accounts' => [
                    ['email' => 'lea@demo.example', 'name' => 'Léa Petit', 'role' => 'Cliente B2C / B2C customer', 'password' => 'Demo1234!'],
                    ['email' => 'sophie@fournitures-pro.example', 'name' => 'Sophie Martin', 'role' => 'Admin back-office (restreint)', 'password' => 'demo-sophie'],
                ],
            ]),
        );
    }
}
