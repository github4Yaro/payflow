<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use App\Domain\Invoice\InvoiceSeries;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class InvoiceSeriesType extends Type
{
    public const string NAME = 'invoice_series';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?InvoiceSeries {
        if (null === $value || $value instanceof InvoiceSeries) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return InvoiceSeries::forYear((int) $value);
        }

        throw new \InvalidArgumentException('Unsupported type conversion.');
    }

    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?int {
        if (null === $value) {
            return null;
        }

        if ($value instanceof InvoiceSeries) {
            return $value->year;
        }

        throw new \InvalidArgumentException(sprintf('Expected %s or null, got %s', InvoiceSeries::class, get_debug_type($value)));
    }
}
