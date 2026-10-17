<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\Controller\PunchOutOciSimulatorController;
use App\Demo\Http\InternalRequestToken;
use App\Demo\Http\VisitorIpResolver;
use App\Demo\PunchOutSimulator\OciBuyerProvider;
use App\Demo\PunchOutSimulator\OciCartReader;
use App\Demo\PunchOutSimulator\OciEntryClient;
use App\Demo\PunchOutSimulator\OciHookUrl;
use App\Demo\PunchOutSimulator\OciReturnGate;
use App\Demo\PunchOutSimulator\ReceivedCart;
use App\Demo\PunchOutSimulator\SignedState;
use App\Tests\Support\OciPostedBody;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Security\ReturnUrlValidator;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;
use Twig\Environment;

/** US-053: the OCI half of the simulator, with the shop's endpoint replaced by a mock. */
final class PunchOutOciSimulatorControllerTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $rendered = [];

    private int $shopCalls = 0;

    public function testEveryActionAnswers404OutsideTheDemoMode(): void
    {
        $controller = $this->controller(demoMode: false);

        foreach (['index', 'start', 'receive'] as $action) {
            try {
                $controller->{$action}(new Request());
                self::fail($action . ' must answer 404 outside the demo mode');
            } catch (NotFoundHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testAHookUrlOutsideTheAllowedDomainsIsRefusedBeforeAnyCallToTheShop(): void
    {
        $response = $this->controller()->start($this->post('/start', ['HOOK_URL' => 'https://not-allowed.example/return']));

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame('hook_url_not_allowed', $this->errorKey());
        self::assertSame(0, $this->shopCalls);
    }

    public function testAnExpiredFormIsRefusedBeforeAnyCallToTheShop(): void
    {
        $response = $this->controller(csrfValid: false)->start($this->post('/start', ['HOOK_URL' => 'https://erp.acme.example/return']));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame('csrf', $this->errorKey());
        self::assertSame(0, $this->shopCalls);
    }

    public function testTheEntryOpensTheShopSessionOfTheVisitor(): void
    {
        $response = $this->controller()->start($this->post('/start', ['HOOK_URL' => 'https://erp.acme.example/demo/punchout-simulator/oci/return']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/fr_FR/', $response->getTargetUrl());
        self::assertSame('PHPSESSID', $response->headers->getCookies()[0]->getName());
        self::assertSame(1, $this->shopCalls);
    }

    public function testAnAnswerOfTheShopThatIsNotARedirectToThePathIsAnError(): void
    {
        $controller = $this->controller(shop: new MockResponse('Forbidden', ['http_code' => 403]));

        $response = $controller->start($this->post('/start', ['HOOK_URL' => 'https://erp.acme.example/return']));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame('status_403', $this->errorKey());
    }

    public function testALimitedEntryIsReportedAs429(): void
    {
        $controller = $this->controller(shop: new MockResponse('Too Many Requests', ['http_code' => 429]));

        $response = $controller->start($this->post('/start', ['HOOK_URL' => 'https://erp.acme.example/return']));

        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    public function testARedirectToAnotherHostIsNotRelayed(): void
    {
        $controller = $this->controller(shop: new MockResponse('', ['http_code' => 303, 'response_headers' => ['Location: https://evil.example/']]));

        $response = $controller->start($this->post('/start', ['HOOK_URL' => 'https://erp.acme.example/return']));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testTheCartPostedBackIsShownOnceAndCreatesNoOrder(): void
    {
        $controller = $this->controller();
        $request = $this->returnRequest($this->state()->sign(['nonce' => 'n1', 'oci' => true]), $this->fields(2));

        $response = $controller->receive($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertArrayHasKey('cart', $this->rendered);
        self::assertArrayNotHasKey('state', $this->rendered, 'No order form: an OCI return creates no order.');
        self::assertArrayNotHasKey('error', $this->rendered);
    }

    public function testAReplayedReturnIsRefusedAndShowsNothing(): void
    {
        $controller = $this->controller();
        $state = $this->state()->sign(['nonce' => 'n1', 'oci' => true]);
        $controller->receive($this->returnRequest($state, $this->fields(1)));

        $response = $controller->receive($this->returnRequest($state, $this->fields(1)));

        self::assertSame(Response::HTTP_GONE, $response->getStatusCode());
        self::assertSame('return_replayed', $this->errorKey());
        self::assertArrayNotHasKey('cart', $this->rendered);
    }

    public function testAReturnWithoutAValidStateIsRefused(): void
    {
        $response = $this->controller()->receive($this->returnRequest('forged.state', $this->fields(1)));

        self::assertSame(Response::HTTP_GONE, $response->getStatusCode());
        self::assertSame('session_expired', $this->errorKey());
        self::assertArrayNotHasKey('cart', $this->rendered);
    }

    public function testAStateThatIsNotAnOciStateIsRefused(): void
    {
        $response = $this->controller()->receive($this->returnRequest($this->state()->sign(['nonce' => 'n1']), $this->fields(1)));

        self::assertSame(Response::HTTP_GONE, $response->getStatusCode());
        self::assertArrayNotHasKey('cart', $this->rendered);
    }

    public function testAnEmptyCartIsShownWithoutError(): void
    {
        $response = $this->controller()->receive($this->returnRequest($this->state()->sign(['nonce' => 'n2', 'oci' => true]), ['~OkCode' => 'ADD']));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertArrayNotHasKey('error', $this->rendered);
        self::assertInstanceOf(ReceivedCart::class, $this->rendered['cart']);
        self::assertTrue($this->rendered['cart']->isEmpty());
    }

    public function testAMalformedReturnIsRefusedWithoutConsumingTheState(): void
    {
        $controller = $this->controller();
        $state = $this->state()->sign(['nonce' => 'n3', 'oci' => true]);

        $bad = $controller->receive($this->returnRequest($state, ['NEW_ITEM-DESCRIPTION' => [1 => 'x'], 'NEW_ITEM-QUANTITY' => [1 => 'many'], 'NEW_ITEM-PRICE' => [1 => '1.00'], 'NEW_ITEM-CURRENCY' => [1 => 'EUR']]));
        $good = $controller->receive($this->returnRequest($state, $this->fields(1)));

        self::assertSame(Response::HTTP_BAD_REQUEST, $bad->getStatusCode());
        self::assertSame(Response::HTTP_OK, $good->getStatusCode());
    }

    /** @return array<array-key, mixed> */
    private function fields(int $lines): array
    {
        $transfer = [];
        for ($n = 1; $n <= $lines; ++$n) {
            $transfer[] = new TransferLine('SKU-' . $n, 'v1:SKU-' . $n, 'Article ' . $n, $n, '10.00', 'EUR');
        }

        return OciPostedBody::of(...$transfer);
    }

    /** @param array<array-key, mixed> $fields */
    private function returnRequest(string $state, array $fields): Request
    {
        return Request::create('/demo/punchout-simulator/oci/return?state=' . rawurlencode($state), 'POST', $fields);
    }

    /** @param array<string, string> $fields */
    private function post(string $path, array $fields): Request
    {
        return Request::create('/demo/punchout-simulator/oci' . $path, 'POST', $fields + ['_token' => 'v']);
    }

    private function errorKey(): mixed
    {
        $error = $this->rendered['error'] ?? null;

        return is_array($error) ? ($error['key'] ?? null) : null;
    }

    private function state(): SignedState
    {
        return new SignedState('secret', new MockClock('2026-10-17 10:00:00'));
    }

    private function controller(bool $demoMode = true, bool $csrfValid = true, ?MockResponse $shop = null): PunchOutOciSimulatorController
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function (string $template, array $parameters): string {
            $this->rendered = $parameters;

            return '';
        });
        $csrf = $this->createMock(CsrfTokenManagerInterface::class);
        $csrf->method('getToken')->willReturn(new CsrfToken('t', 'v'));
        $csrf->method('isTokenValid')->willReturn($csrfValid);
        $urls = $this->createMock(UrlGeneratorInterface::class);
        $urls->method('generate')->willReturn('https://erp.acme.example/demo/punchout-simulator/oci/return');

        $buyer = new Buyer();
        $buyer->setCode('acme-oci');
        $buyer->setOciUsername('user');
        $buyer->setReturnDomains(['erp.acme.example']);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($buyer);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $shop ??= new MockResponse('', ['http_code' => 303, 'response_headers' => ['Location: /fr_FR/', 'Set-Cookie: PHPSESSID=abc; path=/; HttpOnly']]);
        $http = new MockHttpClient(function () use ($shop): MockResponse {
            ++$this->shopCalls;

            return $shop;
        });

        return new PunchOutOciSimulatorController(
            $twig,
            $urls,
            $csrf,
            new OciBuyerProvider($entityManager),
            new OciHookUrl(new ReturnUrlValidator()),
            new OciEntryClient($http, new InternalRequestToken('secret')),
            new OciCartReader(),
            new OciReturnGate(new ArrayAdapter(), new LockFactory(new InMemoryStore())),
            $this->state(),
            new VisitorIpResolver(),
            $demoMode,
        );
    }
}
