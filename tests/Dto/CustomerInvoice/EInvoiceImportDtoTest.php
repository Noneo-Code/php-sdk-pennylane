<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class EInvoiceImportDtoTest extends TestCase
{
    /**
     * L'endpoint générique `/e-invoices/imports` rend la même paire `id`/`url`
     * que ses deux successeurs spécialisés : le SDK lui fait porter le même
     * DTO plutôt qu'un jumeau.
     *
     * @return array<string, mixed>
     */
    #[DocumentedBy('createEInvoiceImport')]
    public static function documentedGenericImportPayload(): array
    {
        return [
            'id' => 42,
            'url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42',
        ];
    }

    public function testFromArrayBuildsDtoFromDocumentedPayload(): void
    {
        $dto = EInvoiceImportDto::fromArray([
            'id' => 42,
            'url' => 'https://app.pennylane.com/api/external/v2/customer_invoices/42',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/customer_invoices/42', $dto->url);
    }

    public function testFromArrayBuildsDtoFromTheGenericImportPayload(): void
    {
        $dto = EInvoiceImportDto::fromArray(self::documentedGenericImportPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/supplier_invoices/42', $dto->url);
    }
}
