<?php

declare(strict_types=1);

namespace App\Infrastructure\InvoiceNumber;

use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceNumberGenerator;
use App\Domain\Invoice\InvoiceSeries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PostgresInvoiceNumberGenerator implements InvoiceNumberGenerator
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function next(
        InvoiceSeries $series,
    ): InvoiceNumber {
        $sequence = $this->connection
            ->executeQuery(
                <<<'SQL'
                INSERT INTO invoice_counters (
                    year,
                    last_number
                )
                VALUES (:year, 1)

                ON CONFLICT (year)
                DO UPDATE
                SET last_number =
                    invoice_counters.last_number + 1

                RETURNING last_number
                SQL,
                [
                    'year' => $series->year,
                ],
            )
            ->fetchOne();

        if (!is_int($sequence)) {
            throw new \RuntimeException('Counter returned no sequence');
        }

        return new InvoiceNumber(
            $series,
            $sequence,
        );
    }
}
