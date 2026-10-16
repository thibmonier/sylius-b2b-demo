<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Mail\DemoMailbox;
use App\Demo\Mail\MailpitUnavailableException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Read-only demo mailbox (US-028): the mails sent by the demo, read server-side from the Mailpit
 * HTTP API. GET only; sensitive mails are never listed and answer 404 by id; 404 when the demo
 * mode is off. Mailpit itself is never exposed and no internal URL reaches the page.
 */
final readonly class DemoMailsController
{
    public function __construct(
        private Environment $twig,
        private DemoMailbox $mailbox,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    #[Route('/demo/mails', name: 'app_demo_mails', methods: ['GET'])]
    public function list(): Response
    {
        $this->assertAvailable();

        try {
            return new Response($this->twig->render('demo/mails.html.twig', ['messages' => $this->mailbox->list(), 'unavailable' => false]));
        } catch (MailpitUnavailableException) {
            return new Response($this->twig->render('demo/mails.html.twig', ['messages' => [], 'unavailable' => true]), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    #[Route('/demo/mails/{id}', name: 'app_demo_mail', requirements: ['id' => '[A-Za-z0-9_-]{1,64}'], methods: ['GET'])]
    public function show(string $id): Response
    {
        $this->assertAvailable();

        try {
            $message = $this->mailbox->find($id);
        } catch (MailpitUnavailableException) {
            return new Response($this->twig->render('demo/mails.html.twig', ['messages' => [], 'unavailable' => true]), Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (null === $message) {
            throw new NotFoundHttpException();
        }

        return new Response($this->twig->render('demo/mail.html.twig', ['message' => $message]));
    }

    private function assertAvailable(): void
    {
        if (!$this->demoMode || !$this->mailbox->isConfigured()) {
            throw new NotFoundHttpException();
        }
    }
}
