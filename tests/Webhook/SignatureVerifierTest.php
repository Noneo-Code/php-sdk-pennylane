<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Webhook;

use Noneo\Sdk\Pennylane\Webhook\SignatureVerifier;
use PHPUnit\Framework\TestCase;

final class SignatureVerifierTest extends TestCase
{
    private function signedHeader(string $rawBody, string $secret, int $timestamp): string
    {
        $signature = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        return "t={$timestamp},v1={$signature}";
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
}
