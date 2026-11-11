<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

final readonly class WebhookEventStore
{
    public function __construct(private Connection $connection)
    {
    }

    public function register(IncomingWebhook $webhook): DeliveryOutcome
    {
        $inserted = $this->connection->executeStatement(
            <<<'SQL'
            INSERT INTO webhook_events
                (provider, event_id, event_type, payload, payload_hash, status, received_at)
            VALUES (:provider, :eventId, :eventType, :payload, :hash, :status, :now)
            ON CONFLICT (provider, event_id) DO NOTHING
            SQL,
            [
                'provider' => $webhook->provider,
                'eventId' => $webhook->eventId,
                'eventType' => $webhook->eventType,
                'payload' => $webhook->rawBody,
                'hash' => $webhook->payloadHash(),
                'status' => WebhookEventStatus::Pending->value,
                'now' => new \DateTimeImmutable(),
            ],
            ['now' => Types::DATETIMETZ_IMMUTABLE],
        );

        if (1 === $inserted) {
            return DeliveryOutcome::Accepted;
        }

        $storedHash = $this->connection->fetchOne(
            'SELECT payload_hash FROM webhook_events WHERE provider = ? AND event_id = ?',
            [$webhook->provider, $webhook->eventId],
        );

        if (!is_string($storedHash)) {
            throw new \RuntimeException('Conflicting row disappeared');
        }

        return hash_equals($storedHash, $webhook->payloadHash())
            ? DeliveryOutcome::Duplicate
            : DeliveryOutcome::Conflict;
    }

    /**
     * @throws Exception
     */
    public function markProcessed(string $provider, string $eventId): bool
    {
        return 1 === $this->connection->executeStatement(
            "UPDATE webhook_events SET status = 'processed', processed_at = now()
             WHERE provider = ? AND event_id = ? AND status = 'pending'",
            [$provider, $eventId],
        );
    }
}
