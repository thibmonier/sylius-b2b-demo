<?php

declare(strict_types=1);

namespace App\Demo\Order;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * Writes the purchase history of US-155 straight to the tables, in a few multi-row INSERTs.
 *
 * About sixty more orders through the Sylius services (state machines, order processors, plugins reacting to each
 * transition) took three times the whole reset. This history only has to look like years of past orders, so each
 * order is written already in its final state, with the same rows the services leave (order, lines, units, shipping
 * adjustment, payment, shipment, addresses, link to the company) but without invoice, timeline entry or webhook event.
 * The orders of the scenarios (approval, overdue open item, cancellation) keep going through the services.
 */
final class BulkOrderWriter
{
    private const CHUNK_ROWS = 400;

    private const LOCALE = 'fr_FR';

    private const SHIPPING_COST = 1500;

    private const SHIPPING_LABEL = 'Messagerie (48 h)';

    private const SHIPMENT_DAYS = 3;

    /** @var array<string, int> */
    private array $nextIds = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    /** @param list<DemoOrderSpec> $specs */
    public function write(array $specs, \DateTimeImmutable $now, string $channelCode): int
    {
        $specs = $this->withoutExisting($specs);
        if ([] === $specs) {
            return 0;
        }

        $lookups = $this->lookups($specs, $channelCode);
        $number = $this->orderSequence();
        $rows = new BulkOrderRows();
        $this->nextIds = [];
        foreach ($specs as $spec) {
            $this->addOrder($rows, $spec, DemoOrderPlan::completedAt($now, $spec->daysAgo), ++$number, $lookups);
        }

        $this->connection->beginTransaction();

        try {
            foreach ($rows->tables() as $table => [$columns, $tableRows]) {
                $this->insert($table, $columns, $tableRows);
            }
            $this->connection->executeStatement('UPDATE sylius_order_sequence SET idx = :idx, version = version + 1', ['idx' => $number]);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }

        return \count($specs);
    }

    /**
     * @param list<DemoOrderSpec> $specs
     *
     * @return list<DemoOrderSpec>
     */
    private function withoutExisting(array $specs): array
    {
        $existing = $this->connection->fetchFirstColumn('SELECT po_reference FROM sylius_order WHERE po_reference IS NOT NULL');

        return array_values(array_filter($specs, static fn (DemoOrderSpec $spec): bool => !\in_array($spec->poReference, $existing, true)));
    }

    private function orderSequence(): int
    {
        $index = $this->connection->fetchOne('SELECT idx FROM sylius_order_sequence ORDER BY id LIMIT 1');
        if (false === $index) {
            $this->connection->executeStatement('INSERT INTO sylius_order_sequence (idx, version) VALUES (0, 1)');

            return 0;
        }

        return self::int($index);
    }

    /**
     * @param list<DemoOrderSpec> $specs
     *
     * @return array{channel: int, customers: array<string, array<string, mixed>>, variants: array<string, array<string, mixed>>, payment: int, shipping: int}
     */
    private function lookups(array $specs, string $channelCode): array
    {
        $emails = array_values(array_unique(array_map(static fn (DemoOrderSpec $spec): string => $spec->customerEmail, $specs)));
        $codes = array_values(array_unique(array_merge(...array_map(static fn (DemoOrderSpec $spec): array => array_map('strval', array_keys($spec->items)), $specs))));

        $customers = $this->connection->fetchAllAssociativeIndexed(
            'SELECT c.email_canonical, c.id, c.first_name, c.last_name, co.id AS company_id, co.name AS company_name
             FROM sylius_customer c
             LEFT JOIN tmonier_company_member m ON m.customer_id = c.id
             LEFT JOIN tmonier_company co ON co.id = m.company_id
             WHERE c.email_canonical IN (?)',
            [$emails],
            [ArrayParameterType::STRING],
        );
        $variants = $this->connection->fetchAllAssociativeIndexed(
            'SELECT v.code, v.id, pt.name AS product_name, vt.name AS variant_name, cp.price
             FROM sylius_product_variant v
             JOIN sylius_product_translation pt ON pt.translatable_id = v.product_id AND pt.locale = :locale
             JOIN sylius_product_variant_translation vt ON vt.translatable_id = v.id AND vt.locale = :locale
             JOIN sylius_channel_pricing cp ON cp.product_variant_id = v.id AND cp.channel_code = :channel
             WHERE v.code IN (:codes)',
            ['locale' => self::LOCALE, 'channel' => $channelCode, 'codes' => $codes],
            ['codes' => ArrayParameterType::STRING],
        );

        return [
            'channel' => $this->idOf('sylius_channel', $channelCode),
            'customers' => $customers,
            'variants' => $variants,
            'payment' => $this->idOf('sylius_payment_method', 'bank_transfer'),
            'shipping' => $this->idOf('sylius_shipping_method', 'carrier'),
        ];
    }

    private function idOf(string $table, string $code): int
    {
        $id = $this->connection->fetchOne(sprintf('SELECT id FROM %s WHERE code = ?', $table), [$code]);
        if (false === $id) {
            throw new \InvalidArgumentException(sprintf('Demo order history: "%s" does not exist in %s.', $code, $table));
        }

        return self::int($id);
    }

    /** @param array{channel: int, customers: array<string, array<string, mixed>>, variants: array<string, array<string, mixed>>, payment: int, shipping: int} $lookups */
    private function addOrder(BulkOrderRows $rows, DemoOrderSpec $spec, \DateTimeImmutable $date, int $number, array $lookups): void
    {
        $customer = $lookups['customers'][$spec->customerEmail] ?? throw new \LogicException(sprintf('Demo order history: customer "%s" does not exist.', $spec->customerEmail));
        $shipped = OrderScenario::Shipped === $spec->scenario;
        $shippedAt = $date->modify(sprintf('+%d days', self::SHIPMENT_DAYS));
        $orderId = $this->nextId('sylius_order');
        $shipmentId = $this->nextId('sylius_shipment');

        $itemsTotal = 0;
        foreach ($spec->items as $code => $quantity) {
            $variant = $lookups['variants'][(string) $code] ?? throw new \InvalidArgumentException(sprintf('Demo order: variant "%s" does not exist.', $code));
            $itemId = $this->nextId('sylius_order_item');
            $price = self::int($variant['price']);
            $itemsTotal += $price * $quantity;
            $rows->add('sylius_order_item', [$itemId, $orderId, self::int($variant['id']), $quantity, $price, $price, $price * $quantity, 0, $price * $quantity, 0, $variant['product_name'], $variant['variant_name'], 1]);
            for ($unit = 0; $unit < $quantity; ++$unit) {
                $rows->add('sylius_order_item_unit', [$this->nextId('sylius_order_item_unit'), $itemId, $shipmentId, 0, self::at($date), self::at($shipped ? $shippedAt : $date)]);
            }
        }

        $total = $itemsTotal + self::SHIPPING_COST;
        $shippingAddress = $this->nextId('sylius_address');
        $billingAddress = $this->nextId('sylius_address');
        foreach ([$shippingAddress, $billingAddress] as $addressId) {
            $rows->add('sylius_address', $this->address($addressId, $customer, $spec, $date));
        }

        $updated = self::at($shipped ? $shippedAt : $date);
        $rows->add('sylius_order', [
            $orderId, $shippingAddress, $billingAddress, $lookups['channel'], self::int($customer['id']), sprintf('%09d', $number), null,
            $shipped ? 'fulfilled' : 'new', self::at($date), $itemsTotal, self::SHIPPING_COST, $total, self::at($date), $updated,
            'EUR', self::LOCALE, 'completed', 'paid', $shipped ? 'shipped' : 'ready', 0, hash('sha256', 'demo-history-' . $spec->poReference), null,
            $spec->poReference, $spec->costCenter,
        ]);
        $rows->add('sylius_adjustment', [$this->nextId('sylius_adjustment'), $orderId, null, null, $shipmentId, 'shipping', self::SHIPPING_LABEL, self::SHIPPING_COST, 0, 0, null, '{"shippingMethodCode": "carrier", "shippingMethodName": "' . self::SHIPPING_LABEL . '"}', self::at($date), $updated]);
        $rows->add('sylius_payment', [$this->nextId('sylius_payment'), $lookups['payment'], $orderId, 'EUR', $total, 'completed', '[]', self::at($date), self::at($date)]);
        $rows->add('sylius_shipment', [$shipmentId, $lookups['shipping'], $orderId, $shipped ? 'shipped' : 'ready', null, self::at($date), $updated, $shipped ? self::at($shippedAt) : null, self::SHIPPING_COST]);
        if (null !== $customer['company_id']) {
            $rows->add('tmonier_company_order', [$this->nextId('tmonier_company_order'), self::int($customer['company_id']), $orderId, self::at($date)]);
        }
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return list<mixed>
     */
    private function address(int $id, array $customer, DemoOrderSpec $spec, \DateTimeImmutable $date): array
    {
        $saintPriest = DemoOrderSpec::SITE_SAINT_PRIEST === $spec->deliverySite;

        return [
            $id, $customer['first_name'], $customer['last_name'], $customer['company_name'],
            $saintPriest ? '45 boulevard des Expositions' : '12 rue de l\'Industrie', $saintPriest ? 'Saint-Priest' : 'Lyon', $saintPriest ? '69800' : '69003',
            self::at($date), self::at($date), 'FR',
        ];
    }

    private function nextId(string $table): int
    {
        $this->nextIds[$table] ??= self::int($this->connection->fetchOne(sprintf('SELECT COALESCE(MAX(id), 0) FROM %s', $table)));

        return ++$this->nextIds[$table];
    }

    /**
     * @param list<string>      $columns
     * @param list<list<mixed>> $rows
     */
    private function insert(string $table, array $columns, array $rows): void
    {
        foreach (array_chunk($rows, self::CHUNK_ROWS) as $chunk) {
            $placeholders = implode(', ', array_fill(0, \count($chunk), '(' . implode(', ', array_fill(0, \count($columns), '?')) . ')'));
            $this->connection->executeStatement(
                sprintf('INSERT INTO %s (%s) VALUES %s', $table, implode(', ', $columns), $placeholders),
                array_merge(...$chunk),
            );
        }
    }

    private static function int(mixed $value): int
    {
        Assert::numeric($value);

        return (int) $value;
    }

    private static function at(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
