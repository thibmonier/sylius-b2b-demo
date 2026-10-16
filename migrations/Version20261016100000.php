<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * US-021 — Add po_reference and cost_center to sylius_order,
 * add po_reference_required and cost_center_enabled to sylius_channel.
 */
final class Version20261016100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-021: Add PO reference and cost center fields to orders and channel configuration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order ADD po_reference VARCHAR(100) DEFAULT NULL, ADD cost_center VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_channel ADD po_reference_required TINYINT(1) NOT NULL DEFAULT 0, ADD cost_center_enabled TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order DROP po_reference, DROP cost_center');
        $this->addSql('ALTER TABLE sylius_channel DROP po_reference_required, DROP cost_center_enabled');
    }
}
