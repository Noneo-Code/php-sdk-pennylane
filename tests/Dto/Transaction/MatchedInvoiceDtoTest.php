<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Transaction;

use Noneo\Sdk\Pennylane\Dto\Transaction\InvoiceType;
use Noneo\Sdk\Pennylane\Dto\Transaction\MatchedInvoiceDto;
use PHPUnit\Framework\TestCase;

final class MatchedInvoiceDtoTest extends TestCase
{
    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = MatchedInvoiceDto::fromArray([
            'id' => 42,
            'type' => 'customer',
            'url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame(InvoiceType::Customer, $dto->type);
        self::assertSame('https://app.pennylane.com/api/external/v2/customer_invoices/42', $dto->url);
    }
}
