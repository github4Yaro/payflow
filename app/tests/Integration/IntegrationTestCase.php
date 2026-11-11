<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Shared\TruncateTablesTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    use TruncateTablesTrait;

    /** @var Connection[] */
    protected array $connections = [];

    /**
     * @throws Exception
     */
    protected function newConnection(): Connection
    {
        $conn = DriverManager::getConnection($this->em->getConnection()->getParams());
        $conn->executeStatement("SET lock_timeout = '250ms'");

        return $this->connections[] = $conn;
    }

    /**
     * @throws Exception
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->connections as $conn) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            $conn->close();
        }
        $this->connections = [];

        if (isset($this->em)) {
            $this->em->close();
        }

        parent::tearDown();
    }

    protected EntityManagerInterface $em;

    /**
     * @throws Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        $this->em = $em;
        $this->truncate();
    }
}
