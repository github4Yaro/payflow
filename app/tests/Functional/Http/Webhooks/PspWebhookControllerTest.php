<?php

declare(strict_types=1);

namespace App\Tests\Functional\Http\Webhooks;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceStatus;
use App\Infrastructure\Webhook\PspPayloadReader;
use App\Tests\Fixtures\invoice\InvoiceFixtureTrait;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\LoadFixtureTrait;
use Doctrine\DBAL\Exception;

final class PspWebhookControllerTest extends FunctionalTestCase
{
    use LoadFixtureTrait;
    use InvoiceFixtureTrait;

    public function testPostReturnsAccepted(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');

        $this->client->request('POST', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(202);
    }

    public function testMarkPaid(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $webhookPayload = PspPayloadReader::read($payloadRaw);

        $invoice = $this->createInvoiceForWebhook($webhookPayload);
        $this->em->persist($invoice);
        $this->em->flush();
        $this->em->clear();

        $this->client->request('POST', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(202);

        $loadInvoice = $this->em->find(Invoice::class, $webhookPayload->invoiceId);
        $this->assertNotNull($loadInvoice);

        $this->assertSame(InvoiceStatus::Paid, $loadInvoice->status);
    }

    /**
     * @throws Exception
     */
    public function testDuplicateIsIgnored(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $this->client->request('POST', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(202);

        $this->client->request('POST', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(202);
        self::assertSame(1, $this->countWebhookEvents());
    }

    public function testConflictingPayloadIsRejected(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $this->client->request('POST', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(202);

        $otherRaw = $this->loadFixture('webhooks/payload_pay_01_2.json');
        $this->client->request('POST', '/webhooks/psp', [], [], [], $otherRaw);
        $this->assertResponseStatusCodeSame(409);
        self::assertSame(1, $this->countWebhookEvents());
        self::assertSame($payloadRaw, $this->getPayloadWebhookEvents());
    }

    public function testGetIsNotAllowed(): void
    {
        $payloadRaw = $this->loadFixture('webhooks/payload_pay_01_1.json');
        $this->client->request('GET', '/webhooks/psp', [], [], [], $payloadRaw);
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * @throws Exception
     */
    private function countWebhookEvents(): int
    {
        $count = $this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM webhook_events');

        $this->assertIsInt($count);

        return $count;
    }

    /**
     * @throws Exception
     */
    private function getPayloadWebhookEvents(): string
    {
        $payload = $this->em->getConnection()->fetchOne('SELECT payload FROM webhook_events');

        $this->assertIsString($payload);

        return $payload;
    }

    /**
     * @throws Exception
     */
    public function testMalformedPayloadIsRejected(): void
    {
        $this->client->request('POST', '/webhooks/psp', [], [], [], '{not json');
        $this->assertResponseStatusCodeSame(400);
        self::assertSame(0, $this->countWebhookEvents());
    }
}
