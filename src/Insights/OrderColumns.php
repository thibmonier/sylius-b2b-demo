<?php

declare(strict_types=1);

namespace App\Insights;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * Reads one column of `sylius_order` for a list of order ids, for the integrations of the purchasing dashboard (US-152).
 * The column name is chosen by the caller among constants, never from a request.
 */
final readonly class OrderColumns
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param 'cost_center'|'po_reference' $column
     * @param list<int>                    $orderIds
     *
     * @return array<int, string|null> order id => value
     */
    public function read(string $column, array $orderIds): array
    {
        if ([] === $orderIds) {
            return [];
        }

        $values = [];
        foreach ($this->connection->fetchAllAssociative(
            sprintf('SELECT id, %s AS value FROM sylius_order WHERE id IN (:ids)', $column),
            ['ids' => $orderIds],
            ['ids' => ArrayParameterType::INTEGER],
        ) as $row) {
            $values[self::id($row['id'])] = \is_string($row['value']) ? $row['value'] : null;
        }

        return $values;
    }

    private static function id(mixed $value): int
    {
        Assert::numeric($value);

        return (int) $value;
    }
}
