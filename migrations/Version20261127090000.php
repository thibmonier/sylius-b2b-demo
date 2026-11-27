<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127090000 extends AbstractMigration
{
    private const LEGACY_INDEXES = ['IDX_75EA56E0FB7336F0', 'IDX_75EA56E0E3BD61CE', 'IDX_75EA56E016BA31DB'];

    private const TRANSPORT_INDEX = 'IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750';

    public function getDescription(): string
    {
        return 'US-318: align messenger_messages with the table of the Symfony Doctrine transport where it predates Version20261113100000.';
    }

    public function up(Schema $schema): void
    {
        // The table of production was created before Version20261113100000, whose CREATE TABLE IF NOT EXISTS did nothing:
        // one index per column and the comments of DBAL 3. The indexes are read from the database itself, not from $schema,
        // which the schema_filter of the test environment leaves without this table. A database built by the migrations is not touched.
        $indexes = array_change_key_case($this->sm->listTableIndexes('messenger_messages'), \CASE_UPPER);
        foreach (self::LEGACY_INDEXES as $index) {
            if (isset($indexes[$index])) {
                $this->addSql(sprintf('DROP INDEX %s ON messenger_messages', $index));
            }
        }

        if (!isset($indexes[self::TRANSPORT_INDEX])) {
            // Same type, same nullability, no row touched: only the "(DC2Type:datetime_immutable)" comments go away.
            $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
            $this->addSql(sprintf('CREATE INDEX %s ON messenger_messages (queue_name, available_at, delivered_at, id)', self::TRANSPORT_INDEX));
        }
    }

    public function down(Schema $schema): void
    {
        // Nothing to undo: the table is the one Version20261113100000 creates, and its own down() drops it.
    }
}
