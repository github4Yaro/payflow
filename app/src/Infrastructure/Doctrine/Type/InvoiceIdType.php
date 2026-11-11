<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use App\Domain\Invoice\InvoiceId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class InvoiceIdType extends Type
{
    public const string NAME = 'invoice_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?InvoiceId {
        if (null === $value || $value instanceof InvoiceId) {
            return $value;
        }

        if (is_string($value)) {
            return InvoiceId::fromString($value);
        }

        throw new \InvalidArgumentException('Unsupported type conversion.');
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof InvoiceId) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected %s or null, got %s', InvoiceId::class, get_debug_type($value)));
    }
}
