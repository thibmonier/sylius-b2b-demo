<?php

declare(strict_types=1);

namespace App\Demo\Order;

/** The rows {@see BulkOrderWriter} collects, by table, in the order the foreign keys need. */
final class BulkOrderRows
{
    private const COLUMNS = [
        'sylius_address' => ['id', 'first_name', 'last_name', 'company', 'street', 'city', 'postcode', 'created_at', 'updated_at', 'country_code'],
        'sylius_order' => [
            'id', 'shipping_address_id', 'billing_address_id', 'channel_id', 'customer_id', 'number', 'notes', 'state', 'checkout_completed_at',
            'items_total', 'adjustments_total', 'total', 'created_at', 'updated_at', 'currency_code', 'locale_code', 'checkout_state',
            'payment_state', 'shipping_state', 'created_by_guest', 'token_value', 'customer_ip', 'po_reference', 'cost_center',
        ],
        'sylius_order_item' => ['id', 'order_id', 'variant_id', 'quantity', 'unit_price', 'original_unit_price', 'units_total', 'adjustments_total', 'total', 'is_immutable', 'product_name', 'variant_name', 'version'],
        'sylius_payment' => ['id', 'method_id', 'order_id', 'currency_code', 'amount', 'state', 'details', 'created_at', 'updated_at'],
        'sylius_shipment' => ['id', 'method_id', 'order_id', 'state', 'tracking', 'created_at', 'updated_at', 'shipped_at', 'adjustments_total'],
        'sylius_order_item_unit' => ['id', 'order_item_id', 'shipment_id', 'adjustments_total', 'created_at', 'updated_at'],
        'sylius_adjustment' => ['id', 'order_id', 'order_item_id', 'order_item_unit_id', 'shipment_id', 'type', 'label', 'amount', 'is_neutral', 'is_locked', 'origin_code', 'details', 'created_at', 'updated_at'],
        'tmonier_company_order' => ['id', 'company_id', 'order_id', 'created_at'],
    ];

    /** @var array<string, list<list<mixed>>> */
    private array $rows = [];

    /** @param list<mixed> $row */
    public function add(string $table, array $row): void
    {
        $this->rows[$table][] = $row;
    }

    /** @return \Generator<string, array{list<string>, list<list<mixed>>}> */
    public function tables(): \Generator
    {
        foreach (self::COLUMNS as $table => $columns) {
            if (isset($this->rows[$table])) {
                yield $table => [$columns, $this->rows[$table]];
            }
        }
    }
}
