<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Schema;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class WebhooksSchemaListener
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        $this->addWebhookEventsTable($schema);
    }

    private function addWebhookEventsTable(Schema $schema): void
    {
        if ($schema->hasTable('webhook_events')) {
            return;
        }

        $table = $schema->createTable('webhook_events');

        $table->addColumn('provider', Types::STRING, ['length' => 32]);
        $table->addColumn('event_id', Types::STRING, ['length' => 255]);
        $table->addColumn('event_type', Types::STRING, ['length' => 64]);
        $table->addColumn('payload', Types::TEXT);
        $table->addColumn('payload_hash', Types::STRING, ['length' => 64]);
        $table->addColumn('status', Types::STRING, ['length' => 16]);
        $table->addColumn('received_at', Types::DATETIMETZ_IMMUTABLE);
        $table->addColumn('processed_at', Types::DATETIMETZ_IMMUTABLE, ['notnull' => false]);

        $table->setPrimaryKey(['provider', 'event_id']);
    }
}
