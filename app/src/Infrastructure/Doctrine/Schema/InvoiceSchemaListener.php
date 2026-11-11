<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Schema;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class InvoiceSchemaListener
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        $this->addInvoiceCountersTable($schema);
        $this->addUnpaidCoverIndex($schema);
    }

    private function addInvoiceCountersTable(Schema $schema): void
    {
        if ($schema->hasTable('invoice_counters')) {
            return;
        }

        $table = $schema->createTable('invoice_counters');
        $table->addColumn('year', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('last_number', Types::BIGINT, ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['year']);
    }

    private function addUnpaidCoverIndex(Schema $schema): void
    {
        $schema->getTable('invoices')
            ->addIndex(
                ['issued_at', 'total_minor_units'],
                'idx_invoices_unpaid_cover',
                [],
                ['where' => "((status)::text = ANY ((ARRAY['issued'::character varying, 'overdue'::character varying])::text[]))"],
            );
    }
}
