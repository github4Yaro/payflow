<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Shared\TruncateTablesTrait;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    use TruncateTablesTrait;
    protected EntityManagerInterface $em;
    protected KernelBrowser $client;

    #[\Override]
    protected function tearDown(): void
    {
        if (isset($this->em)) {
            $this->em->close();
        }

        parent::tearDown();
    }

    /**
     * @throws Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);
        $this->em = $em;
        $this->truncate();
    }
}
