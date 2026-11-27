<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-318: align messenger_messages with the table of the Symfony Doctrine transport where it predates Version20261113100000.';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
        // Nothing to undo: the table is the one Version20261113100000 creates, and its own down() drops it.
    }
}
