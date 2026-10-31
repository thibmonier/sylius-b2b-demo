<?php

declare(strict_types=1);

namespace App\Tests\Functional\Copilot;

use App\Demo\DataFixtures\CompanyWishlistDemoFixture;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-247: the copilot's recorded answer to "état de mon panier" / "what's in my cart" must match the actual items and
 * total of Claire's cart (seeded from CompanyWishlistDemoFixture::ITEMS by CartDemoFixture).
 */
final class CartStateResponseTest extends WebTestCase
{
    private const EXPECTED_ITEM_COUNT = 47;
    private const EXPECTED_TOTAL_HT_CENTS = 96350;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->login('claire@acme.example');
    }

    public function testFixtureItemCountMatchesConstant(): void
    {
        self::assertSame(self::EXPECTED_ITEM_COUNT, array_sum(CompanyWishlistDemoFixture::ITEMS));
    }

    public function testFrenchResponseContainsCorrectItemCountAndTotal(): void
    {
        $answer = $this->ask('/fr_FR/api/copilot/chat', 'état de mon panier');

        self::assertStringContainsString('47', $answer, 'FR response must mention 47 items');
        self::assertStringContainsString('963,50', $answer, 'FR response must mention 963,50 € HT');
    }

    public function testEnglishResponseContainsCorrectItemCountAndTotal(): void
    {
        $answer = $this->ask('/en_US/api/copilot/chat', "what's in my cart");

        self::assertStringContainsString('47', $answer, 'EN response must mention 47 items');
        self::assertStringContainsString('963.50', $answer, 'EN response must mention €963.50 excl. tax');
    }

    private function ask(string $uri, string $message): string
    {
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['message' => $message], \JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();
        $body = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertIsString($body['message'] ?? null);

        return $body['message'];
    }

    private function login(string $email): void
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $this->client->loginUser($user, 'shop');
    }
}
