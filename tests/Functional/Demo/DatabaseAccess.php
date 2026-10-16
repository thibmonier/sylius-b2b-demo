<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\DBAL\Connection;

/**
 * Typed raw SQL access for the functional tests of the demo (the database is the judge of what a request changed).
 */
trait DatabaseAccess
{
    protected function db(): Connection
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }

    /**
     * @param list<int|string> $params
     */
    protected function intValue(string $sql, array $params = []): int
    {
        $value = $this->db()->fetchOne($sql, $params);
        if (!is_numeric($value)) {
            self::fail(\sprintf('"%s" must return a number, got %s.', $sql, get_debug_type($value)));
        }

        return (int) $value;
    }

    /**
     * @param list<int|string> $params
     */
    protected function stringValue(string $sql, array $params = []): string
    {
        $value = $this->db()->fetchOne($sql, $params);
        if (!\is_string($value)) {
            self::fail(\sprintf('"%s" must return a string, got %s.', $sql, get_debug_type($value)));
        }

        return $value;
    }

    /**
     * @param list<int|string> $params
     */
    protected function nullableStringValue(string $sql, array $params = []): ?string
    {
        $value = $this->db()->fetchOne($sql, $params);
        if (null !== $value && !\is_string($value)) {
            self::fail(\sprintf('"%s" must return a string or null, got %s.', $sql, get_debug_type($value)));
        }

        return $value;
    }
}
