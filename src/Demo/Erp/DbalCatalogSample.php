<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Reads the catalogue (read only), always in the same order: the files of the demo are the same after every reset. */
final readonly class DbalCatalogSample implements CatalogSample
{
    public function __construct(
        #[Autowire(service: 'doctrine.dbal.default_connection')]
        private Connection $connection,
    ) {
    }

    public function prices(int $limit): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT v.code AS variant_code, cp.channel_code AS channel_code, cur.code AS currency, cp.price AS price'
            . ' FROM sylius_channel_pricing cp'
            . ' JOIN sylius_product_variant v ON v.id = cp.product_variant_id'
            . ' JOIN sylius_channel ch ON ch.code = cp.channel_code'
            . ' JOIN sylius_currency cur ON cur.id = ch.base_currency_id'
            . ' WHERE cp.price > 0 AND v.enabled = 1 ORDER BY cp.channel_code, v.code LIMIT ' . max(0, $limit),
        );

        return array_map(static fn (array $row): SamplePrice => new SamplePrice(self::text($row['variant_code']), self::text($row['channel_code']), self::text($row['currency']), self::number($row['price'])), $rows);
    }

    public function stock(int $limit): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT code, on_hand FROM sylius_product_variant WHERE on_hand IS NOT NULL AND on_hand >= on_hold AND enabled = 1 ORDER BY code LIMIT ' . max(0, $limit));

        return array_map(static fn (array $row): SampleStock => new SampleStock(self::text($row['code']), self::number($row['on_hand'])), $rows);
    }

    private static function text(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    private static function number(mixed $value): int
    {
        return \is_numeric($value) ? (int) $value : 0;
    }
}
