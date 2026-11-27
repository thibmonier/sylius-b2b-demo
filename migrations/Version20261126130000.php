<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-318: drop the "(DC2Type:…)" comments that the migrations of Sylius 2.3.3 and of the refund plugin 2.2.1 still write, and that DBAL 4 reads as schema drift.';
    }

    public function up(Schema $schema): void
    {
        // Same type, same nullability: only the column comment of DBAL 3 goes away, no row is touched.
        // The drift is upstream: a test application of Sylius alone shows it on sylius_payment_request and sylius_payment_security_token.
        $this->addSql('ALTER TABLE sylius_refund_refund CHANGE type type VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE sylius_refund_credit_memo CHANGE issued_at issued_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sylius_payment_request CHANGE hash hash BINARY(16) NOT NULL, CHANGE payload payload LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE sylius_payment_security_token CHANGE details details LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sylius_refund_refund CHANGE type type VARCHAR(256) NOT NULL COMMENT '(DC2Type:sylius_refund_refund_type)'");
        $this->addSql("ALTER TABLE sylius_refund_credit_memo CHANGE issued_at issued_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE sylius_payment_request CHANGE hash hash BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', CHANGE payload payload LONGTEXT NOT NULL COMMENT '(DC2Type:object)'");
        $this->addSql("ALTER TABLE sylius_payment_security_token CHANGE details details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)'");
    }
}
