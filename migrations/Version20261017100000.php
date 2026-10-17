<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-029: Add app_received_webhook (webhooks accepted by the demo receiver).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_received_webhook (id BINARY(16) NOT NULL, received_at DATETIME NOT NULL, payload LONGTEXT NOT NULL, payload_truncated TINYINT NOT NULL, webhook_id VARCHAR(255) NOT NULL, event_type VARCHAR(100) NOT NULL, signature_verified TINYINT NOT NULL, UNIQUE INDEX UNIQ_BCA9FCAE5C9BA60B (webhook_id), INDEX idx_app_received_webhook_received_at (received_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_received_webhook');
    }
}
