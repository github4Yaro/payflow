<?php

declare(strict_types=1);

namespace App\Tests\Shared;

use Doctrine\DBAL\Exception;

trait TruncateTablesTrait
{
    /** @var array<string> * */
    protected array $tablesList = ['invoices', 'orders', 'invoice_counters', 'webhook_events'];

    /** @var array<string> * */
    protected array $skipList = ['doctrine_migration_versions'];

    /**
     * @throws Exception
     */
    protected function truncate(): void
    {
        $this->em->getConnection()->executeStatement(
            sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', implode(', ', $this->tablesList))
        );
    }
}
