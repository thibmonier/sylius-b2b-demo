<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\Controller\PunchOutSimulatorController;
use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\EndpointClient;
use App\Demo\PunchOutSimulator\OrderMessageParser;
use App\Demo\PunchOutSimulator\OrderRequestBuilder;
use App\Demo\PunchOutSimulator\ResponseParser;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderMessageWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;
use Twig\Environment;

/**
 * US-027 — guard of the demo mode and error scenarios of the simulator (expired session, empty cart).
 */
final class PunchOutSimulatorControllerTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $rendered = [];

    public function testEveryActionAnswers404OutsideTheDemoMode(): void
    {
        $controller = $this->controller(demoMode: false);

        foreach (['index', 'start', 'receive', 'order'] as $action) {
            try {
                $controller->{$action}(new Request());
                self::fail($action . ' must answer 404 outside the demo mode');
            } catch (NotFoundHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testAReturnWithAnExpiredSessionShowsAMessageAndNoOrderForm(): void
    {
        $xml = (new OrderMessageWriter())->write([new TransferLine('V', 'v1:V', 'Item', 1, '1.00', 'EUR')], '1.00', 'EUR', 'not-a-signed-cookie', 'p@x', 'create');

        $response = $this->controller()->receive($this->post(['cXML-base64' => base64_encode($xml)]));

        self::assertSame(Response::HTTP_GONE, $response->getStatusCode());
        self::assertSame('session_expired', $this->errorKey());
        self::assertArrayNotHasKey('state', $this->rendered);
    }

    public function testAnEmptyReturnedCartCannotBeOrdered(): void
    {
        $cookie = $this->state()->sign(['nonce' => 'n']);
        $xml = (new OrderMessageWriter())->write([], '0.00', 'EUR', $cookie, 'p@x', 'create');

        $this->controller()->receive($this->post(['cXML-base64' => base64_encode($xml)]));

        self::assertSame('empty_cart', $this->errorKey());
        self::assertArrayNotHasKey('state', $this->rendered);
    }

    public function testAnOrderOfAnEmptyCartIsRefused(): void
    {
        $state = $this->state()->sign(['cookie' => 'c', 'currency' => 'EUR', 'total' => '0.00', 'lines' => []]);

        $response = $this->controller()->order($this->post(['state' => $state, 'orderId' => 'PO-1']));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame('empty_cart_order', $this->errorKey());
    }

    public function testAnOrderWithAForgedStateIsRefused(): void
    {
        $response = $this->controller()->order($this->post(['state' => 'forged.value', 'orderId' => 'PO-1']));

        self::assertSame(Response::HTTP_GONE, $response->getStatusCode());
        self::assertSame('session_expired', $this->errorKey());
    }

    public function testAnInvalidPurchaseOrderNumberIsRefused(): void
    {
        $line = ['supplierPartId' => 'V', 'supplierPartAuxId' => 'v1:V', 'description' => 'd', 'quantity' => 1, 'unitPrice' => '1.00', 'currency' => 'EUR', 'unitOfMeasure' => 'EA'];
        $state = $this->state()->sign(['cookie' => 'c', 'currency' => 'EUR', 'total' => '1.00', 'lines' => [$line]]);

        $this->controller()->order($this->post(['state' => $state, 'orderId' => 'PO 1; DROP']));

        self::assertSame('invalid_order_id', $this->errorKey());
    }

    private function errorKey(): mixed
    {
        $error = $this->rendered['error'] ?? null;

        return is_array($error) ? ($error['key'] ?? null) : null;
    }

    /** @param array<string, string> $fields */
    private function post(array $fields): Request
    {
        return Request::create('/demo/punchout-simulator/x', 'POST', $fields);
    }

    private function state(): SignedState
    {
        return new SignedState('secret', new MockClock('2026-10-16 10:00:00'));
    }

    private function controller(bool $demoMode = true): PunchOutSimulatorController
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function (string $template, array $parameters): string {
            $this->rendered = $parameters;

            return '';
        });
        $csrf = $this->createMock(CsrfTokenManagerInterface::class);
        $csrf->method('getToken')->willReturn(new \Symfony\Component\Security\Csrf\CsrfToken('t', 'v'));
        $clock = new MockClock('2026-10-16 10:00:00');
        $loader = new SafeXmlLoader();

        return new PunchOutSimulatorController(
            $twig,
            $this->createMock(UrlGeneratorInterface::class),
            $csrf,
            new BuyerCredentialsProvider($this->createMock(EntityManagerInterface::class)),
            new SetupRequestBuilder($clock),
            new OrderRequestBuilder($clock),
            new OrderMessageParser($loader),
            new ResponseParser($loader),
            new EndpointClient(new MockHttpClient()),
            $this->state(),
            $demoMode,
        );
    }
}
