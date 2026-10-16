<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * US-035: Sophie (ROLE_DEMO_ADMIN) reads everything in the back office and edits the non-sensitive data, but the
 * sensitive writes are refused with the demo message, data unchanged. Requires a database loaded with the `demo` suite.
 */
final class DemoAdminRestrictionTest extends DemoAdminWebTestCase
{
    private const MESSAGE_FR = 'Cette action est désactivée dans la démo publique';

    private const MESSAGE_EN = 'This action is disabled in the public demo';

    /** @return iterable<string, array{string}> */
    public static function provideSensitiveReadPages(): iterable
    {
        yield 'administrators' => ['/admin/users/'];
        yield 'channels' => ['/admin/channels/'];
        yield 'payment methods' => ['/admin/payment-methods/'];
        yield 'webhook endpoints' => ['/admin/webhooks/endpoints/'];
        yield 'punchout buyers' => ['/admin/punchout-buyers/'];
        yield 'customers' => ['/admin/customers/'];
        yield 'dashboard' => ['/admin/'];
    }

    #[DataProvider('provideSensitiveReadPages')]
    public function testSophieCanStillReadTheSensitiveResources(string $url): void
    {
        $client = $this->sophieClient();

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    /** @return iterable<string, array{string, string, string, mixed, list<string>}> */
    public static function provideSensitiveEdits(): iterable
    {
        yield 'administrator password and e-mail (public account protected)' => [
            '/admin/users/{user}/edit', 'sylius_admin_admin_user', 'plainPassword', 'Hacked-12345678!', ['sylius_admin_user'],
        ];
        yield 'channel' => ['/admin/channels/{channel}/edit', 'sylius_admin_channel', 'name', 'Hacked channel', ['sylius_channel']];
        yield 'payment gateway / method' => [
            '/admin/payment-methods/{payment_method}/edit', 'sylius_admin_payment_method', 'translations.fr_FR.name', 'Hacked',
            ['sylius_payment_method', 'sylius_payment_method_translation', 'sylius_gateway_config'],
        ];
        yield 'punchout buyer (URL and secrets)' => [
            '/admin/punchout-buyers/{buyer}/edit', 'tmonier_punchout_buyer', 'name', 'Hacked buyer',
            ['tmonier_punchout_buyer', 'tmonier_punchout_buyer_credential'],
        ];
        yield 'customer account (password field)' => [
            '/admin/customers/{customer}/edit', 'sylius_admin_customer', 'user.plainPassword', 'Hacked-12345678!', ['sylius_customer', 'sylius_shop_user'],
        ];
    }

    /**
     * @param list<string> $tables
     */
    #[DataProvider('provideSensitiveEdits')]
    public function testASensitiveEditIsRefusedAndTheDataIsUnchanged(string $url, string $form, string $field, mixed $value, array $tables): void
    {
        $client = $this->sophieClient();
        $before = $this->snapshot(...$tables);
        $page = $this->editForm($client, $this->resolve($url));
        $this->setValue($page['values'], $form . '.' . $field, $value);

        $client->request($page['method'], $page['uri'], $page['values']);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame($before, $this->snapshot(...$tables), 'A refused write must not change anything.');
    }

    public function testAWebhookEndpointCannotBeCreatedAndNoUrlIsStored(): void
    {
        $client = $this->sophieClient();
        $page = $this->editForm($client, '/admin/webhooks/endpoints/new');
        $this->setValue($page['values'], 'tmonier_webhook_endpoint.name', 'exfiltration');
        $this->setValue($page['values'], 'tmonier_webhook_endpoint.url', 'https://attacker.example/hook');

        $client->request($page['method'], $page['uri'], $page['values']);

        $this->assertDemoRefusal($client, self::MESSAGE_FR);
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_webhook_endpoint'));
    }

    public function testTheRefusalMessageIsInEnglishForAnEnglishBackOffice(): void
    {
        $client = $this->sophieClient('en_US');
        $page = $this->editForm($client, $this->resolve('/admin/channels/{channel}/edit'));
        $this->setValue($page['values'], 'sylius_admin_channel.name', 'Hacked channel');

        $client->request($page['method'], $page['uri'], $page['values']);

        $this->assertDemoRefusal($client, self::MESSAGE_EN);
        self::assertStringNotContainsString(self::MESSAGE_FR, (string) $client->getResponse()->getContent());
    }

    public function testAFormPostedFromAnAdminPageComesBackToItWithAFlashMessage(): void
    {
        $client = $this->sophieClient();
        $page = $this->editForm($client, $this->resolve('/admin/channels/{channel}/edit'));
        $client->setServerParameter('HTTP_REFERER', 'http://localhost/admin/channels/');

        $client->request($page['method'], $page['uri'], $page['values']);

        self::assertResponseRedirects('/admin/channels/');
        $crawler = $client->followRedirect();
        self::assertStringContainsString(self::MESSAGE_FR, $this->text($crawler));
        self::assertSelectorNotExists('[data-demo-admin-refused]', 'The flash is shown inside the page the visitor came from.');
    }

    public function testWithoutAnAdminPageToGoBackToTheAnswerIsAnExplicit403Page(): void
    {
        $client = $this->sophieClient('en_US');
        $page = $this->editForm($client, $this->resolve('/admin/channels/{channel}/edit'));
        $client->setServerParameter('HTTP_REFERER', 'https://attacker.example/admin/channels/');

        $client->request($page['method'], $page['uri'], $page['values']);

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorTextContains('[data-demo-admin-refused]', self::MESSAGE_EN);
        self::assertSelectorTextContains('[data-demo-admin-refused]', 'Action disabled in the demo');
    }

    public function testAnAjaxWriteIsRefusedWithAJson403(): void
    {
        $client = $this->sophieClient();
        $page = $this->editForm($client, $this->resolve('/admin/channels/{channel}/edit'));

        $client->xmlHttpRequest($page['method'], $page['uri'], $page['values']);

        self::assertResponseStatusCodeSame(403);
        $body = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($body);
        self::assertIsString($body['message'] ?? null);
        self::assertStringContainsString(self::MESSAGE_FR, $body['message']);
    }

    public function testSophieEditsAProductAndTheChangeIsSaved(): void
    {
        $client = $this->sophieClient();
        $productId = $this->intValue("SELECT translatable_id FROM sylius_product_translation WHERE slug = 'epi-cask-001-fr'");
        $original = $this->db()->fetchAssociative('SELECT name, description FROM sylius_product_translation WHERE translatable_id = ? AND locale = ?', [$productId, 'fr_FR']);

        try {
            $page = $this->editForm($client, '/admin/products/' . $productId . '/edit');
            $this->setValue($page['values'], 'sylius_admin_product.translations.fr_FR.name', 'Casque édité par Sophie');

            $client->request($page['method'], $page['uri'], $page['values']);

            self::assertResponseRedirects();
            self::assertSame('Casque édité par Sophie', $this->stringValue('SELECT name FROM sylius_product_translation WHERE translatable_id = ? AND locale = ?', [$productId, 'fr_FR']));
            $client->followRedirect();
            self::assertResponseIsSuccessful();
        } finally {
            $this->db()->executeStatement('UPDATE sylius_product_translation SET name = ?, description = ? WHERE translatable_id = ? AND locale = ?', [...array_values((array) $original), $productId, 'fr_FR']);
        }
    }

    public function testSophieEditsAnOrderAndTheChangeIsSaved(): void
    {
        $client = $this->sophieClient();
        $orderId = $this->intValue('SELECT id FROM sylius_order WHERE shipping_address_id IS NOT NULL ORDER BY id LIMIT 1');
        $addressId = $this->intValue('SELECT shipping_address_id FROM sylius_order WHERE id = ?', [$orderId]);
        $street = $this->stringValue('SELECT street FROM sylius_address WHERE id = ?', [$addressId]);

        try {
            $page = $this->editForm($client, '/admin/orders/' . $orderId . '/edit');
            $this->setValue($page['values'], 'sylius_admin_order.shippingAddress.street', '99 rue éditée par Sophie');

            $client->request($page['method'], $page['uri'], $page['values']);

            self::assertResponseRedirects();
            self::assertSame('99 rue éditée par Sophie', $this->stringValue('SELECT street FROM sylius_address WHERE id = ?', [$addressId]));
        } finally {
            $this->db()->executeStatement('UPDATE sylius_address SET street = ? WHERE id = ?', [$street, $addressId]);
        }
    }

    public function testTheRestOfTheAdminStaysUsableAfterARefusal(): void
    {
        $client = $this->sophieClient();
        $page = $this->editForm($client, $this->resolve('/admin/channels/{channel}/edit'));
        $client->request($page['method'], $page['uri'], $page['values']);
        $this->assertDemoRefusal($client, self::MESSAGE_FR);

        foreach (['/admin/', '/admin/products/', '/admin/orders/', '/admin/taxons/new'] as $url) {
            $client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
            self::assertSelectorNotExists('.alert-danger', $url . ' must not show a stray alert once the refusal was shown.');
        }
    }

    public function testThePublicAccountsDoNotCarryAnApiRoleSoNoAdminApiTokenIsUsable(): void
    {
        $roles = $this->sophie()->getRoles();

        self::assertNotContains('ROLE_API_ACCESS', $roles);
        self::assertContains('ROLE_DEMO_ADMIN', $roles);
    }

    /** Resolves {user}, {channel}… placeholders to the ids of the loaded fixtures. */
    private function resolve(string $url): string
    {
        $ids = [
            'user' => 'SELECT id FROM sylius_admin_user WHERE email_canonical = \'' . self::SOPHIE . '\'',
            'channel' => "SELECT id FROM sylius_channel WHERE code = 'B2B'",
            'payment_method' => "SELECT id FROM sylius_payment_method WHERE code = 'bank_transfer'",
            'buyer' => "SELECT id FROM tmonier_punchout_buyer WHERE code = 'acme-punchout'",
            'customer' => "SELECT id FROM sylius_customer WHERE email_canonical = 'claire@acme.example'",
        ];

        return preg_replace_callback('/\{(\w+)\}/', fn (array $m): string => (string) $this->intValue($ids[$m[1]]), $url) ?? $url;
    }
}
