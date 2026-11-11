<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260824111004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices ALTER issued_at SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_active_invoice_per_order ON invoices (order_id) WHERE status <> \'void\'');
        $this->addSql('CREATE INDEX idx_invoices_unpaid_cover ON invoices (issued_at DESC) INCLUDE (total_minor_units) WHERE status IN (\'issued\', \'overdue\')');
        $this->addSql('ALTER TABLE orders ALTER created_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_active_invoice_per_order');
        $this->addSql('DROP INDEX idx_invoices_unpaid_cover');
        $this->addSql('ALTER TABLE invoices ALTER issued_at DROP NOT NULL');
        $this->addSql('ALTER TABLE orders ALTER created_at DROP NOT NULL');
    }
}
