<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use PHPUnit\Framework\TestCase;

final class EInvoiceImportDtoTest extends TestCase
{
    public function testFromArrayBuildsDtoFromDocumentedPayload(): void
    {
        $dto = EInvoiceImportDto::fromArray([
            'id' => 42,
            'url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/customer_invoices/42', $dto->url);
    }
}
