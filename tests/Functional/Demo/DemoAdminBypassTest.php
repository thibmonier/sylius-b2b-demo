<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * US-035: requests built by hand, without the user interface, meet the same wall as the forms: valid CSRF token,
 * `_method` override, bulk actions, routes nobody classified, public password-reset routes, stored script.
 */
final class DemoAdminBypassTest extends DemoAdminWebTestCase
{
    private const MESSAGE_FR = 'Cette action est désactivée dans la démo publique';

    public function testADirectPostWithAValidCsrfTokenAndAMethodOverrideIsRefused(): void
    {
        $client = $this->sophieClient();
        $channelId = $this->intValue("SELECT id FROM sylius_channel WHERE code = 'B2B'");
        $before = $this->snapshot('sylius_channel');
        $page = $this->editForm($client, '/admin/channels/' . $channelId . '/edit');
        $this->setValue($page['values'], 'sylius_admin_channel.name', 'Hacked');

        $client->request('POST', '/admin/channels/' . $channelId . '/edit', ['_method' => 'PUT'] + $page['values']);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame($before, $this->snapshot('sylius_channel'));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function provideDeletions(): iterable
    {
        yield 'delete a channel through _method' => ['/admin/channels/', 'POST', '/admin/channels/{id}'];
        yield 'delete a punchout buyer through _method' => ['/admin/punchout-buyers/', 'POST', '/admin/punchout-buyers/{id}'];
        yield 'bulk delete of channels' => ['/admin/channels/', 'POST', '/admin/channels/bulk-delete'];
        yield 'delete a product (deletion is refused everywhere)' => ['/admin/products/', 'POST', '/admin/products/{id}'];
    }

    #[DataProvider('provideDeletions')]
    public function testDeletionsAreRefusedEvenWithAValidTokenFromTheListPage(string $listUrl, string $verb, string $target): void
    {
        $client = $this->sophieClient();
        $tables = ['sylius_channel', 'tmonier_punchout_buyer', 'sylius_product'];
        $before = $this->snapshot(...$tables);
        $token = $this->csrfTokenFromList($client, $listUrl);
        $id = $this->intValue(str_contains($target, 'products') ? 'SELECT MIN(id) FROM sylius_product' : (str_contains($target, 'punchout') ? 'SELECT MIN(id) FROM tmonier_punchout_buyer' : 'SELECT MIN(id) FROM sylius_channel'));

        $client->request($verb, str_replace('{id}', (string) $id, $target), ['_method' => 'DELETE', '_csrf_token' => $token, 'ids' => [$id]]);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame($before, $this->snapshot(...$tables));
    }

    public function testTheMethodOverrideHeaderDoesNotTurnAWriteIntoARead(): void
    {
        $client = $this->sophieClient();
        $before = $this->snapshot('sylius_channel');
        $channelId = $this->intValue("SELECT id FROM sylius_channel WHERE code = 'B2B'");

        $client->request('POST', '/admin/channels/' . $channelId . '/edit', ['sylius_admin_channel' => ['name' => 'Hacked']], [], ['HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT']);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame($before, $this->snapshot('sylius_channel'));
    }

    public function testAPostDisguisedAsAGetStillCountsAsAWrite(): void
    {
        $client = $this->sophieClient();
        $channelId = $this->intValue("SELECT id FROM sylius_channel WHERE code = 'B2B'");

        $client->request('POST', '/admin/channels/' . $channelId . '/edit', ['_method' => 'GET', 'sylius_admin_channel' => ['name' => 'Hacked']]);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
    }

    public function testABulkActionOnCustomersIsRefusedWhileOneOnOrdersIsNot(): void
    {
        $client = $this->sophieClient();
        $before = $this->snapshot('sylius_customer', 'sylius_shop_user');

        $client->request('POST', '/admin/bulk-actions/customer_disable_account/execute', ['ids' => [1, 2, 3]]);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame($before, $this->snapshot('sylius_customer', 'sylius_shop_user'));

        $client->request('POST', '/admin/bulk-actions/order_cancel', ['ids' => []]);
        self::assertNotSame(403, $client->getResponse()->getStatusCode(), 'The bulk actions on orders stay available.');
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideUnclassifiedWriteRoutes(): iterable
    {
        yield 'new promotion' => ['POST', '/admin/promotions/new'];
        yield 'new tax rate' => ['POST', '/admin/tax-rates/new'];
        yield 'archive a shipping method' => ['PATCH', '/admin/shipping-methods/1/archive'];
        yield 'new exchange rate' => ['POST', '/admin/exchange-rates/new'];
        yield 'new locale' => ['POST', '/admin/locales/new'];
    }

    #[DataProvider('provideUnclassifiedWriteRoutes')]
    public function testAWriteRouteNobodyClassifiedIsRefusedByDefault(string $method, string $url): void
    {
        $client = $this->sophieClient();

        $client->request($method, $url, ['anything' => 'x']);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
    }

    public function testNobodyCanResetTheSharedPasswordThroughTheForgottenPasswordRoutes(): void
    {
        $client = self::createClient();
        $hash = $this->stringValue('SELECT password FROM sylius_admin_user WHERE email_canonical = ?', [self::SOPHIE]);

        $client->request('POST', '/admin/forgotten-password', ['sylius_admin_request_password_reset' => ['email' => self::SOPHIE]]);
        self::assertResponseStatusCodeSame(403);
        self::assertMatchesRegularExpression('/disabled in the public demo|désactivée dans la démo publique/', (string) $client->getResponse()->getContent());

        $client->request('POST', '/admin/forgotten-password/some-token', ['sylius_admin_user_reset_password' => ['password' => ['first' => 'Hacked-12345678!', 'second' => 'Hacked-12345678!']]]);
        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('This action is disabled in the public demo', (string) $client->getResponse()->getContent());

        self::assertSame($hash, $this->stringValue('SELECT password FROM sylius_admin_user WHERE email_canonical = ?', [self::SOPHIE]));
        self::assertNull($this->nullableStringValue('SELECT password_reset_token FROM sylius_admin_user WHERE email_canonical = ?', [self::SOPHIE]));
    }

    public function testAScriptSavedByTheEditorIsEscapedOnTheShop(): void
    {
        $client = $this->sophieClient();
        $productId = $this->intValue("SELECT translatable_id FROM sylius_product_translation WHERE slug = 'epi-cask-001-fr'");
        $original = $this->db()->fetchAssociative('SELECT name, description FROM sylius_product_translation WHERE translatable_id = ? AND locale = ?', [$productId, 'fr_FR']);

        try {
            $page = $this->editForm($client, '/admin/products/' . $productId . '/edit');
            $payload = '<script>window.__xss=1</script><img src=x onerror=alert(1)></script><script>alert(2)</script>';
            $this->setValue($page['values'], 'sylius_admin_product.translations.fr_FR.description', $payload);
            $this->setValue($page['values'], 'sylius_admin_product.translations.fr_FR.name', '<script>alert(3)</script>');
            $client->request($page['method'], $page['uri'], $page['values']);
            self::assertResponseRedirects();
            self::assertSame($payload, $this->stringValue('SELECT description FROM sylius_product_translation WHERE translatable_id = ? AND locale = ?', [$productId, 'fr_FR']), 'The script was really saved by the allowed edit.');

            self::ensureKernelShutdown();
            $shop = self::createClient();
            $shop->request('GET', '/fr_FR/products/epi-cask-001-fr');
            self::assertResponseIsSuccessful();
            $page = $shop->getCrawler();
            // Judged on the parsed page, not on the bytes: the test environment prints some values raw inside
            // data-test-* attributes (inert, and absent from prod), what matters is what a browser would run.
            foreach ($page->filter('script:not([type="application/ld+json"])') as $script) {
                self::assertStringNotContainsString('__xss', (string) $script->textContent);
                self::assertStringNotContainsString('alert(', (string) $script->textContent);
            }
            foreach ($page->filter('script[type="application/ld+json"]') as $jsonLd) {
                self::assertIsArray(json_decode((string) $jsonLd->textContent, true), 'The structured data stays valid JSON, the script cannot break out of it.');
            }
            self::assertCount(0, $page->filterXPath('//*[@onerror or @onload or @onclick or @onfocus]'), 'No event handler injected by the content.');
            self::assertStringContainsString('&lt;script&gt;', (string) $shop->getResponse()->getContent(), 'The content is displayed, escaped.');
        } finally {
            $this->db()->executeStatement('UPDATE sylius_product_translation SET name = ?, description = ? WHERE translatable_id = ? AND locale = ?', [...array_values((array) $original), $productId, 'fr_FR']);
        }
    }

    private function csrfTokenFromList(KernelBrowser $client, string $listUrl): string
    {
        $crawler = $client->request('GET', $listUrl);
        self::assertResponseIsSuccessful();
        $token = $crawler->filterXPath('//form//input[@name="_csrf_token"]')->first();
        self::assertCount(1, $token, 'The list page exposes a CSRF token in its delete forms.');

        return (string) $token->attr('value');
    }
}
