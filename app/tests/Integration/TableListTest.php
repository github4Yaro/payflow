<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\DBAL\Exception;

final class TableListTest extends IntegrationTestCase
{
    /**
     * @throws Exception
     */
    public function testTableList(): void
    {
        $tablesList = array_merge($this->tablesList, $this->skipList);

        $DBTables = $this->em->getConnection()->fetchFirstColumn('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'');
        $this->assertArraysHaveIdenticalValuesIgnoringOrder($tablesList, $DBTables);
    }
}
