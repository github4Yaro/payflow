<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819174240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoices (status VARCHAR(255) NOT NULL, order_id UUID NOT NULL, issued_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, number_series INT NOT NULL, number_sequence BIGINT NOT NULL, total_minor_units INT NOT NULL, total_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE orders (status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, id UUID NOT NULL, total_minor_units INT NOT NULL, total_currency VARCHAR(3) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE invoice_counters (year INT NOT NULL, last_number BIGINT DEFAULT 0 NOT NULL, PRIMARY KEY (year))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE invoice_counters');
    }
}
