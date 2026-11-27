<?php

declare(strict_types=1);

namespace App\Tests\Functional\Messenger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\Version;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as DoctrineTransportConnection;

/**
 * US-318: the production table was created before Version20261113100000, whose CREATE TABLE IF NOT EXISTS then did
 * nothing: three single-column indexes and the "(DC2Type:…)" comments of DBAL 3, five statements of drift on the health
 * page. The test environment hides the table from the schema comparison (doctrine.yaml), so the migration is checked
 * here against the table the Symfony Doctrine transport describes.
 */
final class MessengerTableMigrationTest extends KernelTestCase
{
    private const MIGRATION = 'DoctrineMigrations\Version20261127090000';

    /** The table as `SHOW CREATE TABLE` printed it in production on 2026-11-27. */
    private const PRODUCTION_TABLE = <<<'SQL'
        CREATE TABLE messenger_messages (
          id bigint NOT NULL AUTO_INCREMENT,
          body longtext COLLATE utf8mb4_unicode_ci NOT NULL,
          headers longtext COLLATE utf8mb4_unicode_ci NOT NULL,
          queue_name varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
          created_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
          available_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
          delivered_at datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
          PRIMARY KEY (id),
          KEY IDX_75EA56E0FB7336F0 (queue_name),
          KEY IDX_75EA56E0E3BD61CE (available_at),
          KEY IDX_75EA56E016BA31DB (delivered_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

    /** The table as Version20261113100000 creates it on an empty database. */
    private const MIGRATED_TABLE = 'CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, PRIMARY KEY(id), INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB';

    public function testTheProductionTableEndsAsTheTransportDescribesIt(): void
    {
        self::bootKernel();
        $this->replaceTable(self::PRODUCTION_TABLE);
        self::assertNotSame([], $this->alterations(), 'The production table differs from the transport table before the migration.');

        $this->migrate();

        self::assertSame([], $this->alterations());
    }

    public function testATableCreatedByTheEarlierMigrationIsLeftAsItIs(): void
    {
        self::bootKernel();
        $this->replaceTable(self::MIGRATED_TABLE);

        $statements = $this->migrate();

        self::assertSame([], array_values(array_filter($statements, static fn (string $sql): bool => str_contains($sql, 'INDEX'))));
        self::assertSame([], $this->alterations());
    }

    private function replaceTable(string $createTable): void
    {
        $this->connection()->executeStatement('DROP TABLE IF EXISTS messenger_messages');
        $this->connection()->executeStatement($createTable);
    }

    /** @return list<string> the statements the migration ran */
    private function migrate(): array
    {
        $factory = self::getContainer()->get('doctrine.migrations.dependency_factory');
        self::assertInstanceOf(DependencyFactory::class, $factory);
        $migration = $factory->getMigrationRepository()->getMigration(new Version(self::MIGRATION))->getMigration();

        $migration->up(new Schema());
        $statements = [];
        foreach ($migration->getSql() as $query) {
            $statements[] = $query->getStatement();
            $this->connection()->executeStatement($query->getStatement());
        }

        return $statements;
    }

    /** @return list<string> what would bring the table to the one the transport declares */
    private function alterations(): array
    {
        $connection = $this->connection();
        $current = $connection->createSchemaManager()->introspectTable('messenger_messages');
        $diff = $connection->createSchemaManager()->createComparator()->compareTables($current, $this->transportTable());

        return $diff->isEmpty() ? [] : $connection->getDatabasePlatform()->getAlterTableSQL($diff);
    }

    private function transportTable(): Table
    {
        $connection = $this->connection();
        $transport = new DoctrineTransportConnection(['table_name' => 'messenger_messages'], $connection);

        return $transport->configureSchema(new Schema(), $connection, static fn (): bool => true)->getTable('messenger_messages');
    }

    private function connection(): Connection
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }
}
