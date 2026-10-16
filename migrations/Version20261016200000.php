<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * US-024 — Company purchase lists: links an official wishlist (sylius_wishlist) to a company.
 */
final class Version20261016200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US-024: Add app_company_wishlist (company, wishlist, creator).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_company_wishlist (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, wishlist_id INT NOT NULL, company_id INT NOT NULL, created_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1048DEFDFB8E54CD (wishlist_id), INDEX IDX_1048DEFD979B1AD6 (company_id), INDEX IDX_1048DEFDB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE app_company_wishlist ADD CONSTRAINT FK_1048DEFD979B1AD6 FOREIGN KEY (company_id) REFERENCES tmonier_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_company_wishlist ADD CONSTRAINT FK_1048DEFDFB8E54CD FOREIGN KEY (wishlist_id) REFERENCES sylius_wishlist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_company_wishlist ADD CONSTRAINT FK_1048DEFDB03A8386 FOREIGN KEY (created_by_id) REFERENCES sylius_customer (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_company_wishlist');
    }
}
