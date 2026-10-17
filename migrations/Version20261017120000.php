<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-048: add app_demo_load_marker (end-of-load marker of the demo fixtures, read by the entrypoint).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_demo_load_marker (id SMALLINT NOT NULL, loaded_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        // A database that already holds data was loaded before the marker existed: it is taken as loaded, so that the
        // next start does not purge a working demo. An empty database gets its marker from the first load.
        $this->addSql('INSERT INTO app_demo_load_marker (id, loaded_at) SELECT 1, NOW() FROM DUAL WHERE EXISTS (SELECT 1 FROM sylius_channel)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_demo_load_marker');
    }
}
