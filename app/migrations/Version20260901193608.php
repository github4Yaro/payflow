<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901193608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webhook_events (provider VARCHAR(32) NOT NULL, event_id VARCHAR(255) NOT NULL, event_type VARCHAR(64) NOT NULL, payload TEXT NOT NULL, payload_hash VARCHAR(64) NOT NULL, status VARCHAR(16) NOT NULL, received_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (provider, event_id),CONSTRAINT webhook_events_status_check CHECK (status IN (\'pending\', \'processed\', \'failed\')))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE webhook_events');
    }
}
