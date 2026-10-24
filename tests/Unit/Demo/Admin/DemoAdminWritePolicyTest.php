<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Admin;

use App\Demo\Admin\DemoAdminWritePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DemoAdminWritePolicyTest extends TestCase
{
    #[DataProvider('provideAllowedWrites')]
    public function testItAllowsTheNonSensitiveBusinessWrites(string $route, string $method, string $bulkActionCode = ''): void
    {
        self::assertTrue((new DemoAdminWritePolicy())->allows($route, $method, $bulkActionCode));
    }

    /** @return iterable<string, array{0: string, 1: string, 2?: string}> */
    public static function provideAllowedWrites(): iterable
    {
        yield 'product edit' => ['sylius_admin_product_update', 'PUT'];
        yield 'product creation' => ['sylius_admin_product_create', 'POST'];
        yield 'variant edit' => ['sylius_admin_product_variant_update', 'PUT'];
        yield 'taxon edit' => ['sylius_admin_taxon_update', 'PATCH'];
        yield 'order edit' => ['sylius_admin_order_update', 'PUT'];
        yield 'order cancellation' => ['sylius_admin_order_cancel', 'PUT'];
        yield 'payment completion' => ['sylius_admin_payment_complete', 'PUT'];
        yield 'shipment shipping' => ['sylius_admin_shipment_ship', 'PUT'];
        yield 'registration approval decision' => ['tmonier_registration_approval_admin_decide', 'POST'];
        yield 'relaunch of a send in error (US-184)' => ['app_demo_admin_einvoice_relaunch', 'POST'];
        yield 'order timeline note' => ['tmonier_timeline_admin_order_note_add', 'POST'];
        yield 'live component re-render' => ['sylius_admin_live_component', 'POST'];
        yield 'bulk action on orders' => ['tmonier_bulk_actions_admin_execute', 'POST', 'order_cancel'];
    }

    #[DataProvider('provideRefusedWrites')]
    public function testItRefusesTheSensitiveAndTheUnclassifiedWrites(string $route, string $method, string $bulkActionCode = ''): void
    {
        self::assertFalse((new DemoAdminWritePolicy())->allows($route, $method, $bulkActionCode));
    }

    /** @return iterable<string, array{0: string, 1: string, 2?: string}> */
    public static function provideRefusedWrites(): iterable
    {
        yield 'administrator edit' => ['sylius_admin_admin_user_update', 'PUT'];
        yield 'administrator creation' => ['sylius_admin_admin_user_create', 'POST'];
        yield 'channel edit' => ['sylius_admin_channel_update', 'PUT'];
        yield 'payment method edit' => ['sylius_admin_payment_method_update', 'PUT'];
        yield 'payment method creation' => ['sylius_admin_payment_method_create', 'POST'];
        yield 'webhook endpoint creation' => ['tmonier_webhook_admin_webhook_endpoint_create', 'POST'];
        yield 'webhook secret' => ['tmonier_webhook_admin_webhook_endpoint_regenerate_secret', 'POST'];
        yield 'punchout secret' => ['tmonier_punchout_admin_buyer_set_secret', 'POST'];
        yield 'customer edit (password field)' => ['sylius_admin_customer_update', 'PUT'];
        yield 'registration approval route that is not the decision' => ['tmonier_registration_approval_admin_settings_update', 'POST'];
        yield 'registration approval decision cannot delete' => ['tmonier_registration_approval_admin_decide', 'DELETE'];
        yield 'route that does not exist yet' => ['some_future_plugin_admin_settings_update', 'POST'];
        yield 'deletion of a product' => ['sylius_admin_product_delete', 'DELETE'];
        yield 'bulk deletion of products' => ['sylius_admin_product_bulk_delete', 'DELETE'];
        yield 'bulk action on customers' => ['tmonier_bulk_actions_admin_execute', 'POST', 'customer_disable_account'];
        yield 'bulk action without code' => ['tmonier_bulk_actions_admin_prepare', 'POST'];
        yield 'lookalike prefix is not a prefix match of a sensitive family' => ['sylius_admin_payment_method_bulk_delete', 'DELETE'];
    }
}
