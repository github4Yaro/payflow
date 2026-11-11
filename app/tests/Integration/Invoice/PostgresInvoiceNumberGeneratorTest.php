<?php

declare(strict_types=1);

namespace App\Tests\Integration\Invoice;

use App\Domain\Invoice\InvoiceSeries;
use App\Infrastructure\InvoiceNumber\PostgresInvoiceNumberGenerator;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DriverException;

final class PostgresInvoiceNumberGeneratorTest extends IntegrationTestCase
{
    /**
     * @throws Exception
     */
    public function testInvoiceNumberIsGenerated(): void
    {
        $generator = new PostgresInvoiceNumberGenerator($this->em->getConnection());
        $series = InvoiceSeries::forYear(2026);
        $invoiceNumber = $generator->next($series);
        $anotherInvoiceNumber = $generator->next($series);
        self::assertFalse($invoiceNumber->equals($anotherInvoiceNumber));
    }

    /**
     * @throws Exception
     */
    public function testGapLessGeneration(): void
    {
        $generator = new PostgresInvoiceNumberGenerator($this->em->getConnection());
        $series = InvoiceSeries::forYear(2026);
        $sequenceList = [];
        for ($i = 0; $i < 10; ++$i) {
            $invoiceNumber = $generator->next($series);
            $sequenceList[] = $invoiceNumber->sequence;
        }
        self::assertSame(range(1, 10), $sequenceList);
    }

    /**
     * @throws Exception
     */
    public function testCounterSerialisesConcurrentIssuers(): void
    {
        $a = $this->newConnection();
        $b = $this->newConnection();

        $generatorA = new PostgresInvoiceNumberGenerator($a);
        $generatorB = new PostgresInvoiceNumberGenerator($b);
        $series = InvoiceSeries::forYear(2026);

        $a->beginTransaction();
        $generatorA->next($series);

        $b->executeStatement("SET lock_timeout = '150ms'");
        $b->beginTransaction();

        try {
            $generatorB->next($series);
            self::fail('the second connection should not get a number');
        } catch (DriverException $e) {
            self::assertSame('55P03', $e->getSQLState());
        } finally {
            $b->rollBack();
            $a->rollBack();
        }
    }

    /**
     * @throws Exception
     */
    public function testRollbackIssuedNumber(): void
    {
        $a = $this->newConnection();
        $generator = new PostgresInvoiceNumberGenerator($a);
        $series = InvoiceSeries::forYear(2026);
        $a->beginTransaction();
        $generator->next($series);
        $a->commit();
        $a->beginTransaction();
        $firstNumber = $generator->next($series);
        $a->rollBack();
        $secondNumber = $generator->next($series);
        $this->assertTrue($firstNumber->equals($secondNumber));
    }
}
