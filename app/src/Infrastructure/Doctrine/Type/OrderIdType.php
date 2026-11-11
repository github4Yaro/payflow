<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use App\Domain\Order\OrderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class OrderIdType extends Type
{
    public const string NAME = 'order_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?OrderId {
        if (null === $value || $value instanceof OrderId) {
            return $value;
        }

        if (is_string($value)) {
            return OrderId::fromString($value);
        }

        throw new \InvalidArgumentException('Unsupported type conversion.');
    }

    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform,
    ): ?string {
        if (null === $value) {
            return null;
        }

        if ($value instanceof OrderId) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected %s or null, got %s', OrderId::class, get_debug_type($value)));
    }
}
