<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Shared\BelowZeroException;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;

final class PspPayloadReader
{
    public static function read(string $payloadRaw): IncomingWebhook
    {
        try {
            $payloadBody = json_decode($payloadRaw, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidWebhookPayload('Payload is not valid JSON', previous: $e);
        }

        if (!is_array($payloadBody)) {
            throw new InvalidWebhookPayload('Payload is not a JSON object');
        }

        $payloadId = self::requireNonEmptyString($payloadBody, 'id');
        $payloadType = self::requireNonEmptyString($payloadBody, 'type');

        $invoiceRawId = self::requireNonEmptyString($payloadBody, 'data:invoice_id');
        try {
            $invoiceId = InvoiceId::fromString($invoiceRawId);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidWebhookPayload('Invalid invoice_id', previous: $e);
        }

        $currencyRaw = self::requireNonEmptyString($payloadBody, 'data:amount:currency');
        $currency = Currency::tryFrom(strtolower($currencyRaw));
        if (null === $currency) {
            throw new InvalidWebhookPayload('Invalid currency');
        }

        $valueRaw = self::requireNonEmptyInteger($payloadBody, 'data:amount:value');

        try {
            $amount = new Money($currency, $valueRaw);
        } catch (BelowZeroException $e) {
            throw new InvalidWebhookPayload('Invalid value', previous: $e);
        }

        return new IncomingWebhook(
            'psp',
            eventId: $payloadId,
            eventType: $payloadType,
            invoiceId: $invoiceId,
            amount: $amount,
            rawBody: $payloadRaw
        );
    }

    /**
     * @param array<mixed, mixed> $payload
     */
    private static function requireNonEmptyString(array $payload, string $field): string
    {
        $value = $payload;

        foreach (explode(':', $field) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                throw new InvalidWebhookPayload(sprintf('Invalid or missing "%s" field', $field));
            }

            $value = $value[$key];
        }

        if (!is_string($value) || '' === trim($value)) {
            throw new InvalidWebhookPayload(sprintf('Invalid or missing "%s" field', $field));
        }

        return $value;
    }

    /**
     * @param array<mixed, mixed> $payload
     */
    private static function requireNonEmptyInteger(array $payload, string $field): int
    {
        $value = $payload;

        foreach (explode(':', $field) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                throw new InvalidWebhookPayload(sprintf('Invalidr or missing "%s" field', $field));
            }

            $value = $value[$key];
        }

        if (!is_int($value)) {
            throw new InvalidWebhookPayload(sprintf('Invalid or missing "%s" field', $field));
        }

        return $value;
    }
}
