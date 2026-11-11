<?php

declare(strict_types=1);

namespace App\Tests\Unit\Webhook;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Shared\Money;
use App\Infrastructure\Webhook\InvalidWebhookPayload;
use App\Infrastructure\Webhook\PspPayloadReader;
use PHPUnit\Framework\TestCase;

final class PspPayloadReaderTest extends TestCase
{
    public function testPspPayloadReadOk(): void
    {
        $payload = [
            'id' => 'evt_01J8Z3QK7M',
            'type' => 'payment.succeeded',
            'created_at' => '2026-09-02T11:42:07Z',
            'data' => [
                'payment_id' => 'pay_01J8Z3QK7N',
                'invoice_id' => '0198f4c2-1d3a-7c11-9b5e-3f2a8c4d6e10',
                'amount' => [
                    'value' => 149900,
                    'currency' => 'USD',
                ],
                'status' => 'succeeded',
            ],
        ];
        $payloadRaw = json_encode($payload, JSON_THROW_ON_ERROR);
        $incomingWebhook = PspPayloadReader::read($payloadRaw);

        $this->assertInstanceOf(InvoiceId::class, $incomingWebhook->invoiceId);
        $this->assertInstanceOf(Money::class, $incomingWebhook->amount);
    }

    public function testPspPayloadReadErrorEmpty(): void
    {
        $payload = [];
        $payloadRaw = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->expectException(InvalidWebhookPayload::class);
        PspPayloadReader::read($payloadRaw);
    }

    public function testPspPayloadReadErrorInvalidInvoiceId(): void
    {
        $payload = [
            'id' => 'evt_01J8Z3QK7M',
            'type' => 'payment.succeeded',
            'created_at' => '2026-09-02T11:42:07Z',
            'data' => [
                'payment_id' => 'pay_01J8Z3QK7N',
                'invoice_id' => 'not_a_uuid',
                'amount' => [
                    'value' => 149900,
                    'currency' => 'USD',
                ],
                'status' => 'succeeded',
            ],
        ];
        $payloadRaw = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->expectException(InvalidWebhookPayload::class);
        PspPayloadReader::read($payloadRaw);
    }

    public function testPspPayloadReadErrorInvalidAmount(): void
    {
        $payload = [
            'id' => 'evt_01J8Z3QK7M',
            'type' => 'payment.succeeded',
            'created_at' => '2026-09-02T11:42:07Z',
            'data' => [
                'payment_id' => 'pay_01J8Z3QK7N',
                'invoice_id' => '0198f4c2-1d3a-7c11-9b5e-3f2a8c4d6e10',
                'amount' => [
                    'value' => 149900,
                    'currency' => 'USDT',
                ],
                'status' => 'succeeded',
            ],
        ];
        $payloadRaw = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->expectException(InvalidWebhookPayload::class);
        PspPayloadReader::read($payloadRaw);
    }
}
