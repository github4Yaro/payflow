<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Webhooks;

use App\Application\Webhook\MarkInvoicePaid;
use App\Infrastructure\Webhook\DeliveryOutcome;
use App\Infrastructure\Webhook\InvalidWebhookPayload;
use App\Infrastructure\Webhook\PspPayloadReader;
use App\Infrastructure\Webhook\WebhookEventStore;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhooks/psp', name: 'webhook_psp', methods: ['POST'])]
final readonly class PspWebhookController
{
    public function __construct(
        private WebhookEventStore $store,
        private MarkInvoicePaid $paidMarker,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request): Response
    {
        try {
            $webhook = PspPayloadReader::read($request->getContent());
        } catch (InvalidWebhookPayload) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
        $outcome = $this->store->register($webhook);

        if (DeliveryOutcome::Accepted === $outcome) {
            ($this->paidMarker)($webhook->invoiceId);

            $this->store->markProcessed($webhook->provider, $webhook->eventId);
        }

        return new Response('', $outcome->statusCode());
    }
}
