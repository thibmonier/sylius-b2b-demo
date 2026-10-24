<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Einvoice\FictitiousPlatformOutage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Entity\InvoiceTransmission;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\RelaunchTransmissionHandler;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\TransmissionNotRelaunchable;
use Twig\Environment;

/**
 * US-184: the transmissions of the e-invoices in the back office, and the relaunch of a send in error. The relaunch is a POST
 * with a CSRF token, behind the administration firewall; the journal refuses any status but "error".
 */
final readonly class EinvoiceTransmissionsController
{
    public const TOKEN_ID = 'einvoice_relaunch';

    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $entityManager,
        private RelaunchTransmissionHandler $relaunch,
        private FictitiousPlatformOutage $outage,
        private CsrfTokenManagerInterface $csrf,
        private RequestStack $requests,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urls,
    ) {
    }

    #[Route('/%sylius_admin.path_name%/demo/einvoice', name: 'app_demo_admin_einvoice_transmissions', methods: ['GET'])]
    public function index(): Response
    {
        $transmissions = $this->entityManager->getRepository(InvoiceTransmission::class)->findBy([], ['updatedAt' => 'DESC']);

        return new Response($this->twig->render('admin/demo/einvoice/index.html.twig', ['transmissions' => $transmissions, 'token_id' => self::TOKEN_ID]));
    }

    #[Route('/%sylius_admin.path_name%/demo/einvoice/{invoiceId}/relaunch', name: 'app_demo_admin_einvoice_relaunch', requirements: ['invoiceId' => '[A-Za-z0-9-]+'], methods: ['POST'])]
    public function relaunch(Request $request, string $invoiceId): Response
    {
        if (!$this->csrf->isTokenValid(new CsrfToken(self::TOKEN_ID, $request->request->getString('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        try {
            // The simulated outage is over once an administrator relaunches (see FictitiousPlatformOutage).
            $this->outage->recover();
            $this->relaunch->relaunch($invoiceId);
            $this->flash('success', 'relaunched');
        } catch (TransmissionNotRelaunchable) {
            $this->flash('error', 'not_relaunchable');
        }

        return new RedirectResponse($this->urls->generate('app_demo_admin_einvoice_transmissions'));
    }

    private function flash(string $type, string $message): void
    {
        $session = $this->requests->getSession();
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $this->translator->trans('einvoice.flash.' . $message, [], 'demo_einvoice'));
        }
    }
}
