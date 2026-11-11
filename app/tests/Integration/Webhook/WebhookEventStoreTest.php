<?php

declare(strict_types=1);

namespace App\Tests\Integration\Webhook;

use App\Infrastructure\Webhook\DeliveryOutcome;
use App\Infrastructure\Webhook\PspPayloadReader;
use App\Infrastructure\Webhook\WebhookEventStore;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\LoadFixtureTrait;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DriverException;

final class WebhookEventStoreTest extends IntegrationTestCase
{
    use LoadFixtureTrait;

    public function testRegisterEventOnlyOnce(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $webhook = PspPayloadReader::read($payloadRaw);
        $store = new WebhookEventStore($this->em->getConnection());

        $res = $store->register($webhook);
        $this->assertSame(DeliveryOutcome::Accepted, $res);

        $res = $store->register($webhook);
        $this->assertSame(DeliveryOutcome::Duplicate, $res);
    }

    public function testRegisterEventConflict(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $webhook = PspPayloadReader::read($payloadRaw);
        $store = new WebhookEventStore($this->em->getConnection());

        $res = $store->register($webhook);
        $this->assertSame(DeliveryOutcome::Accepted, $res);

        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_2.json');
        $webhook = PspPayloadReader::read($payloadRaw);

        $res = $store->register($webhook);
        $this->assertSame(DeliveryOutcome::Conflict, $res);
    }

    /**
     * @throws Exception
     */
    public function testMarkProcessedOnlyOnce(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $webhook = PspPayloadReader::read($payloadRaw);
        $store = new WebhookEventStore($this->em->getConnection());

        $res = $store->register($webhook);
        $this->assertSame(DeliveryOutcome::Accepted, $res);

        self::assertTrue($store->markProcessed('psp', $webhook->eventId));
        $first = $this->fetchProcessedAt('psp', $webhook->eventId);

        self::assertFalse($store->markProcessed('psp', $webhook->eventId));
        $second = $this->fetchProcessedAt('psp', $webhook->eventId);

        self::assertSame($first, $second);
    }

    /**
     * @throws Exception
     */
    public function testMarkProcessedBlocksConcurrentWriter(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $webhook = PspPayloadReader::read($payloadRaw);
        new WebhookEventStore($this->em->getConnection())->register($webhook);

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        self::assertTrue(new WebhookEventStore($a)->markProcessed('psp', $webhook->eventId));

        try {
            new WebhookEventStore($b)->markProcessed('psp', $webhook->eventId);
            self::fail('exception should have been thrown');
        } catch (DriverException $e) {
            self::assertSame('55P03', $e->getSQLState());
        }

        $a->commit();

        self::assertFalse(new WebhookEventStore($b)->markProcessed('psp', $webhook->eventId));
    }

    /**
     * @throws Exception
     */
    private function fetchProcessedAt(string $provider, string $eventId): string
    {
        $processedAt = $this->em->getConnection()->fetchOne(
            'Select processed_at from webhook_events where provider = ? and event_id = ?', [$provider, $eventId],
        );

        $this->assertIsString($processedAt);

        return $processedAt;
    }
}
