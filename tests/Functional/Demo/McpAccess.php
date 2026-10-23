<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\Typed;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Tmonier\SyliusB2bMcpPlugin\Entity\Connection;
use Tmonier\SyliusB2bMcpPlugin\Entity\OAuthClient;
use Tmonier\SyliusB2bMcpPlugin\Entity\OAuthToken;
use Tmonier\SyliusB2bMcpPlugin\OAuth\ScopeCatalog;
use Tmonier\SyliusB2bMcpPlugin\OAuth\TokenStore;

/**
 * An AI assistant connected to a demo customer, for the functional tests: an access token as the OAuth flow would have
 * issued it (the tables are the ones of the b2b-mcp plugin), and calls to the MCP endpoint with it.
 */
trait McpAccess
{
    /** An access token of an AI assistant connected to the account of the customer. */
    private function mcpAccessToken(string $email, string $lifetime = '+1 hour'): string
    {
        $container = self::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $tokens = $container->get('tmonier_b2b_mcp.oauth.token_store');
        self::assertInstanceOf(TokenStore::class, $tokens);
        $scopes = $container->get('tmonier_b2b_mcp.oauth.scope_catalog');
        self::assertInstanceOf(ScopeCatalog::class, $scopes);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer);

        $oauthClient = new OAuthClient('test-assistant-' . bin2hex(random_bytes(4)), 'Test assistant', ['https://assistant.example/callback']);
        $em->persist($oauthClient);
        $connection = new Connection();
        $connection->setClient($oauthClient);
        $connection->setCustomer($customer);
        $connection->setChannelCode('B2B');
        $connection->setScopes($scopes->supported());
        $em->persist($connection);
        $em->flush();

        $identifier = bin2hex(random_bytes(24));
        $tokens->store(OAuthToken::TYPE_ACCESS, $identifier, $connection, $scopes->supported(), new \DateTimeImmutable($lifetime));
        $em->clear();

        return $identifier;
    }

    /**
     * One JSON-RPC request to the MCP endpoint; without a token the call is anonymous.
     *
     * @param array<string, mixed> $params
     */
    private function mcpCall(KernelBrowser $client, ?string $token, string $method = 'ping', array $params = [], ?string $session = null): void
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json, text/event-stream'];
        if (null !== $token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }
        if (null !== $session) {
            $server['HTTP_MCP_SESSION_ID'] = $session;
        }
        $client->request('POST', '/_mcp', server: $server, content: (string) json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => $method] + ([] !== $params ? ['params' => $params] : [])));
    }

    /** Opens an MCP session as the protocol does (initialize) and returns its identifier. */
    private function mcpSession(KernelBrowser $client, string $token): string
    {
        $this->mcpCall($client, $token, 'initialize', ['protocolVersion' => '2025-03-26', 'capabilities' => new \stdClass(), 'clientInfo' => ['name' => 'demo-test', 'version' => '1.0']]);
        $session = $client->getResponse()->headers->get('Mcp-Session-Id');
        self::assertIsString($session, 'initialize answers with a session: ' . $client->getResponse()->getContent());

        return $session;
    }

    /**
     * The result of a tool call, decoded from the text content of the answer.
     *
     * @param array<string, mixed> $arguments
     *
     * @return array<string, mixed>
     */
    private function mcpTool(KernelBrowser $client, string $token, string $tool, array $arguments = []): array
    {
        $session = $this->mcpSession($client, $token);
        $this->mcpCall($client, $token, 'tools/call', ['name' => $tool, 'arguments' => [] === $arguments ? new \stdClass() : $arguments], $session);
        self::assertResponseIsSuccessful();
        $answer = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($answer);
        $text = Typed::find($answer, 'result', 'content', 0, 'text');
        self::assertIsString($text, 'a tool answer: ' . json_encode($answer));
        $data = json_decode($text, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
