<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\TransactionReferenceDto;
use PHPUnit\Framework\TestCase;

final class TransactionReferenceDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $reference = TransactionReferenceDto::fromArray([
            'banking_provider' => 'bank',
            'provider_field_name' => 'label',
            'provider_field_value' => 'invoice_number',
        ]);

        self::assertSame('bank', $reference->bankingProvider);
        self::assertSame('label', $reference->providerFieldName);
        self::assertSame('invoice_number', $reference->providerFieldValue);
    }
}
