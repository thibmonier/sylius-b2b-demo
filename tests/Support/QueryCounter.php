<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

/**
 * Counts the SQL statements sent to the database (test environment only). The counter is static: the kernel may
 * be rebooted between two requests of the same test, which would create a new service instance.
 */
final class QueryCounter implements Middleware
{
    private static int $count = 0;

    /** @var list<string> */
    private static array $statements = [];

    public static function reset(): void
    {
        self::$count = 0;
        self::$statements = [];
    }

    /** @param list<string> $ignoredTables tables whose statements are not counted (see the callers) */
    public static function count(array $ignoredTables = []): int
    {
        $ignored = static fn (string $sql): bool => [] !== array_filter($ignoredTables, static fn (string $table): bool => str_contains($sql, 'FROM ' . $table . ' '));

        return \count(array_filter(self::$statements, static fn (string $sql): bool => !$ignored($sql)));
    }

    /** @return list<string> */
    public static function statements(): array
    {
        return self::$statements;
    }

    public function wrap(Driver $driver): Driver
    {
        return new class($driver) extends AbstractDriverMiddleware {
            public function connect(array $params): DriverConnection
            {
                return new class(parent::connect($params)) extends AbstractConnectionMiddleware {
                    public function prepare(string $sql): Statement
                    {
                        return new class(parent::prepare($sql), $sql) extends AbstractStatementMiddleware {
                            public function __construct(Statement $statement, private readonly string $sql)
                            {
                                parent::__construct($statement);
                            }

                            public function execute(): Result
                            {
                                QueryCounter::hit($this->sql);

                                return parent::execute();
                            }
                        };
                    }

                    public function query(string $sql): Result
                    {
                        QueryCounter::hit($sql);

                        return parent::query($sql);
                    }

                    public function exec(string $sql): int|string
                    {
                        QueryCounter::hit($sql);

                        return parent::exec($sql);
                    }
                };
            }
        };
    }

    public static function hit(string $sql): void
    {
        ++self::$count;
        self::$statements[] = $sql;
    }
}
