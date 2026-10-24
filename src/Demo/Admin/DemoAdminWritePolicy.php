<?php

declare(strict_types=1);

namespace App\Demo\Admin;

/**
 * Which admin writes the restricted demo account may perform (US-035, matrix in docs/securite-admin-demo.md).
 *
 * Sylius CE has no fine-grained rights, so the policy is an allow-list of business resources (catalogue,
 * orders and their workflow, notes): every other write route is refused by default, which also covers the
 * routes added by a future plugin. Deletions are refused everywhere.
 */
final class DemoAdminWritePolicy
{
    /** @var list<string> */
    private const ALLOWED_PREFIXES = [
        'sylius_admin_product_',
        'sylius_admin_ajax_product_',
        'sylius_admin_taxon_',
        'sylius_admin_order_',
        'sylius_refund_',
        'tmonier_admin_order_',
        'tmonier_timeline_',
        'tmonier_ai_copilot_admin_',
        'tmonier_quote_request_admin_',
        'tmonier_withdrawal_admin_',
        'tmonier_purchase_approval_admin_',
    ];

    /** @var list<string> */
    private const ALLOWED_ROUTES = [
        'sylius_admin_payment_complete',
        'sylius_admin_shipment_ship',
        'sylius_admin_dashboard',
        'sylius_admin_entity_autocomplete',
        'sylius_admin_live_component',
        'sylius_admin_impersonate_user',
        // US-020: approve or reject a pending registration (POST, CSRF token and administration role checked by the plugin).
        'tmonier_registration_approval_admin_decide',
        'tmonier_bulk_actions_admin_ship_with_tracking_codes',
        // US-184: relaunch of an e-invoice send in error (POST, CSRF token checked, only the status "error" is relaunched).
        'app_demo_admin_einvoice_relaunch',
    ];

    /** @var list<string> */
    private const BULK_ACTION_ROUTES = [
        'tmonier_bulk_actions_admin_prepare',
        'tmonier_bulk_actions_admin_execute',
    ];

    /** @var list<string> bulk actions on orders, payments and shipments only (not customers or channels) */
    private const BULK_ACTION_PREFIXES = ['order_', 'payment_', 'shipment_'];

    /**
     * @param string $method         the unsafe method of the request (POST, PUT, PATCH or DELETE)
     * @param string $bulkActionCode the `code` of a bulk-action route, empty otherwise
     */
    public function allows(string $route, string $method, string $bulkActionCode = ''): bool
    {
        if ('DELETE' === $method) {
            return false;
        }

        if (\in_array($route, self::BULK_ACTION_ROUTES, true)) {
            return $this->isAllowedBulkAction($bulkActionCode);
        }

        return \in_array($route, self::ALLOWED_ROUTES, true) || $this->hasAllowedPrefix($route);
    }

    private function hasAllowedPrefix(string $route): bool
    {
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isAllowedBulkAction(string $code): bool
    {
        foreach (self::BULK_ACTION_PREFIXES as $prefix) {
            if (str_starts_with($code, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
