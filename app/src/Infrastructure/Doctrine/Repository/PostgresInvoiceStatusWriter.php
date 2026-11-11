<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceStatus;
use App\Domain\Invoice\InvoiceStatusWriter;
use App\Infrastructure\Doctrine\Type\InvoiceIdType;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final readonly class PostgresInvoiceStatusWriter implements InvoiceStatusWriter
{
    public function __construct(private Connection $connection)
    {
    }

    public function markPaid(InvoiceId $id): bool
    {
        $to = InvoiceStatus::Paid;

        $from = array_map(
            static fn (InvoiceStatus $status): string => $status->value,
            InvoiceStatus::allowedFrom($to),
        );

        return 1 === $this->connection->executeStatement(
            <<<'SQL'
            UPDATE invoices
               SET status = :to
             WHERE id = :id
               AND status IN (:from)
            SQL,
            [
                'to' => $to->value,
                'id' => $id,
                'from' => $from,
            ],
            [
                'id' => InvoiceIdType::NAME,
                'from' => ArrayParameterType::STRING,
            ],
        );
    }
}
