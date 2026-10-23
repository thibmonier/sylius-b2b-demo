<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Smoke tests of the demo (US-005): the key pages answer on both channels, with every plugin
 * installed. Run against a database loaded with the `demo` fixture suite.
 */
final class PagesTest extends WebTestCase
{
    public function testTheHealthcheckAnswers(): void
    {
        $client = self::createClient();
        $client->request('GET', '/healthz');

        self::assertResponseIsSuccessful();
        self::assertSame('ok', $client->getResponse()->getContent());
    }

    public function testTheB2bChannelIsServedByDefault(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Fournitures Pro — B2B', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('/withdrawal', (string) $client->getResponse()->getContent(), 'No withdrawal on the B2B channel');
    }

    public function testTheSwitcherServesTheB2cChannelWithItsConsumerFeatures(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        self::assertResponseRedirects();
        $client->followRedirects();
        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Fournitures Pro — Particuliers', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('/withdrawal', (string) $client->getResponse()->getContent(), 'Withdrawal link on the B2C channel');
    }

    public function testAnUnknownChannelCodeFallsBackToB2b(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie('demo_channel', 'ANY'));
        $client->request('GET', '/en_US/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Fournitures Pro — B2B', (string) $client->getResponse()->getContent());
    }

    /** @return iterable<string, array{string}> */
    public static function shopPages(): iterable
    {
        yield 'login' => ['/fr_FR/login'];
        yield 'register' => ['/fr_FR/register'];
        yield 'quick order' => ['/fr_FR/quick-order'];
        yield 'english home' => ['/en_US/'];
    }

    #[DataProvider('shopPages')]
    public function testShopPagesAnswer(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    /** US-019: the B2B channel hides prices and purchase from anonymous visitors, the cart included. */
    public function testTheCartAsksAnAnonymousB2bVisitorToSignIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/cart/');

        self::assertResponseRedirects('/fr_FR/login');
    }

    /** @return iterable<string, array{string}> */
    public static function adminPages(): iterable
    {
        yield 'dashboard' => ['/admin/'];
        yield 'orders' => ['/admin/orders/'];
        yield 'customers' => ['/admin/customers/'];
        yield 'products' => ['/admin/products/'];
        yield 'channels' => ['/admin/channels/'];
        yield 'punchout buyers' => ['/admin/punchout-buyers/'];
        yield 'companies' => ['/admin/companies/'];
        yield 'audit log' => ['/admin/audit-log'];
        yield 'quote requests' => ['/admin/quote-requests/'];
        yield 'webhooks' => ['/admin/webhooks/endpoints/'];
    }

    #[DataProvider('adminPages')]
    public function testAdminPagesAnswer(string $path): void
    {
        $client = self::createClient();
        $repository = self::getContainer()->get('sylius.repository.admin_user');
        self::assertInstanceOf(\Doctrine\Persistence\ObjectRepository::class, $repository);
        $admin = $repository->findOneBy(['username' => 'sophie']);
        self::assertInstanceOf(AdminUserInterface::class, $admin);
        $client->loginUser($admin, 'admin');
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }
}
