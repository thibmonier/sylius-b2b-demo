<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Http\OutboundRequestRefused;
use App\Demo\Webhook\DemoWebhookTarget;
use App\Demo\Webhook\ReceivedWebhookStore;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * US-029 / T-029-05: the whole chain, with a real HTTP server: an order is placed, the outbox queues the event, the worker
 * delivers it through the guarded webhook transport to the receiver of the demo (a second PHP server running this
 * application behind the private gate), and Sophie sees it. Also: an external host is refused before any connection and a
 * redirect is not followed. Run against a database loaded with the `demo` fixture suite; the rows the test adds are removed.
 */
final class WebhookDeliveryTest extends DemoAdminWebTestCase
{
    use EnvironmentSwitch;

    private const ENDPOINT = 'Démo — récepteur interne';

    /** @var resource|null */
    private $server;

    private ?string $capture = null;

    /** @var array{url: string}|null */
    private ?array $endpointBefore = null;

    /** @var list<string> */
    private array $eventsBefore = [];

    protected function tearDown(): void
    {
        if (\is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        if (null !== $this->capture) {
            @unlink($this->capture);
        }
        if (null !== $this->endpointBefore) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $this->db()->executeStatement('DELETE FROM tmonier_webhook_delivery WHERE event_id NOT IN (' . implode(',', array_fill(0, max(1, \count($this->eventsBefore)), '?')) . ')', $this->eventsBefore ?: ['-']);
            $this->db()->executeStatement('DELETE FROM tmonier_webhook_outbox_event WHERE id NOT IN (' . implode(',', array_fill(0, max(1, \count($this->eventsBefore)), '?')) . ')', $this->eventsBefore ?: ['-']);
            $this->db()->executeStatement('UPDATE tmonier_webhook_endpoint SET url = ? WHERE name = ?', [$this->endpointBefore['url'], self::ENDPOINT]);
            $this->db()->executeStatement('DELETE FROM app_received_webhook');
        }
        parent::tearDown();
    }

    public function testAnOrderPlacedIsDeliveredSignedToTheReceiverAndShownToSophie(): void
    {
        $port = $this->startServer(__DIR__ . '/../../../public/index.php', gated: true);
        $this->setEnvironment(['PORT' => (string) $port]);
        self::bootKernel();
        $this->pointTheDemoEndpointToTheReceiver();

        $number = $this->placeAnOrder();
        $worker = new CommandTester((new Application(self::$kernel ?? throw new \LogicException()))->find('tmonier:webhook:dispatch'));
        $worker->execute(['--limit' => '200']);

        $delivery = $this->db()->fetchAssociative('SELECT d.status, d.last_response_status, d.last_response_body, d.last_error FROM tmonier_webhook_delivery d JOIN tmonier_webhook_outbox_event e ON e.id = d.event_id WHERE e.type = ? AND d.id = (SELECT MAX(id) FROM tmonier_webhook_delivery)', ['order.placed']);
        self::assertIsArray($delivery);
        self::assertSame(['delivered', 200], [$delivery['status'], is_numeric($delivery['last_response_status']) ? (int) $delivery['last_response_status'] : null], 'the receiver answered 2xx through the private gate: ' . json_encode($delivery));
        self::assertGreaterThanOrEqual(1, $this->intValue("SELECT COUNT(*) FROM app_received_webhook WHERE event_type = 'order.placed' AND signature_verified = 1 AND payload LIKE ?", ['%' . $number . '%']));

        // Sophie browses the admin in this process, not through the gated server.
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '0', 'TRUSTED_HOSTS' => '^[a-z0-9.-]+$']);
        self::ensureKernelShutdown();
        $client = $this->sophieClient();
        $page = $client->request('GET', '/admin/demo/webhooks');
        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(1, $page->filter('[data-demo-webhook-row] [data-demo-webhook-signature="verified"]')->count());
        $detail = $client->click($page->filter('[data-demo-webhook-row] a')->link());
        self::assertStringContainsString('order.placed', $detail->filter('[data-demo-webhook-type]')->text());
    }

    public function testAnExternalHostIsRefusedBeforeAnyConnectionAndTheWorkerRecordsTheFailure(): void
    {
        self::bootKernel();
        foreach (['tmonier_webhook.http_client.transport', 'http_client'] as $service) {
            $client = self::getContainer()->get($service);
            self::assertInstanceOf(HttpClientInterface::class, $client);

            try {
                $client->request('POST', 'https://hooks.partner.example/webhook', ['body' => '{}']);
                self::fail($service . ' must refuse an external host');
            } catch (OutboundRequestRefused) {
                // expected: refused before any connection
            }
        }
        $webhookClient = self::getContainer()->get('tmonier_webhook.http_client');
        self::assertInstanceOf(HttpClientInterface::class, $webhookClient);
        foreach (['https://hooks.partner.example/webhook', 'http://localhost.evil.example:8080/demo/webhooks/receive'] as $external) {
            try {
                $webhookClient->request('POST', $external, ['body' => '{}']);
                self::fail($external . ' must be refused');
            } catch (TransportExceptionInterface) {
                // expected: the plugin client or the demo guard refuses it, no connection is made
            }
        }

        try {
            // Loopback is open for the receiver: the demo guard still refuses any other loopback URL.
            $webhookClient->request('POST', 'http://127.0.0.1:9/other', ['body' => '{}']);
            self::fail('another loopback URL must be refused');
        } catch (OutboundRequestRefused) {
            // expected
        }

        $this->rememberState();
        $this->db()->executeStatement('UPDATE tmonier_webhook_endpoint SET url = ? WHERE name = ?', ['http://127.0.0.1:9/other', self::ENDPOINT]);
        $this->placeAnOrder();
        (new CommandTester((new Application(self::$kernel ?? throw new \LogicException()))->find('tmonier:webhook:dispatch')))->execute(['--limit' => '200']);

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
        $failed = $this->db()->fetchOne("SELECT last_error FROM tmonier_webhook_delivery WHERE status <> 'delivered' ORDER BY id DESC LIMIT 1");
        self::assertIsString($failed);
        self::assertStringContainsString('demo receiver', $failed);
    }

    public function testARedirectFromTheReceiverIsNotFollowed(): void
    {
        $port = $this->startServer(__DIR__ . '/Fixtures/redirect-router.php', gated: false);
        $this->setEnvironment(['PORT' => (string) $port]);
        self::bootKernel();
        $target = self::getContainer()->get(DemoWebhookTarget::class);
        self::assertInstanceOf(DemoWebhookTarget::class, $target);
        $client = self::getContainer()->get('tmonier_webhook.http_client');
        self::assertInstanceOf(HttpClientInterface::class, $client);

        $status = $client->request('POST', $target->url(), ['body' => '{}'])->getStatusCode();

        self::assertSame(302, $status, 'the 3xx is handed back as it is');
        self::assertNotNull($this->capture);
        self::assertFileDoesNotExist($this->capture, 'nothing reached the redirect target');
    }

    private function pointTheDemoEndpointToTheReceiver(): void
    {
        $target = self::getContainer()->get(DemoWebhookTarget::class);
        self::assertInstanceOf(DemoWebhookTarget::class, $target);
        $this->rememberState();
        $this->db()->executeStatement('UPDATE tmonier_webhook_endpoint SET url = ? WHERE name = ?', [$target->url(), self::ENDPOINT]);
        self::getContainer()->get(ReceivedWebhookStore::class);
        $this->db()->executeStatement('DELETE FROM app_received_webhook');
    }

    private function rememberState(): void
    {
        $this->endpointBefore = ['url' => $this->stringValue('SELECT url FROM tmonier_webhook_endpoint WHERE name = ?', [self::ENDPOINT])];
        $this->eventsBefore = array_map(static fn (mixed $id): string => \is_string($id) ? $id : '', $this->db()->fetchFirstColumn('SELECT id FROM tmonier_webhook_outbox_event'));
    }

    /** Re-completes an existing order: the checkout state moves to "completed", which the outbox records as order.placed. */
    private function placeAnOrder(): string
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $row = $this->db()->fetchAssociative("SELECT id, number FROM sylius_order WHERE state = 'new' AND checkout_state = 'completed' ORDER BY id LIMIT 1");
        self::assertIsArray($row, 'the demo fixtures must be loaded');
        $this->db()->executeStatement("UPDATE sylius_order SET checkout_state = 'cart' WHERE id = ?", [$row['id']]);
        $orders = self::getContainer()->get('sylius.repository.order');
        self::assertInstanceOf(RepositoryInterface::class, $orders);
        $order = $orders->find($row['id']);
        self::assertInstanceOf(OrderInterface::class, $order);
        $entityManager->refresh($order);

        $order->setCheckoutState('completed');
        $entityManager->flush();

        self::assertIsString($row['number']);

        return $row['number'];
    }

    /** @return int the port of a PHP server running $router, on the loopback */
    private function startServer(string $router, bool $gated): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($socket);
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        $port = (int) substr($name, (int) strrpos($name, ':') + 1);

        $this->capture = sys_get_temp_dir() . '/webhook-redirect-' . bin2hex(random_bytes(4)) . '.log';
        if ($gated) {
            $this->setEnvironment([
                'DEMO_BASIC_AUTH' => '1',
                'DEMO_BASIC_AUTH_USER' => 'visitor',
                'DEMO_BASIC_AUTH_PASSWORD_HASH' => self::gateHash('correct horse battery'),
                // The outbox calls 127.0.0.1: it must be trusted next to the public host.
                'TRUSTED_HOSTS' => '^shop\.test$',
            ]);
        }
        $this->setEnvironment(['PORT' => (string) $port]);

        $server = proc_open(
            [\PHP_BINARY, '-d', 'memory_limit=-1', '-d', 'variables_order=EGPCS', '-S', '127.0.0.1:' . $port, '-t', \dirname($router), $router],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            \dirname($router),
            ['CAPTURE_FILE' => $this->capture, 'REDIRECT_TARGET' => 'http://127.1:' . $port . '/hit'] + getenv(),
        );
        self::assertIsResource($server);
        $this->server = $server;
        for ($try = 0; $try < 100 && false === @fsockopen('127.0.0.1', $port); ++$try) {
            usleep(100_000);
        }

        return $port;
    }
}
