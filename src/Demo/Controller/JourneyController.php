<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Command\ResetStatus;
use App\Demo\Journey\Journey;
use App\Demo\Journey\JourneyCatalog;
use App\Demo\Journey\JourneyLauncher;
use App\Demo\Journey\JourneyProgress;
use App\Demo\Journey\JourneyStep;
use App\Demo\Journey\ScenarioPreparationFailed;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Guided journeys (US-105): one URL per step, rendered on the server, the progress kept in the session. The list, a step
 * (or the exit screen, one past the last step) and the launch of the scenario of a step. Only in the demo mode.
 */
final readonly class JourneyController
{
    private const LAUNCH_CSRF = 'demo_journey_launch';

    private const LOCALE = '[a-z]{2}_[A-Z]{2}';

    public function __construct(
        private Environment $twig,
        private JourneyCatalog $catalog,
        private JourneyProgress $progress,
        private JourneyLauncher $launcher,
        private ResetStatus $resetStatus,
        private DemoAccountCatalog $accounts,
        private TokenStorageInterface $tokens,
        private UrlGeneratorInterface $urls,
        private RouterInterface $router,
        private CsrfTokenManagerInterface $csrf,
        private TranslatorInterface $translator,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.journeys_integrate_route')]
        private string $integrateRoute,
    ) {
    }

    #[Route('/{_locale}/guides', name: 'app_demo_journeys', requirements: ['_locale' => self::LOCALE], methods: ['GET'])]
    public function index(): Response
    {
        $this->assertDemoMode();
        $this->progress->syncWithReset($this->resetStatus->lastSuccess());
        $resumes = [];
        foreach ($this->catalog->all() as $journey) {
            $resumes[$journey->id] = $this->progress->hasStarted($journey->id) ? $this->progress->step($journey->id) : null;
        }

        return new Response($this->twig->render('demo/journey/index.html.twig', [
            'journeys' => $this->catalog->all(),
            'resumes' => $resumes,
            'reset_notice' => $this->progress->consumeResetNotice(),
        ]));
    }

    #[Route('/{_locale}/guides/{id}/{step}', name: 'app_demo_journey_step', requirements: ['_locale' => self::LOCALE, 'id' => '[a-z0-9_-]+', 'step' => '\d+'], methods: ['GET'])]
    public function step(Request $request, string $id, int $step): Response
    {
        $this->assertDemoMode();
        $journey = $this->journey($id);
        if ($step < 1 || $step > $journey->exitNumber()) {
            throw new NotFoundHttpException();
        }
        if ($this->progress->syncWithReset($this->resetStatus->lastSuccess()) && 1 !== $step) {
            return new RedirectResponse($this->stepUrl($journey, 1));
        }
        $notice = $this->progress->consumeResetNotice();
        $this->progress->moveTo($id, $step);

        $current = $journey->step($step);
        $account = $this->accounts->findShopAccount($journey->accountEmail);

        return new Response($this->twig->render('demo/journey/step.html.twig', [
            'journey' => $journey,
            'number' => $step,
            'step' => $current,
            'exit' => $journey->isExit($step),
            'target_url' => null === $current ? null : $this->targetUrl($current, $request->getLocale()),
            'previous_url' => $step > 1 ? $this->stepUrl($journey, $step - 1) : null,
            'next_url' => $journey->isExit($step) ? null : $this->stepUrl($journey, $step + 1),
            'first_url' => $this->stepUrl($journey, 1),
            'wrong_account' => $this->signedInEmail() !== strtolower($journey->accountEmail),
            'account' => $account,
            'reset_notice' => $notice,
            'integrate_url' => $this->integrateUrl($request->getLocale()),
            'launch_token' => $this->csrf->getToken(self::LAUNCH_CSRF)->getValue(),
        ]));
    }

    #[Route('/{_locale}/guides/{id}/{step}/launch', name: 'app_demo_journey_launch', requirements: ['_locale' => self::LOCALE, 'id' => '[a-z0-9_-]+', 'step' => '\d+'], methods: ['POST'])]
    public function launch(Request $request, string $id, int $step): Response
    {
        $this->assertDemoMode();
        $journey = $this->journey($id);
        $current = $journey->step($step);
        if (null === $current || !$current->hasScenario()) {
            throw new NotFoundHttpException();
        }
        if ($this->signedInEmail() !== strtolower($journey->accountEmail)) {
            return new RedirectResponse($this->stepUrl($journey, $step));
        }
        if (!$this->csrf->isTokenValid(new CsrfToken(self::LAUNCH_CSRF, (string) $request->request->get('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        try {
            $this->launcher->launch($current);
        } catch (ScenarioPreparationFailed) {
            $this->flash($request, 'error', 'launch.failed');

            return new RedirectResponse($this->stepUrl($journey, $step));
        }

        return new RedirectResponse($this->targetUrl($current, $request->getLocale()));
    }

    private function assertDemoMode(): void
    {
        if (!$this->demoMode) {
            throw new NotFoundHttpException();
        }
    }

    private function journey(string $id): Journey
    {
        return $this->catalog->find($id) ?? throw new NotFoundHttpException();
    }

    private function stepUrl(Journey $journey, int $number): string
    {
        return $this->urls->generate('app_demo_journey_step', ['id' => $journey->id, 'step' => $number]);
    }

    private function targetUrl(JourneyStep $step, string $locale): string
    {
        $parameters = $step->routeParameters;
        if (str_contains($this->router->getRouteCollection()->get($step->route)?->getPath() ?? '', '{_locale}')) {
            $parameters['_locale'] = $locale;
        }

        return $this->urls->generate($step->route, $parameters);
    }

    /** The page "Intégrer" (US-107), null while it does not exist. */
    private function integrateUrl(string $locale): ?string
    {
        try {
            return $this->urls->generate($this->integrateRoute, ['_locale' => $locale]);
        } catch (RouteNotFoundException) {
            return null;
        }
    }

    private function signedInEmail(): ?string
    {
        $user = $this->tokens->getToken()?->getUser();
        $email = $user instanceof ShopUserInterface ? $user->getCustomer()?->getEmail() : null;

        return null === $email ? null : strtolower($email);
    }

    private function flash(Request $request, string $type, string $key): void
    {
        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $this->translator->trans($key, [], 'journeys'));
        }
    }
}
