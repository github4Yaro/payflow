<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceNotFound;
use App\Domain\Invoice\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class DoctrineInvoiceRepository implements InvoiceRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @throws ORMException
     */
    public function get(InvoiceId $id): Invoice
    {
        return $this->em->find(Invoice::class, $id)
            ?? throw new InvoiceNotFound((string) $id);
    }

    public function save(Invoice $invoice): void
    {
        $this->em->persist($invoice);
    }
}
