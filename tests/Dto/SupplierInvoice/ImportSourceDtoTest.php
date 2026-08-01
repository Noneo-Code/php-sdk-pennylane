<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\ImportSourceConnector;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\ImportSourceDto;
use PHPUnit\Framework\TestCase;

final class ImportSourceDtoTest extends TestCase
{
    public function testFromArrayBuildsAnImportSourceDtoFromTheDocumentedPayload(): void
    {
        $importSource = ImportSourceDto::fromArray([
            'source' => 'dropbox',
            'path' => '/ACCOUNTING INVOICES/2024/invoice.pdf',
        ]);

        self::assertSame(ImportSourceConnector::Dropbox, $importSource->source);
        self::assertSame('/ACCOUNTING INVOICES/2024/invoice.pdf', $importSource->path);
    }
}
