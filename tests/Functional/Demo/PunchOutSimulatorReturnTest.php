<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\PunchOutSimulator\SignedState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderMessageWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Log\MessageLog;
use Tmonier\SyliusPunchoutCxmlPlugin\Log\MessageLogger;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/**
 * US-040 / M8: the cart that comes back to the simulator is only taken for the one the shop sent: byte for byte the
 * PunchOutOrderMessage the plugin logged for its own transfer, once, and while the session lives. A forged, replayed or
 * expired return shows no order form and creates nothing.
 */
final class PunchOutSimulatorReturnTest extends WebTestCase
{
    use DatabaseAccess;

    public function testTheMessageTheShopSentIsAcceptedAndShowsTheOrderForm(): void
    {
        $client = self::createClient();
        $xml = $this->shopMessage(quantity: 2);

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($xml)]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Gants nitrile');
        self::assertSelectorExists('form[action*="/demo/punchout-simulator/order"] input[name="orderId"]');
    }

    public function testACartTheShopNeverSentIsRefused(): void
    {
        $client = self::createClient();
        $orders = $this->intValue('SELECT COUNT(*) FROM sylius_order');
        $unlogged = $this->message(quantity: 2, payloadId: 'forged-' . bin2hex(random_bytes(6)) . '@x');

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($unlogged)]);

        $this->assertRefused($client, 403);
        self::assertSame($orders, $this->intValue('SELECT COUNT(*) FROM sylius_order'));
    }

    public function testAFalsifiedCartWithAValidSessionIsRefused(): void
    {
        $client = self::createClient();
        $cookie = $this->cookie();
        $genuine = $this->shopMessage(quantity: 1, cookie: $cookie);
        $falsified = str_replace(['quantity="1"', '12.50'], ['quantity="500"', '0.01'], $genuine);
        self::assertNotSame($genuine, $falsified);

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($falsified)]);

        $this->assertRefused($client, 403);
    }

    public function testAReplayedReturnIsRefused(): void
    {
        $client = self::createClient();
        $field = ['cXML-base64' => base64_encode($this->shopMessage(quantity: 1))];

        $client->request('POST', '/demo/punchout-simulator/return', $field);
        self::assertResponseIsSuccessful();
        $client->request('POST', '/demo/punchout-simulator/return', $field);

        $this->assertRefused($client, 410);
        self::assertStringContainsString('déjà été reçu', (string) $client->getResponse()->getContent());
    }

    public function testAReturnThatTheShopSentTooLongAgoIsRefused(): void
    {
        $client = self::createClient();
        $payloadId = 'old-' . bin2hex(random_bytes(6)) . '@x';
        $xml = $this->shopMessage(quantity: 1, payloadId: $payloadId);
        $this->db()->executeStatement('UPDATE tmonier_punchout_message_log SET created_at = ? WHERE payload_id = ?', [(new \DateTimeImmutable('-3 hours'))->format('Y-m-d H:i:s'), $payloadId]);

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($xml)]);

        $this->assertRefused($client, 410);
    }

    private function assertRefused(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, int $status): void
    {
        self::assertResponseStatusCodeSame($status);
        self::assertSelectorExists('[role="alert"]');
        self::assertSelectorNotExists('form[action*="/order"]');
        self::assertSelectorNotExists('input[name="state"]');
    }

    /** What the plugin sends back, and what it writes in its journal when it does (MessageLogger, as TransferAction). */
    private function shopMessage(int $quantity, ?string $cookie = null, ?string $payloadId = null): string
    {
        $payloadId ??= 'shop-' . bin2hex(random_bytes(6)) . '@x';
        $xml = $this->message($quantity, $payloadId, $cookie);
        $logger = self::getContainer()->get(MessageLogger::class);
        self::assertInstanceOf(MessageLogger::class, $logger);
        $logger->log(MessageLog::DIRECTION_OUT, 'PunchOutOrderMessage', $payloadId, 200, $xml);

        return $xml;
    }

    private function message(int $quantity, string $payloadId, ?string $cookie = null): string
    {
        return (new OrderMessageWriter())->write(
            [new TransferLine('SKU-1', 'v1:SKU-1', 'Gants nitrile', $quantity, '12.50', 'EUR')],
            number_format($quantity * 12.5, 2, '.', ''),
            'EUR',
            $cookie ?? $this->cookie(),
            $payloadId,
            'create',
        );
    }

    private function cookie(): string
    {
        $state = self::getContainer()->get(SignedState::class);
        self::assertInstanceOf(SignedState::class, $state);

        return $state->sign(['nonce' => bin2hex(random_bytes(8))]);
    }
}
