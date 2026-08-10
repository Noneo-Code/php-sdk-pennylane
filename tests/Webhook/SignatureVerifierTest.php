<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Webhook;

use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Exception\InvalidWebhookSignatureException;
use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;
use Noneo\Sdk\Pennylane\Webhook\WebhookSignatureScheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SignatureVerifierTest extends TestCase
{
    private function signedHeader(string $rawBody, string $secret, int $timestamp): string
    {
        $signature = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        return "t={$timestamp},v1={$signature}";
    }

    /**
     * Le nom de l'en-tête n'est pas documenté publiquement par Pennylane :
     * le figer dans une constante évite que chaque application le retape à
     * l'aveugle, et donne un seul point à corriger le jour où la capture d'un
     * webhook réel le contredit.
     */
    public function testExposesTheSignatureHeaderName(): void
    {
        self::assertSame('X-Pennylane-Signature', SignatureVerifier::HEADER);
    }

    public function testAcceptsAValidSignature(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654,"event":"dms_file.created"}';
        $header = $this->signedHeader($rawBody, $secret, time());

        $verifier = new SignatureVerifier($secret);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    public function testRejectsASignatureComputedWithAWrongSecret(): void
    {
        $rawBody = '{"id":987654,"event":"dms_file.created"}';
        $header = $this->signedHeader($rawBody, 'wrong-secret', time());

        $verifier = new SignatureVerifier('whsec_test');

        self::assertFalse($verifier->verify($rawBody, $header));
    }

    public function testRejectsASignatureComputedOverADifferentBody(): void
    {
        $secret = 'whsec_test';
        $header = $this->signedHeader('{"id":1}', $secret, time());

        $verifier = new SignatureVerifier($secret);

        self::assertFalse($verifier->verify('{"id":2}', $header));
    }

    public function testRejectsATimestampOlderThanTheDefaultFiveMinuteTolerance(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654,"event":"dms_file.created"}';
        $header = $this->signedHeader($rawBody, $secret, time() - 301);

        $verifier = new SignatureVerifier($secret);

        self::assertFalse($verifier->verify($rawBody, $header));
    }

    public function testAcceptsATimestampWithinAConfiguredTolerance(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654,"event":"dms_file.created"}';
        $header = $this->signedHeader($rawBody, $secret, time() - 600);

        $verifier = new SignatureVerifier($secret, toleranceSeconds: 900);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    public function testRejectsAMalformedHeader(): void
    {
        $verifier = new SignatureVerifier('whsec_test');

        self::assertFalse($verifier->verify('{}', 'not-a-valid-header'));
    }

    public function testRejectsAHeaderMissingTheTimestamp(): void
    {
        $verifier = new SignatureVerifier('whsec_test');

        self::assertFalse($verifier->verify('{}', 'v1=abcdef'));
    }

    public function testRejectsAHeaderWithANonNumericTimestamp(): void
    {
        $verifier = new SignatureVerifier('whsec_test');

        self::assertFalse($verifier->verify('{}', 't=not-a-number,v1=abcdef'));
    }

    /**
     * Le format réel de Pennylane n'étant pas établi (cf. le docblock de
     * SignatureVerifier), le mode `BodyOnly` couvre l'autre hypothèse : HMAC
     * du corps seul, sans horodatage.
     */
    public function testBodyOnlySchemeAcceptsALowercaseHexHmacOfTheRawBody(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654,"event":"dms_file.created"}';
        $header = hash_hmac('sha256', $rawBody, $secret);

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    public function testBodyOnlySchemeAcceptsAnUppercaseHexHmac(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654}';
        $header = strtoupper(hash_hmac('sha256', $rawBody, $secret));

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    public function testBodyOnlySchemeAcceptsABase64Hmac(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654}';
        $header = base64_encode(hash_hmac('sha256', $rawBody, $secret, binary: true));

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    #[DataProvider('algorithmPrefixes')]
    public function testBodyOnlySchemeToleratesAnAlgorithmPrefix(string $prefix): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654}';
        $header = $prefix.hash_hmac('sha256', $rawBody, $secret);

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        self::assertTrue($verifier->verify($rawBody, $header));
    }

    /** @return iterable<string, array{0: string}> */
    public static function algorithmPrefixes(): iterable
    {
        yield 'sha256' => ['sha256='];
        yield 'hmac-sha256' => ['hmac-sha256='];
        yield 'casse mixte' => ['SHA256='];
    }

    public function testBodyOnlySchemeRejectsASignatureComputedWithAWrongSecret(): void
    {
        $rawBody = '{"id":987654}';
        $header = hash_hmac('sha256', $rawBody, 'wrong-secret');

        $verifier = new SignatureVerifier('whsec_test', scheme: WebhookSignatureScheme::BodyOnly);

        self::assertFalse($verifier->verify($rawBody, $header));
    }

    public function testBodyOnlySchemeRejectsAnUndecodableSignature(): void
    {
        $verifier = new SignatureVerifier('whsec_test', scheme: WebhookSignatureScheme::BodyOnly);

        self::assertFalse($verifier->verify('{}', 'pas-une-signature'));
    }

    /**
     * Le cœur du compromis : le schéma se choisit à la construction, jamais
     * d'après le contenu de l'en-tête. Une auto-détection laisserait un
     * attaquant demander le format sans horodatage, donc sans anti-rejeu.
     */
    public function testTheDefaultSchemeRejectsABodyOnlySignature(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654}';
        $header = hash_hmac('sha256', $rawBody, $secret);

        $verifier = new SignatureVerifier($secret);

        self::assertFalse($verifier->verify($rawBody, $header));
    }

    public function testTheBodyOnlySchemeRejectsATimestampedSignature(): void
    {
        $secret = 'whsec_test';
        $rawBody = '{"id":987654}';
        $header = $this->signedHeader($rawBody, $secret, time());

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        self::assertFalse($verifier->verify($rawBody, $header));
    }

    private function documentedBody(): string
    {
        return (string) json_encode([
            'id' => 987654,
            'event' => 'customer_invoice.e_invoicing_status_updated',
            'retry_count' => 0,
            'created' => 1782864000,
            'data' => [
                'context' => ['company_id' => 'abc-123'],
                'object' => ['id' => 42, 'e_invoicing' => ['status' => 'accepted']],
            ],
        ]);
    }

    /**
     * `verify()` puis `fromArray()` laissait la porte ouverte à l'oubli de la
     * première moitié : `constructEvent()` rend l'événement indissociable de
     * sa vérification.
     */
    public function testConstructEventReturnsTheParsedEventOnAValidSignature(): void
    {
        $secret = 'whsec_test';
        $rawBody = $this->documentedBody();

        $verifier = new SignatureVerifier($secret);

        $event = $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, $secret, time()));

        self::assertSame(987654, $event->id);
        self::assertSame(WebhookEventType::CustomerInvoiceEInvoicingStatusUpdated, $event->event);
        self::assertSame('abc-123', $event->companyId);
    }

    public function testConstructEventAlsoWorksUnderTheBodyOnlyScheme(): void
    {
        $secret = 'whsec_test';
        $rawBody = $this->documentedBody();

        $verifier = new SignatureVerifier($secret, scheme: WebhookSignatureScheme::BodyOnly);

        $event = $verifier->constructEvent($rawBody, hash_hmac('sha256', $rawBody, $secret));

        self::assertSame(987654, $event->id);
    }

    public function testConstructEventRejectsAnInvalidSignature(): void
    {
        $rawBody = $this->documentedBody();

        $verifier = new SignatureVerifier('whsec_test');

        $this->expectException(InvalidWebhookSignatureException::class);

        $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, 'wrong-secret', time()));
    }

    public function testConstructEventRejectsAReplayOutsideTheTolerance(): void
    {
        $secret = 'whsec_test';
        $rawBody = $this->documentedBody();

        $verifier = new SignatureVerifier($secret);

        $this->expectException(InvalidWebhookSignatureException::class);

        $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, $secret, time() - 301));
    }

    /**
     * Le corps est du contenu attaquant tant que la signature n'est pas
     * validée — et même après, il porte des données comptables. Il n'a rien à
     * faire dans un message d'exception qui finira en journal ou dans Sentry.
     */
    public function testTheRejectionMessageLeaksNeitherTheBodyNorTheSecret(): void
    {
        $secret = 'whsec_test';
        $rawBody = $this->documentedBody();

        $verifier = new SignatureVerifier($secret);

        try {
            $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, 'wrong-secret', time()));
            self::fail('Une signature invalide devait être rejetée.');
        } catch (InvalidWebhookSignatureException $exception) {
            self::assertStringNotContainsString('987654', $exception->getMessage());
            self::assertStringNotContainsString($secret, $exception->getMessage());
        }
    }

    public function testConstructEventRejectsABodyThatIsNotJson(): void
    {
        $secret = 'whsec_test';
        $rawBody = 'pas du JSON';

        $verifier = new SignatureVerifier($secret);

        $this->expectException(\JsonException::class);

        $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, $secret, time()));
    }

    public function testConstructEventRejectsAJsonBodyThatIsNotAnObject(): void
    {
        $secret = 'whsec_test';
        $rawBody = '"une chaîne"';

        $verifier = new SignatureVerifier($secret);

        $this->expectException(\InvalidArgumentException::class);

        $verifier->constructEvent($rawBody, $this->signedHeader($rawBody, $secret, time()));
    }
}
