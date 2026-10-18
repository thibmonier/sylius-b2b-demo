<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-087: no raw translation key (sylius.ui.xxx, tmonier_xxx.ui.xxx, app.xxx.yyy) is ever visible on the
 * key pages of the shop and of the back office, in French and in English. The failure names the page.
 *
 * "/admin/punchout-buyers/" is left out until the PunchOut plugin release that translates its title (US-070).
 */
final class RawTranslationKeysSmokeTest extends WebTestCase
{
    private const RAW_KEY = '/\b(?:sylius|app|about|demo|tmonier_[a-z0-9_]+)\.[a-z0-9_]+(?:\.[a-z0-9_]+)+\b/';

    /** @return iterable<string, array{string}> */
    public static function shopPages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            foreach (['/', '/plugins', '/a-propos', '/login', '/quick-order'] as $path) {
                yield $locale . $path => ['/' . $locale . $path];
            }
        }
    }

    #[DataProvider('shopPages')]
    public function testAnonymousShopPagesShowNoRawKey(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertNoRawKey((string) $client->getResponse()->getContent(), $path);
    }

    /** The empty quote request page showed `sylius.ui.continue_shopping`. */
    #[DataProvider('signedInShopPages')]
    public function testSignedInShopPagesShowNoRawKey(string $path): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => 'claire@acme.example']);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertNoRawKey((string) $client->getResponse()->getContent(), $path);
    }

    /** @return iterable<string, array{string}> */
    public static function signedInShopPages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            foreach (['/quote-request', '/cart/'] as $path) {
                yield $locale . $path => ['/' . $locale . $path];
            }
        }
    }

    /** @return iterable<string, array{string}> */
    public static function adminPages(): iterable
    {
        foreach (['/admin/', '/admin/orders/', '/admin/customers/', '/admin/products/', '/admin/companies/', '/admin/audit-log', '/admin/quote-requests/'] as $path) {
            yield $path => [$path];
        }
    }

    #[DataProvider('adminPages')]
    public function testAdminPagesShowNoRawKey(string $path): void
    {
        $client = self::createClient();
        $repository = self::getContainer()->get('sylius.repository.admin_user');
        self::assertInstanceOf(ObjectRepository::class, $repository);
        $admin = $repository->findOneBy(['username' => 'sophie']);
        self::assertInstanceOf(AdminUserInterface::class, $admin);
        $client->loginUser($admin, 'admin');

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertNoRawKey((string) $client->getResponse()->getContent(), $path);
    }

    public function testTheDashboardChartSeriesAreTranslated(): void
    {
        $client = self::createClient();
        $repository = self::getContainer()->get('sylius.repository.admin_user');
        self::assertInstanceOf(ObjectRepository::class, $repository);
        $admin = $repository->findOneBy(['username' => 'sophie']);
        self::assertInstanceOf(AdminUserInterface::class, $admin);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/');
        $chart = $client->getCrawler()->filter('#statistics-chart');

        self::assertCount(1, $chart);
        self::assertStringNotContainsString('dashboard.', (string) $chart->attr('data-sales-label'));
        self::assertStringNotContainsString('dashboard.', (string) $chart->attr('data-paid-orders-count-label'));
        self::assertNotSame('Sales', $chart->attr('data-sales-label'));
        self::assertNotSame('Paid orders count', $chart->attr('data-paid-orders-count-label'));
        self::assertNotSame('', trim((string) $chart->attr('data-sales-label')));
        self::assertNotSame('', trim((string) $chart->attr('data-paid-orders-count-label')));
    }

    private static function assertNoRawKey(string $html, string $path): void
    {
        $html = (string) preg_replace('#<(script|style|code|pre)\b.*?</\1>#is', '', $html);
        $html = (string) preg_replace('#<!--.*?-->#s', '', $html);
        $text = html_entity_decode(strip_tags((string) preg_replace('#<[^>]*\b(?:href|src|action|content|data-[a-z-]+|value|id|class)="[^"]*"[^>]*>#i', '<x>', $html)), \ENT_QUOTES);

        preg_match_all(self::RAW_KEY, $text, $matches);
        self::assertSame([], array_values(array_unique($matches[0])), 'raw translation key(s) visible on ' . $path);
    }
}
