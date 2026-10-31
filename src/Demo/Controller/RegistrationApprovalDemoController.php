<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * The demo of the Registration Approval plugin (US-243). With registrations open, it is the registration form. In public
 * mode (DEMO_REGISTRATION_OPEN=0) the form answers 403, so the page explains the flow and sends to Sophie's review of
 * the registrations waiting in the admin panel.
 */
final readonly class RegistrationApprovalDemoController
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire(param: 'demo.registration_open')]
        private bool $registrationOpen,
    ) {
    }

    #[Route('/{_locale}/validation-des-inscriptions', name: 'app_demo_registration_approval', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(string $_locale): Response
    {
        if ($this->registrationOpen) {
            return new RedirectResponse($this->urlGenerator->generate('sylius_shop_register', ['_locale' => $_locale]));
        }

        return new Response($this->twig->render('demo/registration_approval.html.twig'));
    }
}
